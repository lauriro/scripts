<?php
/* Digest-Authentication System, v2.0 */
/* This hasn't been modified to use a database yet... */
/* This file is based on IETF RFC 2617 Standard: http://www.ietf.org/rfc/rfc2617.txt?number=2617
	Thomas Pike's Digest Authentication class was used as a reference: http://www.xiven.com/sourcecode/digestauthentication
*/

define('SECRET', 'SuperSecret-MonkeyStomp');
// Use $GLOBALS explicitly, in case this file gets included from within a subroutine.
$GLOBALS['passwords'] = array(
	'user' => 'password',
);

/* This class implements a 'scoring' system, to calculate a confidence score based on the circumstances of authentication.
	The following constants are used to calculate that score... */ 
define('AUTH_SCORE', 80);			// Base score if Digest 'auth' scheme is used.
define('AUTH_INT_SCORE', 100);		// Base score if Digest 'auth-int' is used.
define('NONCE_LIFE', 20);			// New nonces appear every n seconds
define('NONCE_COUNT', 9);			// n-1 old nonces are considered before the auth data is marked stale.
define('BASIC_SCORE', 50);			// Base score if HTTP 'Basic' authentication is used.
define('BASIC_PENALTY_TIME', 20);	// Score is decreased by 1 point per n seconds between requests.
define('IP_CHANGE_PENALTY', 25);	// Score is decreased n points if the user's IP has changed since last request.

define('MAX_FAILURES', 8);			// Account lockout goes into effect after n authentication failures.
define('LOCKOUT_TIME', 60);			// Account lockout lasts n seconds.
define('LOCKOUT_PENALTY', 30);		// Lockout is extended by n seconds if the user tries to authenticate while locked out.

class DigestAuth {
	public $confidence;
	public $qop;

	private $userName;
	private $data;
	private $allowBasic;
	
	function __construct($threshold = 30) {
		$this->confidence = 0;
		$nonces = $this->validNonces();
		session_start();
		$headers = array();
		$this->allowBasic = (BASIC_SCORE >= $threshold);
		
		// Initialize the session.  We need to define OPAQUE, FAILURES, nonce, and nc...
		if (!isset($_SESSION['OPAQUE'])) {
			$_SESSION['OPAQUE'] = $this->KD(time(), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], SECRET, $_SERVER['SERVER_SIGNATURE']);
			$_SESSION['OPAQUE_VALIDATED'] = FALSE;
		}
		if (!isset($_SESSION['FAILURES'])) $_SESSION['FAILURES'] = 0;
		if (!isset($_SESSION['nonce'])) {
			$_SESSION['nonce'] = $nonces[0];
			$_SESSION['nc'] = 1;	// nc is the NEXT nonce count we expect to receive from the client.
		}
		
		// If there is a lockout in effect
		if (isset($_SESSION['LOCKOUT'])) {
			if ($_SESSION['LOCKOUT'] > time()) {
				// If the lockout hasn't expired, we increase the lockout time, display a message, and abort execution.
				$_SESSION['LOCKOUT'] += LOCKOUT_PENALTY;
				header('HTTP/1.x 403.8 Forbidden: Site Access Denied.');
				die('<h1>Too many failed login attempts</h1><p>A timeout is in effect for '.($_SESSION['LOCKOUT'] - time()).' more seconds.  Please Wait.  If you try to login again before this time has expired, an additional penalty may be assessed.</p>');
			} else {
				// Clear the lockout and reset our failure count.
				unset($_SESSION['LOCKOUT']);
				$_SESSION['FAILURES'] = 0;
			}
		}
		
