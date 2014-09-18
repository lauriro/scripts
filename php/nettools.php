<?
###############################################################################
#                                                                             #
#                                PHP Net Tools                                #
#                      Copyright (C) 2006 Eric Robertson                      #
#                             h4rdc0d3@gmail.com                              #
#                                                                             #
#                                   -------                                   #
#                                                                             #
#  PHP Net Tools is free software; you can redistribute it and/or modify      #
#  it under the terms of the GNU General Public License as published by       #
#  the Free Software Foundation; either version 2 of the License, or          #
#  (at your option) any later version.                                        #
#                                                                             #
#  This program is distributed in the hope that it will be useful,            #
#  but WITHOUT ANY WARRANTY; without even the implied warranty of             #
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              #
#  GNU General Public License for more details.                               #
#                                                                             #
#  You should have received a copy of the GNU General Public License          #
#  along with this program; if not, please visit http://www.gnu.org           #
#                                                                             #
#                                   -------                                   #
#                                                                             #
#  You are permitted to edit and redistrubute this code as you wish,          #
#  as long as you give credit where due and include a copy of the GPL.        #
#                                                                             #
#  PHP Net Tools includes the following functional and configurable features: #
#    Resolve host/reverse DNS lookup, find the country in which the target    #
#    host is located, ip whois, domain whois, ns lookup, dig, http request,   #
#    ping, traceroute, tracepath, portscan, nmap, and info logging.           #
#                                                                             #
#  Please see the help option ([?]) for more information on each function.    #
#                                                                             #
#                                   -------                                   #
#                                                                             #
#  last revision: 10.06.2006 (v2.8.2)                                         #
#  see changelog.txt                                                          #
#                                                                             #
###############################################################################


// Set script version number
$version = '2.8.2';

// Log information of anyone visiting the site? (default = FALSE)
$enable_log_user = FALSE;

// Declare some globals
global $ip, $host_name, $host_ip, $url_parts;


// Shorten the variable names from submitted form elements - also initializes the variables for security
//
// NEW METHOD:
//   This method is faster as it dynamically creates variables only for the necessary POST elements.
//   It is also neater and uses much less code.  The only downfall is that it is less intuitive to read.
//
//   (for info on how "variable variables" work, see http://www.php.net/manual/en/language.variables.variable.php)
//
$post_keys = array_keys($_POST);

for ($i = 0; $i < count($post_keys); $i++)
{
	$$post_keys[$i] = $_POST[$post_keys[$i]];
}


// Function to split a URL into its components
function split_url($host)
{
	global $url_parts;

	$url_parts = parse_url($host);

	// If no port specified, default to port 80
	if (!isset($url_parts['port'])) {
		$url_parts['port'] = 80; }

	// If no path specified, default to "/"
	if (!isset($url_parts['path'])) {
		$url_parts['path'] = '/'; }
}

// Function to find the ip address of the user
function get_ip()
{
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
	elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP']; }
	else {
		$ip = $_SERVER['REMOTE_ADDR']; }

	return $ip;
}

// Function to log information of the person browsing the site (date, time, IP, host, what they scanned).
//   Saved in a comma separated text file which allows it to be opened as a spreadsheet
function log_user($host)
{
	global $ip;

	// Current Date and Time
	$log_date = date('Y.m.d');
	$log_time = date('H:i:s');

	// Create a variable holding the information to be saved
	$log_info = "$log_date,$log_time,$ip," . gethostbyaddr($ip) . ",$host\r\n";

	// If the "log.csv" file already exists ...
	if (file_exists('log.csv'))
	{
		// ... open the file ready to add the info to the end ...
		$fp = fopen('log.csv', 'a');
	}
	// ... otherwise, ...
	else
	{
		// ... create a new file and add a line with the heading
		$fp = fopen('log.csv', 'w');
		@fwrite($fp, "DATE,TIME,USER IP,USER HOST,QUERY\r\n\r\n");
	}

	// Add user info to the log file and close it
	@fwrite($fp, $log_info);
	fclose($fp);
}

// Function to print the Resolve Host/Reverse Lookup option results
function resolve($host)
{
	global $host_name, $host_ip;
	
	// If the value of $host begins with an alpha character, it must be a host name, so find its ip...
	if(eregi("^[a-z]", $host))
	{
		$host_name = $host;
		$host_ip = gethostbyname($host);
	}
	// ...otherwise, it must be an ip address, so find its host name
	else
	{
		$host_name = gethostbyaddr($host);
		$host_ip = $host;
	}

	echo "<a href=\"javascript:enter_ip('$host');\">$host</a> resolved to ";

	if ($host == $host_name) {
		echo "<a href=\"javascript:enter_ip('$host_ip');\">$host_ip</a><br><br>"; }
	else {
		echo "<a href=\"javascript:enter_ip('$host_name');\">$host_name</a><br><br>"; }
}

