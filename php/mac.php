MAC aadress:<br><form method=get action="mac.php" style="margin:0"><?php


// http://standards.ieee.org/regauth/oui/oui.txt

echo '<input type="text" name="mac" value="'.(isset($_GET['mac'])?$_GET['mac']:'').'"><input type="submit">';


function flush_mac_list($file,$output){
	if(file_exists($output)){
		$data=include $output;
	}else $data=array();
	if(!is_array($data)) $data=array();
	if(file_exists($file)){
		$temp=file($file);
		echo 'Ridu: '.count($temp);
		foreach($temp as $k=>$v){
			list($mac,$text)=explode(' ',trim($v),2);
			$data[$mac]=$text;
		}
		write_data_file($data,$output);
	}else die("Cannot find file ($file)");
}

function flush_oui_file($output){
	if(file_exists($output)){
		$data=include $output;
	}else $data=array();
	if(!is_array($data)) $data=array();
	$temp=file("http://standards.ieee.org/regauth/oui/oui.txt") or die("Cannot open file");
	echo 'Ridu: '.count($temp);
	$muster="/^([0-9a-f]{2}-[0-9a-f]{2}-[0-9a-f]{2})[\t ]+\(hex\)[\t ]+([^\r\n]+)/im";
	foreach($temp as $k=>$v){
		if(preg_match($muster,$v,$results)){
			$data[$results[1]]=$results[2];
		}
	}
	write_data_file($data,$output);
}

function write_data_file($data,$output){
	if($handle=fopen($output,'w')){
		fwrite($handle,'<?php $d=array();'."\n");
		ksort($data);
		foreach($data as $k=>$v){
			fwrite($handle,'$d["'.strtoupper($k).'"]="'.str_replace('"','\"',$v).'";'."\n");
		}
		fwrite($handle,'return $d; ?>');
		fclose($handle);
	}else die("Cannot open file ($output)");
}

$output='mac_data.php';

if(isset($_GET['flush_mac_list'])) flush_mac_list('mac_list.txt',$output);
if(isset($_GET["update"])) flush_oui_file($output);


if(isset($_GET['mac'])){
	echo '<hr>';
	$mac=strtr(strtoupper(trim($_GET['mac'],"\t\n\r\0\x0B-: ")),array(' '=>'-',':'=>'-')).'-';
	if(preg_match('/^(([0-9A-F]){2}-){3,6}$/',$mac)){
		$list=array();
		$exists=false;
		$abi=explode('-',trim($mac,'-'));
		for($i=count($abi);$i>2;$list[]=implode('-',array_slice($abi,0,$i)),$i--);
		$data=include $output;
		foreach($list as $mac){
			if(isset($data[$mac])){
				$exists=true;
				echo '<b title="['.$mac.']">'.$data[$mac].'</b><br>';
			}
		}
		if(!$exists) echo 'Ei leidnud MAC-i tootjat';
	}else echo 'Ei tundnud MAC aadressi mustrit ära';
}


?><hr><i>MAC address - <b>M</b>edia <b>A</b>ccess <b>C</b>ontrol address</i>
</form>