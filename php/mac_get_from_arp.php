<?php

//First get the IP address then use the
//DOS command + only get row with client IP address
//This takes only one line of the ARP table instead
//of what could be a very large table of data to 
//hopefull give a small speed/performance advantage

$remoteIp = rtrim($_SERVER['REMOTE_ADDR']);
$location = rtrim(`arp -a $remoteIp`);
print_r($remoteIp.$location);//display

//reduce no of white spaces then 
//Split up into array element by white space
$location = preg_replace('/\s+/', 's', $location);
$location = split('\s',$location);// 

$num=count($location);//get num of array elements
$loop=0;//start at array element 0
while ($loop<$num)
{
//mac address is always one after the 
//IP after inserting the firstline
//(preg_replace) line above.
if ($location[$loop]==$remoteIp)
{
$loop=$loop+1;
echo "<h1>Client MAC Address:- ".$location[$loop]."</h1>";
$_SESSION['MAC'] = $loop;
return;
}
else {$loop=$loop+1;}
}



function returnMacAddress() {
$location = `which arp`;
$location = rtrim($location);

// Execute the arp command and store the output in $arpTable
$arpTable = `$location -n`;


$arpSplitted = split("\n",$arpTable);


$remoteIp = $GLOBALS['REMOTE_ADDR'];
$remoteIp = str_replace(".", "\\.", $remoteIp);

// Cicle the array to find the match with the remote ip address
foreach ($arpSplitted as $value) {
// Split every arp line, this is done in case the format of the arp
// command output is a bit different than expected
$valueSplitted = split(" ",$value);

foreach ($valueSplitted as $spLine) {
if ( preg_match("/$remoteIp/",$spLine) ) {
$ipFound = true;
}

// The ip address has been found, now rescan all the string
// to get the mac address

if ($ipFound) {
// Rescan all the string, in case the mac address, in the string
// returned by arp, comes before the ip address (you know,Murphy's laws)
reset($valueSplitted);

foreach ($valueSplitted as $spLine) {
if (
preg_match("/[0-9a-f][0-9a-f][:-][0-9a-f][0-9a-f][:-][0-9a-f][0-9a-f][:-][0-9a-f][0-9a-f][:-]".
"[0-9a-f][0-9a-f][:-][0-9a-f][0-9a-f]/i",$spLine)) {
return $spLine;
}
}
}

$ipFound = false;
}
}

return false;
}

echo returnMacAddress();
?>