// Function to find the country location of the machine host/ip
function ip_to_country()
{
	// Do a whois on the ip using "whois.arin.net" and store the results in $buffer
	$buffer = nl2br(whois_ip('whois.arin.net', '-1', 'FALSE'));

	// If the whois contains a line for a referral server, do a new whois using this server and store in $buffer
	if (eregi("ReferralServer:[[:space:]]*[a-z]*(whois://)*([a-z0-9-][\.a-z0-9-]{2,})[:]*([0-9]+)*", $buffer, $regs))
	{
		$referral_host = $regs[2];
		$buffer = nl2br(whois_ip($referral_host, $regs[3], 'FALSE'));
	}

	// If there is a line labeled "country", get its value and print it...
	if (eregi("country:[[:space:]]*([a-z]{2,})", $buffer, $regs))
	{
		// Store the value of the "country" line from the buffer
		$country = $regs[1];

		// Caching of the country list:  If the file "list_file.txt" exists on the server, the country list was already cached,
		//   so read it into the $list_file variable...
		if (file_exists('list_file.txt'))
		{
			$list_file = @file_get_contents('list_file.txt');
		}
		// ...otherwise, download the country list and cache it on the server in the file "list_file.txt"
		else
		{
			// The ISO standards website provides a free text file listing every country and it's 2 character country code -
			//   download this file and store it in the $list_file variable
			$list_file = @file_get_contents('http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1-semic.txt');

			// Cache the country code list on the server
			$fp = fopen('list_file.txt', 'w');
			@fwrite($fp, $list_file);
			fclose($fp);
		}

		// Convert the new line characters in the file contents to HTML line breaks and split it at each line into an array
		$list_file_br = nl2br($list_file);
		$list_rows = explode("<br />", $list_file_br);

		// Define an array to store the country info
		$country_list = array();

		// Loop through each line in the file and save the 2 character country code and it's full name
		//   in the $country_list array
		for ($i = 1; $i < count($list_rows); $i++)
		{
			$row = explode(";", $list_rows[$i]);
			$row_abbr = $row[1];
			$row_name = ucwords(strtolower($row[0]));

			$country_list[$row_abbr] = $row_name;
		}

		// If the country in the whois buffer is in the country_list array, print its full name...
		if (array_key_exists($country, $country_list)) {
			echo "Location: &nbsp;<b>$country_list[$country]</b> ($country)<br><br>"; }
		// ...otherwise, just print the 2 character country code listed in the whois buffer
		else {
			echo "Location: &nbsp;<b>$country</b><br><br>"; }
	}
	// ...or if there is no "country" line, print location unknown
	else {
		echo 'Location: &nbsp;Unknown<br><br>'; }
}

// Function to perform a whois lookup on the machine's ip address
function whois_ip($whois_ip_server, $whois_ip_port, $do_echo)
{
	if (eregi("^[a-z0-9\:\.\-]+$", $whois_ip_server))
	{
		global $host_ip;

		// The whois server "whois.arin.net" requires a "+" flag to get all the details
		if ($whois_ip_server == 'whois.arin.net') {
			$whois_ip_server .= ' +'; }

		// Set a variable containing the command to be sent to the system
		$command = "whois -h $whois_ip_server $host_ip";

		// If we passed a specific port to this function to connect to, add the necessary info to the command
		if ($whois_ip_port > 0) {
			$command .= " -p $whois_ip_port"; }

		// Send the whois command to the system
		//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
		//   to pipe STDERR to STDOUT so if there is an error, we can see it.
		$fp = shell_exec("$command 2>&1");

		// If the $do_echo variable is set to "TRUE", send the results to the parse_output() function...
		if ($do_echo == 'TRUE')
		{
			$output = '<a href="javascript:toggle_results(\'r_whois_ip\')" class="toggle"><b>Whois (IP) Results:</b></a><blockquote>';
			$output .= '<div id="r_whois_ip" style="display: block;">';
			$output .= nl2br(htmlentities(trim($fp)));
			$output .= '</div></blockquote>';

			parse_output($output);
		}
		// ...otherwise, return the results in a variable (i.e. for the ip_to_country() function)
		else {
			return $fp; }
	}
	else
	{
		echo '<a href="javascript:toggle_results(\'r_whois_ip\')" class="toggle"><b>Whois (IP) Results:</b></a><blockquote>';
		echo '<div id="r_whois_ip" style="display: block;">';
		echo 'Invalid character(s) in the Whois (IP) Server field.';
		echo '</div></blockquote>';
	}
}

