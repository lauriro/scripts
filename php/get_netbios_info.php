<?php

error_reporting(E_ALL);

/* get the ip of the client */
if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
{
$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
$ip = $_SERVER["REMOTE_ADDR"]; 
}
echo 'ip : '.$ip.'<br>';

function _get_netbios_info($ip){
	$domain=$username=$null=false;
	if($fp=fsockopen('udp://'.$ip,137)){
		fwrite($fp,"\x80b\0\0\0\1\0\0\0\0\0\0 CKAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\0\0!\0\1");
		stream_set_timeout($fp,1);
		if($data=fread($fp, 256)){
			$nbrec=ord($data[56]);
			for($i=0;$i<$nbrec;$i++) {
				$type=ord($data[72+18*$i]);
				${($type==3?'username':($type==30?'domain':'null'))}=trim(substr($data,57+18*$i,15));
			}
		}
		fclose($fp);
	}
	return array($domain,$username);
}

list($domain,$username)=_get_netbios_info($ip);

if($username) echo 'login: '.strtoupper($domain).'/'.strtolower($username);
else echo 'Tere külaline! Sinu ära tundmisega ma ei saanud hakkama.'

?>