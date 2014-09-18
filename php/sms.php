<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> SMS </title>
</head>

<body>
SMS sõnum:<br><form name="sms" method=post action="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin:0">
<input type="text" name="s6num" size=50 maxlength=143>
<input type="submit" value="Saada">

<b><?php

if (isset($_POST['s6num'])) {
	echo '<hr>';
	if($_POST['s6num']!=''){
		$message=stripslashes($_POST['s6num']);

		$ip=$host=$proxy='';
		if(getenv('HTTP_X_FORWARDED_FOR')){
			$ip_list=explode(',',getenv('HTTP_X_FORWARDED_FOR').','.getenv('REMOTE_ADDR'));
			$ip=array_shift($ip_list);$proxy_list=array();
			foreach($ip_list as $a){
				$proxy_list[]=$a.' ['.($a!='unknown'?@gethostbyaddr($a):$a).']';
			}
			$proxy=implode(', ',array_reverse($proxy_list));
		}else $ip=getenv('REMOTE_ADDR');
		$host=($ip!='unknown')?@gethostbyaddr($ip):$ip;

		if(mail('ruutusms@neti.ee','[sms] '.$message,$message,
			"From: sms@sms.ee\r\n".
			"Reply-To: Lauri <ruutu@neti.ee>\r\n".
			"X-x: --- --- --- --- --- --- --- --- ---\r\n".
			"X-x: \r\n".
			"X-x:   Time: ".date('H:i:s - d.m.Y')."\r\n".
			"X-x:     IP: $ip [$host]\r\n".
			($proxy!=''?"X-x:  Proxy: $proxy\r\n":'').
			"X-x: \r\n".
			"X-x: --- --- --- --- --- --- --- --- ---\r\n".
			"X-Mailer: i.am.smart.ee\r\n")) echo 'SMS saadetud! :)';
		else echo 'Tekkis viga sõnumi saatmisel. :(';

	}
	else echo 'Kirjuta ikka sõnum ka. :(';
}
?></b>

</form><hr><i>SMS - <b>S</b>hort <b>M</b>essage <b>S</b>ervice</i>
</body>
</html>