// Function to perform a whois lookup on a domain
function whois_domain($host, $whois_domain_server)
{
	if (eregi("^[a-z0-9\.\-]+$", $whois_domain_server))
	{
		global $host_name, $host_ip;

		// Set the default value for a variable
		$whois_host = $host;

		// Split the host into its domain levels
		$split_host = explode('.', $host_name);

		// If the host name contains "www", remove it
		if ($split_host[0] == 'www')
		{
			array_shift($split_host);
			$whois_host = implode(".", $split_host);
		}

		// If searching a japanese whois server, use the "/e" switch to suppress japanese characters in the output
		if (substr($whois_domain_server, -3) == '.jp') {
			$whois_host .= '/e'; }

		// Set a variable containing the command to be sent to the system
		$command = "whois -h $whois_domain_server $whois_host";

		// Send the whois command to the system
		//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
		//   to pipe STDERR to STDOUT so if there is an error, we can see it.
		$fp = shell_exec("$command 2>&1");

		// Save the results as a variable and send to the parse_output() function
		$output = '<a href="javascript:toggle_results(\'r_whois_domain\')" class="toggle"><b>Whois (Domain) Results:</b></a><blockquote>';
		$output .= '<div id="r_whois_domain" style="display: block;">';
		$output .= nl2br(htmlentities(trim($fp)));
		$output .= '</div></blockquote>';

		parse_output($output);
	}
	else
	{
		echo '<a href="javascript:toggle_results(\'r_whois_domain\')" class="toggle"><b>Whois (Domain) Results:</b></a><blockquote>';
		echo '<div id="r_whois_domain" style="display: block;">';
		echo 'Invalid character(s) in the Whois (Domain) Server field.';
		echo '</div></blockquote>';
	}
}

// Function to perform an NS Lookup on a host/ip
function nslookup($host)
{
	// Set initial command to be run on the server
	$command = "nslookup $host -sil";

	// Send the nslookup command to the system
	//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
	//   to pipe STDERR to STDOUT so if there is an error, we can see it.
	$fp = shell_exec("$command 2>&1");

	// Save the results as a variable and send to the parse_output() function
	$output = '<a href="javascript:toggle_results(\'r_nslookup\')" class="toggle"><b>NS Lookup Results:</b></a><blockquote>';
	$output .= '<div id="r_nslookup" style="display: block;">';
	$output .= nl2br(htmlentities(trim($fp)));
	$output .= '</div></blockquote>';

	parse_output($output);
}

// Function to perform a dig on a host/ip
function dig($host, $dig_class, $dig_server)
{
	if (eregi("^[a-z0-9\.\-]*$", $dig_server))
	{
		// Set initial command to be run on the server
		$command = "dig -t $dig_class $host";

		// If a dig server has been entered, add the necessary info to the command
		if ($dig_server) {
			$command .= ' @' . $dig_server; }

		// Send the dig command to the system
		//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
		//   to pipe STDERR to STDOUT so if there is an error, we can see it.
		$fp = shell_exec("$command 2>&1");

		// Save the results as a variable and send to the parse_output() function
		$output = "<a href=\"javascript:toggle_results('r_dig')\" class=\"toggle\"><b>Dig Results ($dig_class):</b></a><blockquote>";
		$output .= '<div id="r_dig" style="display: block;">';
		$output .= nl2br(htmlentities(trim($fp)));
		$output .= '</div></blockquote>';

		parse_output($output);
	}
	else
	{
		echo "<a href=\"javascript:toggle_results('r_dig')\" class=\"toggle\"><b>Dig Results ($dig_class):</b></a><blockquote>";
		echo '<div id="r_dig" style="display: block;">';
		echo 'Invalid characters in the Dig Server field.';
		echo '</div></blockquote>';
	}
}

// Function to retreive HTTP request info
function http_req($http_type)
{
	global $url_parts;

	if (isset($url_parts))
	{
		$http_query = "$http_type " . $url_parts['path'];

		if (isset($url_parts['query'])) {
			$http_query .= '?' . $url_parts['query']; }

		$http_query .= " HTTP/1.0\r\n";
		$http_query .= 'Host: ' . $url_parts['host'] . "\r\n";
		$http_query .= "Connection: close\r\n";
		$http_query .= "\r\n";

		// Create a connection to the server
		$fp = @fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, 30);

		$buffer = '';

		// If a connection to the server was made, send out request data and save the response to $buffer
		if ($fp)
		{
			@fwrite($fp, $http_query);

			while (!feof($fp)) {
				$buffer .= fgets($fp, 4096); }

			fclose($fp);

			// Save the results as a variable and send to the parse_output() function
			$output = "<a href=\"javascript:toggle_results('r_http_req')\" class=\"toggle\"><b>HTTP Request Results ($http_type):</b></a><blockquote>";
			$output .= '<div id="r_http_req" style="display: block;">';
			$output .= '<pre>' . htmlentities(trim($buffer)) . '</pre>';
			$output .= '</div></blockquote>';

			parse_output($output);
		}
		else
		{
			echo "<a href=\"javascript:toggle_results('r_http_req')\" class=\"toggle\"><b>HTTP Request Results ($http_type):</b></a><blockquote>";
			echo '<div id="r_http_req" style="display: block;">';
			echo 'Could not connect to the specified server.';
			echo '</div></blockquote>';
		}
	}
	else
	{
		echo "<a href=\"javascript:toggle_results('r_http_req')\" class=\"toggle\"><b>HTTP Request Results ($http_type):</b></a><blockquote>";
		echo '<div id="r_http_req" style="display: block;">';
		echo 'The address in the Host field is either not a URL, or does not appear to be using the HTTP protocol.';
		echo '</div></blockquote>';
	}
}

