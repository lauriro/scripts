<?php


$_SERVER['PHP_AUTH_USER']='admin';
$_SERVER['PHP_AUTH_PW']='Tere';

$vcnt = xcache_count(XC_TYPE_VAR);


$pcnt = xcache_count(XC_TYPE_PHP);
for ($i = 0; $i < $pcnt; $i ++) {
	$data = xcache_info(XC_TYPE_PHP, $i);
	if ($type === XC_TYPE_PHP) {
		$data += xcache_list(XC_TYPE_PHP, $i);
	}

	echo "php#$i ";
	echo (100 - (int) ($data['avail'] / $data['size'] * 100)) . '% used';
	$data['type'] = XC_TYPE_PHP;
	$data['cacheid'] = $i;
	$cacheinfos[] = $data;

	//print_r($data);


}
for ($i = 0; $i < $vcnt; $i ++) {
	$data = xcache_info(XC_TYPE_VAR, $i);
	if ($type === XC_TYPE_VAR) {
		$data += xcache_list(XC_TYPE_VAR, $i);
	}
	$data['type'] = XC_TYPE_VAR;
	$data['cache_name'] = "var#$i";
	$data['cacheid'] = $i;
	$cacheinfos[] = $data;
}












function number_formats($a, $keys)
{
	foreach ($keys as $k) {
		$a[$k] = number_format($a[$k]);
	}
	return $a;
}

function size($size)
{
	$size = (int) $size;
	if ($size < 1024)
		return number_format($size, 2) . ' b';

	if ($size < 1048576)
		return number_format($size / 1024, 2) . ' K';

	return number_format($size / 1048576, 2) . ' M';
}

function age($time)
{
	if (!$time) return '';
	$delta = REQUEST_TIME - $time;

	if ($delta < 0) {
		$delta = -$delta;
	}
	
  	static $seconds = array(1, 60, 3600, 86400, 604800, 2678400, 31536000);
	static $name = array('s', 'm', 'h', 'd', 'w', 'M', 'Y');

	for ($i = 6; $i >= 0; $i --) {
		if ($delta >= $seconds[$i]) {
			$ret = (int) ($delta / $seconds[$i]);
			return $ret . ' ' . $name[$i];
		}
	}

	return '0 s';
}



?>