		// Set up the Authentication Data that will go to the client.
		$this->data = array(
			'qop'=>'auth-int,auth',				// Quality of Protection.  Prefer Integrity & Authorization, accept Authorization...
			'algorithm'=>'MD5',					// Hash Algorithm.  No others supported that I know of.
			'realm'=>$_SERVER['SERVER_NAME'],	// Separates this set of credentials to the client, facilitates stored passwords.
		//	'domain'=>$_SERVER['SERVER_NAME'],	// Top-level URI's of protection spaces.  Causes problems with Opera...
			'uri'=>$_SERVER['REQUEST_URI'],		// Page being requested.
			'nonce'=>$nonces[0],				// Freshest valid nonce.
			'opaque'=>$_SESSION['OPAQUE'],		// Defines a value which must be returned unmodified by the client.
			'stale'=>'false',					// Tells client whether to recalculate its keys with a new nonce, instead of prompting.
		);
		if (isset($_SERVER['Authorization'])) {
			// Try to get the Auth Header if it was properly set
			$headers['Authorization'] = $_SERVER['Authorization'];
		} elseif (function_exists('apache_request_headers')) {
			// Otherwise, try to get Apache's headers (Apache doesn't set $_SERVER['Authorization'] for some reason)
			$headers = apache_request_headers();
		} elseif (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
			// Fall back to HTTP Basic Authentication as a last resort.  Regenerate the string header from PHP's info.
			$headers['Authorization'] = "Basic ".base64_encode("$_SERVER[PHP_AUTH_USER]:$_SERVER[PHP_AUTH_PW]");
		}
		if (isset($headers['Authorization'])) {
			$this->digest = (substr($headers['Authorization'], 0, 7) === 'Digest ');
			if ($this->digest) {
				preg_match_all('/(\w+)=("([^"]*)"|([^",]+),)/', $headers['Authorization'], $vals, PREG_SET_ORDER);
				foreach ($vals as $val) {
					// Generate local vars to make things easier.
					// Typically, it's: $username, $realm, $nonce, $uri, $algorithm, $response, $opaque, $qop, $nc, $cnonce
					if (!empty($val[4])) {
						$$val[1] = $val[4];		// Value was unquoted already
					} else $$val[1] = $val[3];	// Value was quoted, take unquoted copy
				//	print "  $$val[1] = '".$$val[1]."';<br />\n";
				}
				// Account for IE's problem with Query Strings
				if ($_SERVER['QUERY_STRING'] != '' and strpos($uri, '?') === FALSE) {
					$this->data['uri'] = $_SERVER['PHP_SELF'];
				}
				// Check URI for validity...
				if ($uri !== $this->data['uri']) {
					$this->badHeader();		// Request was malformed
				} else {
					// Get the nonce, and find out how old it is
					$nonceIndex = array_search($nonce, $nonces);	// Index of $nonce within $nonces...
					if ($nonceIndex === FALSE) {
						$this->getNewNonce();	// Nonce is invalid, probably just too old.  Request re-authentication.
					} else {
						$this->confidence = -$nonceIndex;		// Score -1 for each unit of age on our nonce.
						$this->data['nonce'] = $nonce;			// Mark it for next time...
						if ($_SESSION['nonce'] !== $nonce) {
							$_SESSION['nonce'] = $nonce;
							$_SESSION['nc'] = 1;
						}
						// Check Nonce Count.
						$nci = hexdec($nc);
						if ($nci < $_SESSION['nc']) {
							// Almost certainly a replay attack.
							$this->getNewNonce();
						} else {
							if (($nci - $_SESSION['nc']) > 3) {
								// The client's computer has failed to connect several times.  Re-Authenticate.
								$this->getNewNonce();
							} else {
								// NONCE COUNT PASSED
								$_SESSION['nc']++;
								// Check Opaque
								if (isset($opaque)) {
									// IE only sends the OPAQUE value ONE TIME, when it is first validated.  Can't they get anything right?
									$_SESSION['OPAQUE_VALIDATED'] = ($opaque === $this->data['opaque']);
								}
								if (!$_SESSION['OPAQUE_VALIDATED']) {
									// We always send a QOP header, so we MUST ALWAYS have an opaque value, according to specs.
									$this->sendHeader();	// If it is present and wrong, something was most likely corrupted.
								} else {
									// Check $username to make sure it exists...
									if (!isset($GLOBALS['passwords'][$username])) $this->sendHeader();
									// Start Generating the $response value we're checking against...
									$a1 = $this->KD($username, $this->data['realm'], $GLOBALS['passwords'][$username]);
									$this->qop = $qop;
									if ($qop === 'auth-int') {
										// Integrity means that we have to include the whole request body in $a2
									//	$body = file_get_contents("php://input");		// PHP5 Only...
										$body = implode("\n", file('php://input'));	// PHP 4.3.0 ++
										$a2 = $this->KD($_SERVER['REQUEST_METHOD'], $uri, MD5($body));
										$this->confidence += AUTH_INT_SCORE;
										$rA2 = $this->KD('', $uri, MD5($body));
									} else {
										$a2 = $this->KD($_SERVER['REQUEST_METHOD'], $uri);
										$this->confidence += AUTH_SCORE;
										$rA2 = $this->KD('', $uri);
									}
									$expected = $this->KD($a1, $nonce, $nc, $cnonce, $qop, $a2);
									if ($response !== $expected) {
										print "Password & Username Failed Check For $qop!";
										$this->sendHeader();
									}
									$rsp = $this->KD($a1, $nonce, $nc, $cnonce, $qop, $rA2);
									header("Authentication-Info: nextnonce=\"$nonces[0]\" qop=\"$qop\" rspauth=\"$rA2\" cnonce=\"$cnonce\" nc=$nc");
									/* THE USER IS FULLY AUTHENTICATED AT THIS POINT.  HOWEVER, WE MAY STILL DECIDE TO RE-AUTHENTICATE... */
									$this->userName = $username;
								}
							}
						}
					}
				}
			} else {
				// Retrieve Username and Password from the header.
				$basic = Explode(' ', $headers['Authorization']);
				if (isset($basic[1])) list($username, $password) = Explode(':', base64_decode($basic[1]));
				if (isset($GLOBALS['passwords'][$username])) {
					if ($GLOBALS['passwords'][$username] !== $password) $this->sendHeader();
				} else $this->sendHeader();
				// User is now authenticated under HTTP Basic
				if (!$this->allowBasic) $this->authErr('NoDigest');	// If Basic isn't good enough, redirect to an error message.
				$this->confidence += BASIC_SCORE;
				$this->userName = $username;
				$this->qop = 'basic';
				/* Under Digest Authentication, aging of the nonces accomplishes this for us... */
				$delta = (int)((time() - $_SESSION['time']) / (BASIC_PENALTY_TIME));
				$this->confidence -= $delta;
			}
		} else {
			// No Authorization Header was supplied...
			$this->sendHeader();
		}
		/* Penalize for IP Change and/or excessive time elapsed between pageloads. */
		if ($_SERVER['REMOTE_ADDR'] != $_SESSION['IP']) {
			$this->confidence -= IP_CHANGE_PENALTY;
			$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
		}
		$_SESSION['time'] = time();
		