// Function to perform a ping on a host/ip
function ping($host, $ping_count)
{
	// Set initial command to be run on the server
	$command = "ping -c $ping_count $host";

	// Send the ping command to the system.
	//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
	//   to pipe STDERR to STDOUT so if there is an error, we can see it.
	$fp = shell_exec("$command 2>&1");

	// Save the results as a variable and send to the parse_output() function
	$output = '<a href="javascript:toggle_results(\'r_ping\')" class="toggle"><b>Ping Results:</b></a><blockquote>';
	$output .= '<div id="r_ping" style="display: block;">';
	$output .= nl2br(htmlentities(trim($fp)));
	$output .= '</div></blockquote>';

	parse_output($output);
}

// Function to perform a traceroute on a host/ip
function traceroute($host)
{
	// Set initial command to be run on the server
	$command = "traceroute $host";

	// Send the traceroute command to the system.
	//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
	//   to pipe STDERR to STDOUT so if there is an error, we can see it.
	$fp = shell_exec("$command 2>&1");

	// Save the results as a variable and send to the parse_output() function
	$output = '<a href="javascript:toggle_results(\'r_traceroute\')" class="toggle"><b>Traceroute Results:</b></a><blockquote>';
	$output .= '<div id="r_traceroute" style="display: block;">';
	$output .= nl2br(htmlentities(trim($fp)));
	$output .= '</div></blockquote>';

	parse_output($output);
}

// Function to perform a tracepath on a host/ip
function tracepath($host)
{
	// Set initial command to be run on the server
	$command = "tracepath $host";

	// Send the tracepath command to the system.
	//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
	//   to pipe STDERR to STDOUT so if there is an error, we can see it.
	$fp = shell_exec("$command 2>&1");

	// Save the results as a variable and send to the parse_output() function
	$output = '<a href="javascript:toggle_results(\'r_tracepath\')" class="toggle"><b>Tracepath Results:</b></a><blockquote>';
	$output .= '<div id="r_tracepath" style="display: block;">';
	$output .= nl2br(htmlentities(trim($fp)));
	$output .= '</div></blockquote>';

	parse_output($output);
}

// Function to perform a port scan on a host/ip
function portscan($host, $ports, $scan_timeout)
{
	if (eregi("^[0-9\,\-]+$", $ports))
	{
		echo '<a href="javascript:toggle_results(\'r_portscan\')" class="toggle"><b>Portscan Results:</b></a><blockquote>';
		echo '<div id="r_portscan" style="display: block;">';
		echo "<table border='0' cellspacing='0' cellpadding='0'>";

		// split the $ports variable into an array containing the port numbers to scan
		$port_array = explode(",", $ports);

		// Save the current time (for calculating how long the scan took)
		$start_time = time();

		// Loop through the ports and check to see if they are open or not
		for ($i = 0; $i < count($port_array); $i++)
		{
			// If the current loop contains two sets of numbers with a dash separating them,
			//   it is a range of ports, so create a new loop to scan and print out each one...
			if (eregi("([0-9]+)[-]{1}([0-9]+)", $port_array[$i], $regs))
			{
				for ($x = $regs[1]; $x <= $regs[2]; $x++)
				{
					// Create a connection to the port
					$fp = @fsockopen($host, $x, $errno, $errstr, $scan_timeout);

					// If we can connect to the port, set the port status to "open",
					//   otherwise, set the port status to "closed"
					if ($fp) {
						$port_status = "<font class='open_port'>open</font>";
						fclose($fp); }
					else {
						$port_status = 'closed'; }

					// Get the description of the port
					$port_name = getservbyport($x, 'tcp');

					// Print the port status
					echo "<tr><td width='50%'>Port <b>$x</b> is $port_status.</td>";

					// If the current port has a description/default use, print it
					if ($port_name) {
						echo "<td>[$port_name]</td>"; }

					echo '</tr>';
				}
			}

			// ...otherwise, if the current loop contains just numbers, it is a single port, so scan it
			elseif (eregi("[0-9]+", $port_array[$i]))
			{
				// Create a connection to the port
				$fp = @fsockopen($host, $port_array[$i], $errno, $errstr, $scan_timeout);

				// If we can connect to the port, set the port status to "open",
				//   otherwise, set the port status to "closed"
				if ($fp) {
					$port_status = "<font class='open_port'>open</font>";
					fclose($fp); }
				else {
					$port_status = 'closed'; }

				// Get the description of the port
				$port_name = getservbyport($port_array[$i], 'tcp');

				// Print the port status
				echo "<tr><td width='50%'>Port <b>$port_array[$i]</b> is $port_status.</td>";

				// If the current port has a description/default use, print it
				if ($port_name) {
					echo "<td>[$port_name]</td>"; }

				echo '</tr>';
			}
		}

		// Save the current time again (for calculating how long the scan took)
		$end_time = time();

		// Calculate the elapsed time during the port scan
		$time_diff = $end_time - $start_time;
		$mins = date('i', $time_diff);
		$secs = date('s', $time_diff);

		// If the the elapsed time during the port scan was less than a second, set it as taking 1 second
		//   (it obviously has to take some amount of time)
		if (($mins == '00') && ($secs == '00')) {
			$secs = '01'; }

		// Print the elapsed time of the port scan
		echo "<tr><td colspan='2'><br>Portscan completed in <b>$mins</b> minutes and <b>$secs</b> seconds.</td></tr>";
		echo '</table></div></blockquote>';
	}
	else
	{
		echo '<a href="javascript:toggle_results(\'r_portscan\')" class="toggle"><b>Portscan Results:</b></a><blockquote>';
		echo '<div id="r_portscan" style="display: block;">';
		echo 'Invalid characters in the Portscan field.';
		echo '</div></blockquote>';
	}
}

