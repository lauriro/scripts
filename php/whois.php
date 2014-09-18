<?php
$domain=$_POST['domain'];

?><form method="POST" action= "<?PHP echo($_SERVER['PHP_SELF']); ?>">
Function checkDomainReg($domain)<br>
author: David Wade Arnold<br><br>
<b>Check to see if a domain is registered</b>
<P> <INPUT name="domain" SIZE="30" MAXLENGTH="35">
<P> <INPUT TYPE="SUBMIT" VALUE="Check Domain">
<INPUT TYPE="RESET" VALUE="Clear">
</P>
</FORM>
<hr>
<font size="2"><pre>
<?
//This function was found at : http://www.phpwizard.net/phpTidbits/
function checkDomainReg($domain, $server="whois.crsnic.net") { 
/*	Author: David Wade Arnold -- david@eccentrichosting.net
	checkDomainReg: checks to see if a domain name is taken. Returns boolean false 
	domain name is not taken.
	configuration: This scripts depends on one line of a whois output. 
	If the whois server is changed make sure that the lineNumber variable
	is changed to the line that returns:
	   Domain Name: ECCENTRICTECHNOLOGIES.COM
	-- when domain exists
	and
	No match for "ASHDFIOWUET.NET".
	-- when domain does not exist 
	This line is diffrent for diffrent whois servers. 
	wrap-up: Please email me if you make changes. Hey, I want a better version too!
*/
	$lineNumber = 8;
	// open a socket connection to a whois server
	$fp = fsockopen ($server, 43, &$errnr, &$errstr) or die("$errno: $errstr");
	fputs($fp, "$domain\n");

	$x=0;
	while (!feof($fp)) {
		//return each line of stout and place it in $serverReturn
		$serverReturn = fgets($fp, 2048);
		$x++;
		if ($x == $lineNumber) { 
			$line = $serverReturn;
		}
	}
	fclose($fp);
	//tokenize the string so we can find the No
	$token = strtok("$line"," ");
	if ($token == 'No') {
		$result = 0;
	} else {
		$result = 1;
	}
	return $result; 
} 



 ?>
<?
If (isset ($domain)){
	$answer = checkDomainReg($domain);
	if($answer) {
		echo "Please try again the domain $domain is registered";
	} else {
		echo "$domain Is free to register"; }
}
?>
</pre></font>