		/* Make sure our current level of trust is appropriate.  If not, re-request login information. */
		if ($this->confidence < $threshold) $this->sendHeader();
		unset($_SESSION['FAILURES']);
		// One of our stronger protections against replay attacks, although costly on the server side...
		//session_regenerate_id();
	}
	
	function KD() {
		$args = func_get_args();
		return MD5(implode(':', $args));
	}
	
	function validNonces() {
		$t = time();
		$t -= $t % NONCE_LIFE;	// Round to the nearest appropriate block of time...
		for ($i = 0; $i < NONCE_COUNT; $i++) {
			$result[] = $this->KD($t, $_SERVER['HTTP_USER_AGENT'], SECRET);
			$t -= NONCE_LIFE;
		}
		return $result;
	}
	
	function getNewNonce() {
		unset($_SESSION['nonce']);
		$this->data['stale'] = 'true';
		$this->sendHeader();
	}
	
	function getUserName() {
		return $this->userName;
	}
	
	function badHeader() {
		if (++$_SESSION['FAILURES'] > MAX_FAILURES) {
			$_SESSION['LOCKOUT'] = time() + LOCKOUT_TIME;
			die("Too many retries.  Lockout in effect for 1 minute.");
		}
		//session_destroy();
		header('HTTP/1.x 400 Bad Request');
		die('Bad Header Information');
	}
	
	function sendHeader() {
		@ob_clean();
		if (++$_SESSION['FAILURES'] > MAX_FAILURES) {
			$_SESSION['LOCKOUT'] = time() + LOCKOUT_TIME;
			die("Too many retries.  Lockout in effect for 1 minute.");
		}
		$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['time'] = time();
		$_SESSION['nc'] = 1;
		$authStr = "";
		$first = TRUE;
		foreach($this->data as $key=>$val) {
			if ($first) {
				$first = FALSE;
				$authStr = "$key=\"$val\"";
			} else $authStr .= ", $key=\"$val\"";
		}
		$realm = $this->data['realm'];
		if ($this->allowBasic) $authStr .= "\r\nWWW-Authenticate: Basic realm=\"$realm\"";
		header("WWW-Authenticate: Digest $authStr");
		header("HTTP/1.x 401 Unauthorized");
		die("<div class=\"contentcell\"><h1>Login Error</h1>A valid login is required to access this page.</div>");
	}
	
	function authErr($err) {
		header("HTTP/1.x 401 Unauthorized");
		if ($err == 'NoDigest') {
			// Some older browsers will try to send Basic authentication regardless, because they don't understand the Digest directive
			// but they act on it anyway.  So, we give the user a page describing what is wrong.
			die("<h1>Your Browser is Too Old</h1><p>You are using a web browser that does not understand <b>DIGEST AUTHENTICATION</b>.  Please use a browser that does understand this security protocol to access this page.</p>");
		} else {
			die($err);
		}
	}
}
?>