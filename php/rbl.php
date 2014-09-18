<?php

$server_list = array(
	'bl.estpak.ee'=>'',
	'bl.spamcop.net'=>'',
	'blackholes.mail-abuse.org'=>'',
	'sbl.spamhaus.org'=>'',
	'sbl-xbl.spamhaus.org'=>'',
	'l1.spews.dnsbl.sorbs'=>'',
	'dnsbl.sorbs.net'=>'',
	'dynablock.wirehub.net'=>'',
	'list.dsbl.org'=>'',
	'rbl-plus.mail-abuse.org'=>'',
	'relays.ordb.org'=>'',
	'spamguard.leadmon.net'=>'',
);

$a='';
if (getenv('HTTP_CLIENT_IP')&&strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown'))
	$a.=getenv('HTTP_CLIENT_IP');
else if (getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'),'unknown'))
	$a.=getenv('REMOTE_ADDR');
else if (!empty($_SERVER['REMOTE_ADDR'])&&strcasecmp($_SERVER['REMOTE_ADDR'],'unknown'))
	$a.=$_SERVER['REMOTE_ADDR'];
if (getenv('HTTP_X_FORWARDED_FOR')) $a = getenv('HTTP_X_FORWARDED_FOR').','.$a;
$b=explode(',',$a);
$a=array_shift($b);
$c=array();
foreach($b as $d){
	$c[]=$d.' ['.($d!='unknown'?@gethostbyaddr($d):$d).']';
}
$proxy=implode(', ',array_reverse($c));
$uhost=($a!='unknown')?@gethostbyaddr($a):$a;
$uip=$a;

function win_checkdnsrr($hostname, $rectype) {
	if (!empty($hostname)) {
		exec("nslookup -type=$rectype -timeout=1 $hostname", $output);
		foreach ($output as $line) {
			if (eregi($hostname, $line)) return true;
		}
	}
	return false;
}

function dnsblquery ($ip) {
	global $server_list;
	if ($ip) {
		$ips = explode(".", $ip);
		$ipreverse = "$ips[3].$ips[2].$ips[1].$ips[0]";
		$bl_list=$ok_list=array();
		$t=array_keys($server_list);
		$func = (function_exists('checkdnsrr')?'':'win_').'checkdnsrr';
		foreach ($t as $k=>$v) {
			if ($func("$ipreverse.$v", "A")) {
				$bl_list[]=$v;
			}
		}
		return $bl_list;
	}
	return false;
}

$ip=(!empty($_POST['ip'])?$_POST['ip']:'');

?>
IP aadress:<br><form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin:0">
<input type=text name="ip" value="<?php echo $ip; ?>"> <input type=submit class="browse" value=" testi "></form>
<?php

if (!empty($ip)) {
	echo '<hr>Testiti IP-d: '.$ip.'<br>';
	$bl = dnsblquery($ip);
	if (count($bl) > 0) {
		echo '<font color="red">';
		foreach ($bl as $line) {
			echo "<b>$line </b><i>blacklistis</i>! <br>";
		}
		echo '</font>';
	}else echo '<font color="green"><b>Ei leitud ühestki listist ;)</b></font>';
	echo '<br><i>Kontrolliti '.count($server_list).' listist.</i>';
}


?><hr><i>RBL - <b>R</b>eal-time <b>B</b>lackhole <b>L</b>ist</i>