// Function to perform an nmap on a host/ip
function nmap($host, $nmap_options)
{
	if (eregi("^[a-z0-9 @_:,-\.\*\/]+$", $nmap_options))
	{
		// Set initial command to be run on the server
		$command = "nmap $nmap_options $host";

		// Send the nmap command to the system.
		//   Normally, the shell_exec function does not report STDERR messages.  The "2>&1" option tells the system
		//   to pipe STDERR to STDOUT so if there is an error, we can see it.
		$fp = shell_exec("$command 2>&1");

		echo '<a href="javascript:toggle_results(\'r_nmap\')" class="toggle"><b>Nmap Results:</b></a><blockquote>';
		echo '<div id="r_nmap" style="display: block;">';
		echo nl2br(htmlentities(trim($fp)));
		echo '</div></blockquote>';
	}
	else
	{
		echo '<a href="javascript:toggle_results(\'r_nmap\')" class="toggle"><b>Nmap Results:</b></a><blockquote>';
		echo '<div id="r_nmap" style="display: block;">';
		echo 'Invalid characters in the Nmap field.';
		echo '</div></blockquote>';
	}
}

// Function to parse the results of various commands to create shortcut links
function parse_output($input)
{
	// Create a regular expression to validate email addresses
	//   (credit goes to "bobocop at bobocop dot cz" from "eregi" comments on php.net for this regular expression)
	$user = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
	$domain = '([a-z]([-a-z0-9]*[a-z0-9]+)?)';
	$regex = $user . '+(\.' . $user . '+)*@(' . $domain . '{1,63}\.)+' . $domain . '{2,63}';

	// Convert IP addresses to links
	$output = eregi_replace("([0-9]{1,3}(\.[0-9]{1,3}){3})", "<a href=\"javascript:enter_ip('\\0');\">\\0</a>", $input);
	
	// Convert email addresses to links
	$output = eregi_replace($regex, "<a href=\"mailto:\\0\">\\0</a>", $output);

	// Print the results
	echo $output;
}


// Set the default value for certain variables
if (!$host) {
	$host = 'Enter Host or IP'; }
if (!$whois_ip_server) {
	$whois_ip_server = 'whois.arin.net'; }
if (!$whois_domain_server) {
	$whois_domain_server = 'whois-servers.net'; }
if (!$ping_count) {
	$ping_count = '4'; }
if (!$scan_timeout) {
	$scan_timeout = '1'; }

// Call the get_ip() function and store the results in $ip
$ip = get_ip();

?>


<html>
<head>
<title>PHP Net Tools</title>

<style type="text/css">
body,table
{
	font-family: Veranda, Arial, Helvetica;
	font-size: 12;
	margin-top: 0;
}

td
{
	padding: 0 2 0 2;
}

A:link, A:visited, A:active
{
	color: #0000FF;
	text-decoration: none;
}

A:hover
{
	color: #000000;
	text-decoration: underline;
}

A.toggle
{
	color: #000000;
	text-decoration: none;
}

tr.version
{
	color: #909090;
	font-size: 10;
	font-family: Lucida Console, Veranda, Arial, Helvetica;
}

tr.title
{
	color: #FFFFFF;
	text-align: center;
	font-weight: bold;
	background-color: #00008B;
}

tr.options
{
	background-color: #87CEEB;
}

td.host
{
	font-weight: bold;
	background-color: #9999FF;
	padding: 2 0 2 0;
}

.open_port
{
	color: #FF0000;
	font-weight: bold;
}
</style>

<script language="javascript">
function check_focus(val)
{
	if (val.value == "Enter Host or IP") {
		val.value = ""; }
}

function enter_ip(ip)
{
	document.forms[0].host.value = ip;
}

// Function to display help popup box - [pixel width, pixel height, display title, main description text, wikipedia url, manpage url]
function help(box_width, box_height, box_title, box_msg, box_wiki, box_manpage)
{	
	var box_left = (screen.width - box_width) / 2;
	var box_top = (screen.height - box_height) / 2;

	box_header = "<html><head><title>" + box_title + "</title>";
	box_header += "<style type='text/css'>body { font-family: Veranda, Arial, Helvetica; font-size: 14; background-color: #87E8EB; } ";
	box_header += "A:link, A:visited, A:active { color: #0000FF; text-decoration: none; } A:hover { font-weight: bold; }</style>";
	box_header += "</head><body>";

	box_footer = "</body></html>";

	if (box_wiki != "")
	{
		box_msg += "<br><br><br>";
		box_msg += "<a href='" + box_wiki + "' target='_wiki'>Wikipedia Article</a>";
	}

	if (box_manpage != "")
	{
		if (box_wiki == "") {
			box_msg += "<br><br>"; }

		box_msg += "<br><a href='" + box_manpage + "' target='_manpage'>Manual Page</a>";
	}

	box_text = box_header + box_msg + box_footer;

	var help_box = window.open('','help_box','width=' + box_width + ',height=' + box_height + ',left=' + box_left + ',top=' + box_top + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=yes');
	help_box.document.write(box_text);
	help_box.focus();
}

function toggle_results(results_id)
{
	var el = document.getElementById(results_id);

	if (el.style.display == "block") {
		el.style.display = "none"; }
	else {
		el.style.display = "block"; }
}

function toggle_all(state)
{
	var fields = new Array('r_whois_ip', 'r_whois_domain', 'r_nslookup', 'r_dig', 'r_http_req', 'r_ping', 'r_traceroute', 'r_tracepath', 'r_portscan', 'r_nmap');

	for (i = 0; i < fields.length; i++)
	{
		var el = document.getElementById(fields[i]);

		if (el)
		{
			if (state == "collapse") {
				el.style.display = "none"; }
			else {
				el.style.display = "block"; }
		}
		else
		{
			continue;
		}
	}
}
</script>
</head>

<body>

<form name="tools" action="<? echo $_SERVER['PHP_SELF']; ?>" method="post">
<table align="center" border="0" cellspacing="0" cellpadding="0">
  <tr class="version" height="14">
    <td align="left" colspan="2">Copyright (C) 2006 Eric Robertson</td>
    <td width="30"></td>
    <td align="right" colspan="2">v<? echo $version; ?></td>
  </tr>
  <tr class="title" height="20">
    <td colspan="2">Host Information</td>
    <td width="30"></td>
    <td colspan="2">Host Connectivity</td>
  </tr>
  <tr class="options">
    <td width="290"><input type="checkbox" name="resolve" <? if (isset($resolve)) { echo "checked"; } ?>>Resolve Host / Reverse Lookup</td>
    <td width="20" align="right">[ <a href="javascript:help('400', '150', 'Reverse Lookup Help', '<i>Reverse Lookup</i> is a term referring to using DNS lookups to determine what IP address is associated with a given hostname.', 'http://en.wikipedia.org/wiki/Reverse_DNS_lookup' ,'')">?</a> ]</td>
    <td width="30"></td>
    <td width="290"><input type="checkbox" name="ping" <? if (isset($ping)) { echo "checked"; } ?>>Ping &nbsp;-&nbsp;  count: 
          <input type="text" name="ping_count" size="1" value="<? echo $ping_count; ?>">
    </td>
    <td width="20" align="right">[ <a href="javascript:help('400', '150', 'Ping Help', '<i>Ping</i> is a computer network tool which provides a basic test of whether a particular host is operating properly and is reachable on the network. Ping provides estimates of the round-trip time and packet loss rate between hosts.', 'http://en.wikipedia.org/wiki/Ping', 'http://man.linuxquestions.org/index.php?query=ping&section=0&type=2')">?</a> ]</td>
  </tr>
  <tr class="options">
    <td><input type="checkbox" name="ip_to_country" <? if (isset($ip_to_country)) { echo "checked"; } ?>>Get Country</td>
    <td align="right">[ <a href="javascript:help('500', '160', 'Get Country Help', 'The <i>Get Country</i> option will attempt to determine the country the specified host is located in, and should work with almost any host, although it may not be foolproof.<br><br>This feature may be slow the first time it is used, as it must download a large <a href=\'http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1-semic.txt\' target=\'_iso\'>country list file</a> from the ISO Standards website.  After this first use, the file gets cached on the server and will be much faster.', '', '')">?</a> ]</td>
    <td></td>
    <td><input type="checkbox" name="trace" <? if (isset($trace)) { echo "checked"; } ?>>Traceroute</td>
    <td align="right">[ <a href="javascript:help('400', '160', 'Traceroute Help', '<i>Traceroute</i> (<i>tracert</i> on Windows systems) is a utility which allows the user to determine the route packets take to reach a particular host.  It sends out UDP datagrams and uses the returning ICMP packets to produce a list of hosts that have been traversed en route to the destination.', 'http://en.wikipedia.org/wiki/Traceroute', 'http://man.linuxquestions.org/index.php?query=traceroute&section=0&type=2')">?</a> ]</td>
  </tr>
  <tr class="options">
    <td><input type="checkbox" name="whois_ip" <? if (isset($whois_ip)) { echo "checked"; } ?>>Whois (IP) &nbsp;
          <input type="text" name="whois_ip_server" size="20" value="<? echo $whois_ip_server; ?>">
    </td>
    <td align="right">[ <a href="javascript:help('500', '210', 'Whois (IP) Help', '<i>Whois</i> is an internet protocol which is widely used for querying a database in order to determine the owner of a domain name, or an IP address on the Internet.<br><br>The Whois (IP) server field can also accept a port.  If you need to specify a port, just add a colon to the end of the server followed by the port number.<br><br><b>Ex:</b>&nbsp; whois.arin.net:43', 'http://en.wikipedia.org/wiki/Whois' ,'http://man.linuxquestions.org/?query=whois&section=0&type=2')">?</a> ]</td>
    <td></td>
    <td><input type="checkbox" name="tracepath" <? if (isset($tracepath)) { echo "checked"; } ?>>Tracepath</td>
    <td align="right">[ <a href="javascript:help('430', '180', 'Tracepath Help', '<i>Tracepath</i> is a command similar to <i>Traceroute</i> except it usually does not require specific user priveledges on the server.<br><br>If you are unable to run the Traceroute command, try Tracepath.  Keep in mind, however, that not all servers will have Tracepath installed.', 'http://en.wikipedia.org/wiki/Tracepath', 'http://man.linuxquestions.org/index.php?query=tracepath&section=0&type=2')">?</a> ]</td>
  </tr>
  <tr class="options">
    <td><input type="checkbox" name="whois_domain" <? if (isset($whois_domain)) { echo "checked"; } ?>>Whois (Domain) &nbsp;
          <input type="text" name="whois_domain_server" size="20" value="<? echo $whois_domain_server; ?>">
    </td>
    <td align="right">[ <a href="javascript:help('400', '160', 'Whois (Domain) Help', '<i>Whois</i> is an internet protocol which is widely used for querying a database in order to determine the owner of a domain name, or an IP address on the Internet.', 'http://en.wikipedia.org/wiki/Whois', 'http://man.linuxquestions.org/?query=whois&section=0&type=2')">?</a> ]</td>
    <td></td>
    <td><input type="checkbox" name="portscan" <? if (isset($portscan)) { echo "checked"; } ?>>Portscan &nbsp;
          <input type="text" name="ports" size="20" value="<? echo $ports; ?>">
    </td>
    <td align="right">[ <a href="javascript:help('500', '200', 'Portscan Help', 'A <i>port scanner</i> is a piece of software designed to search a network host for open ports. This is often used by administrators to check the security of their networks.<br><br>Use commas (,) to separate ports and use a dash (-) to indicate a range of ports.  You can mix commas and dashes.<br><br><b>Ex:</b>&nbsp; \'21-23,80\' will scan ports 21,22,23 and 80.', 'http://en.wikipedia.org/wiki/Port_scanner', '')">?</a> ]</td>
  </tr>
  <tr class="options">
    <td><input type="checkbox" name="ns" <? if (isset($ns)) { echo "checked"; } ?>>NS Lookup</td>
    <td align="right">[ <a href="javascript:help('400', '160', 'NS Lookup Help', 'The <i>nslookup</i> command uses DNS lookup to find the IP addresses of a particular computer.  The functionality of nslookup has been depreciated and it is recommended that <i>dig</i> be used instead (<a href=\'http://homepages.tesco.net./~J.deBoynePollard/FGA/nslookup-flaws.html\' target=\'_nslokup_flaws\'>more info</a>).', 'http://en.wikipedia.org/wiki/Nslookup', 'http://man.linuxquestions.org/index.php?query=nslookup&section=0&type=2')">?</a> ]</td>
    <td></td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Timeout: &nbsp;<input type="text" name="scan_timeout" size="1" value="<? echo $scan_timeout; ?>"> second(s)</td>
    <td></td>
  </tr>
  <tr class="options">
    <td><input type="checkbox" name="dig" <? if (isset($dig)) { echo "checked"; } ?>>Dig &nbsp;
          <select name="dig_class">
<?
		$dig_class_array = Array('ANY', 'A', 'IN', 'MX', 'NS', 'SOA', 'HINFO', 'AXFR', 'IXFR');

		for ($i = 0; $i < count($dig_class_array); $i++)
		{
			echo "<option value=\"$dig_class_array[$i]\"";

			if (!isset($_POST['dig_class'])) {
				$dig_class = 'ANY'; }

			if ($dig_class_array[$i] == $dig_class) {
				echo ' selected'; }

			echo ">$dig_class_array[$i]</option>";
		}
?>
          </select> &nbsp;
          <input type="text" name="dig_server" size="20" value="<? echo $dig_server; ?>"> &nbsp;
    </td>
    <td align="right">[ <a href="javascript:help('500', '200', 'Dig Help', '<i>Dig</i> (Domain Information Groper) is a flexible tool for interrogating DNS name servers.  It performs DNS lookups and displays the answers that are returned from the name server that was queried.  Most DNS administrators use dig to troubleshoot DNS problems because of its flexibility, ease of use and clarity of output.<br><br>Use the drop-down menu to select the desired dig query type and the text box to enter a specific DNS server to query (optional).', '', 'http://man.linuxquestions.org/index.php?query=dig&section=0&type=2')">?</a> ]</td>
    <td></td>
    <td><input type="checkbox" name="nmap" <? if (isset($nmap)) { echo "checked"; } ?>>Nmap &nbsp;
          <input type="text" name="nmap_options" size="30" value="<? echo $nmap_options; ?>">
    </td>
    <td align="right">[ <a href="javascript:help('500', '180', 'Nmap Help', '<i>Nmap</i> is an advanced portscan utility with many extra features.  Some of these options require root priveledges on the server and will not work in this script.  If you attempt to use one such option, an error will be returned.<br><br>Also, not all servers have nmap installed or allow its use.  An error will also be returned in these cases.', 'http://en.wikipedia.org/wiki/Nmap', 'http://www.insecure.org/nmap/data/nmap_manpage.html')">?</a> ]</td>
  </tr>
  <tr class="options">
    <td><input type="checkbox" name="http_req" <? if (isset($http_req)) { echo "checked"; } ?>>HTTP Request &nbsp;
          <select name="http_type">
<?
		$http_type_array = Array('HEAD', 'GET', 'OPTIONS', 'TRACE');

		for ($i = 0; $i < count($http_type_array); $i++)
		{
			echo "<option value=\"$http_type_array[$i]\"";

			if (!isset($_POST['http_type'])) {
				$http_type = 'HEAD'; }

			if ($http_type_array[$i] == $http_type) {
				echo ' selected'; }

			echo ">$http_type_array[$i]</option>";
		}
?>
          </select> &nbsp;
    </td>
    <td align="right">[ <a href="javascript:help('500', '150', 'HTTP Request Help', 'Use the Hypertext Transfer Protocol (HTTP) to retreive various information about a web server.<br><br>For a description of the different request methods, please visit the Wikipedia article linked below.', 'http://en.wikipedia.org/wiki/Http_request', '')">?</a> ]</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td class="host" align="center" colspan="5">Host: &nbsp;&nbsp;
        <input type="text" name="host" size="30" value="<? echo $host; ?>" onFocus="check_focus(this)"> &nbsp;
        <input type="submit" name="submit" value="Get Info">
    </td>
  </tr>
  <tr>
    <td align="center" colspan="5"><? echo "Your IP: &nbsp;&nbsp;<a href=\"javascript:enter_ip('$ip');\">$ip</a>"; ?>
  </tr>
</table>
</form>

<br>

<?
// If the user clicked the "Get Info" button...
if ($_POST['submit'])
{
	// If no options were selected, print an error
	if (!isset($resolve) && !isset($ip_to_country) && !isset($whois_ip) && !isset($whois_domain) && !isset($ns) && !isset($dig) && !isset($http_req) && !isset($ping) && !isset($trace) && !isset($tracepath) && !isset($portscan) && !isset($nmap)) {
		echo '<br><br>You must select at least one option to perform.';
		exit; }

	// If there was no host entered, print an error
	if (($host == 'Enter Host or IP') || ($host == "")) {
		echo '<br><br>You must enter a valid Host or IP address.';
		exit; }

	// Make sure there are no invalid characters in the host field - if so, print error and stop script
	if (!eregi("^[-a-z0-9\.\?\&:/@#]+$", $host)) {
		echo '<blockquote>Invalid character(s) in the Host field.</blockquote>';
		exit; }

	// Print links to collapse and expand all results
	echo '<font style="font-size: 8pt;">';
	echo '[<a href="javascript:toggle_all(\'collapse\')">Collapse All</a>] ';
	echo '[<a href="javascript:toggle_all(\'expand\')">Expand All</a>]';
	echo '</font><br><br><br><br>';

	// If a URL was entered into the Host field, call the split_url() function
	if (eregi("^https?://", $host))
	{
		split_url($host);

		// Re-set the $host variable to just the domain portion of the URL so the other functions can do their thing
		$host = $url_parts['host'];
	}

	// If the option is set to log the user info, call the log_user() function
	if ($enable_log_user == TRUE) {
		log_user($host); }
}

// If an option is set, call its respective function
if (isset($resolve)) {
	resolve($host); }
if (isset($ip_to_country)) {
	ip_to_country(); }
if (isset($whois_ip)) {
	whois_ip($whois_ip_server, '-1', 'TRUE'); }
if (isset($whois_domain)) {
	whois_domain($host, $whois_domain_server); }
if (isset($ns)) {
	nslookup($host); }
if (isset($dig)) {
	dig($host, $dig_class, $dig_server); }
if (isset($http_req)) {
	http_req($http_type); }
if (isset($ping)) {
	ping($host, $ping_count); }
if (isset($trace)) {
	traceroute($host); }
if (isset($tracepath)) {
	tracepath($host); }
if (isset($portscan)) {
	portscan($host, $ports, $scan_timeout); }
if (isset($nmap)) {
	nmap($host, $nmap_options); }
?>

</body>
</html>