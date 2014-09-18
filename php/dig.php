<?php

$ns_list=array(
	'dns.estpak.ee',
	'.. dns2.estpak.ee',
	'.. dns3.estpak.ee',
	'.. dns4.estpak.ee',
	'ns.elion.ee',
	'.. ns2.elion.ee',
	'ns.online.ee',
	'.. ns2.online.ee',
	'ns.starman.ee',
	'ns.uninet.ee',
	'ns.tele2.ee',
	'ns.eenet.ee',
	'ns.zone.ee',
);

$elion_mx_list=array(
	'mail.elion.ee','smtp.elion.ee',
	'zen.estpak.ee','194.126.101.100',
	'mail.atlasasp.ee','194.126.100.228',
	'mail2.atlasasp.ee','194.126.100.226',
);

$elion_wb_list=array(
	'neti.neti.ee','194.126.101.73','194.126.101.91','194.126.101.67','194.126.101.79',
	'195.250.183.246','194.126.101.94',
	'194.126.101.109',	'kodu.neti.ee',
	'194.126.101.70',		'centrum.neti.ee',
	'194.126.101.73',		'web.neti.ee',
	'194.126.101.75',		'light.neti.ee',
	'194.126.101.88',		'webs.neti.ee',
	'194.126.124.169',	'saurus.neti.ee',
	'194.126.124.179',	'kontor.neti.ee',
	'194.126.101.112',	'virtual.neti.ee',
	'','',
);

$elion_ns_list=array(
	'ns.elion.ee','213.168.18.146',
	'ns2.elion.ee','195.50.193.163',
);

$info=$data='';
$adr=(!empty($_POST['adr'])?$_POST['adr']:'');
$server=(!empty($_POST['server'])?$_POST['server']:'ns.elion.ee');
if(!empty($_POST['adr'])){
	$mx=$ns=$wb=array();
	$webadr=preg_replace("%[^0-9a-zA-Z\.\-]%",'',(strpos($adr,'@')?substr($adr,strpos($adr,'@')+1):$adr));

	exec("dig @".$server." any $webadr",$buffer);
	$muster='/^\s*(\S+)[\s.\din]+(MX|NS|A|CNAME)\s*(\d+)?\s+([\S]+)$/i';

	foreach($buffer as $k=>$v){
		if(preg_match($muster,$v,$matches)){
			$serv=trim($matches[4]," \t.");
			$matches[1]=trim($matches[1]," \t.");
			if($matches[2]=='MX'){
				if(!isset($elion_mx))$elion_mx=false;
				$mx[$matches[3].'.'.$k]=' ['.$matches[3].'] '.$serv.' ';
				if(in_array($serv,$elion_mx_list)) $elion_mx=true;
			}elseif($matches[1]==$webadr&&in_array($matches[2],array('A','CNAME'))){
				if(!isset($elion_wb))$elion_wb=false;
				$wb[$serv]=$serv;
				if(in_array($serv,$elion_wb_list)) $elion_wb=true;
			}elseif($matches[2]=='NS'&&preg_match('/'.preg_quote($matches[1]).'$/i',$webadr)&&strlen($matches[1])>5){
				if(!isset($elion_ns))$elion_ns=false;
				$ns[$serv]=$serv;
				if(in_array($serv,$elion_ns_list)) $elion_ns=true;
			}
		}
	}

	ksort($mx,SORT_NUMERIC);ksort($ns);

	$bg=$title='';
	$txt='e-maili kirje puudu';
	if(isset($elion_mx)){
		$bg=($elion_mx?'lightgreen':'#ff9b9b');
		$txt=($elion_mx?'e-mail asub elioni serveris':'e-mail ei asu elioni serveris');
		$title=join("\n",$mx);
	}
	$info.='<tr><td bgColor="'.$bg.'" class=tulem align=center title="'.$title.'"><b>'.$txt.'</b></td></tr>';
	$bg=$title='';
	$txt='web-i kirje puudu';
	if(isset($elion_wb)){
		$bg=($elion_wb?'lightgreen':'#ff9b9b');
		$txt=($elion_wb?'veebileht asub elioni serveris':'veebileht ei asu elioni serveris');
		$title=join("\n",$wb);
	}
	$info.='<tr><td bgColor="'.$bg.'" class=tulem align=center title="'.$title.'"><b>'.$txt.'</b></td></tr>';
	$bg=$title='';
	$txt='nimeserveri kirje puudu';
	if(isset($elion_ns)){
		$bg=($elion_ns?'lightgreen':'#ff9b9b');
		$txt=($elion_ns?'domeen on elioni nimeserveris':'domeen ei ole elioni nimeserveris');
		$title=join("\n",$ns);
	}
	$info.='<tr><td bgColor="'.$bg.'" class=tulem align=center title="'.$title.'"><b>'.$txt.'</b></td></tr>';
	$info='<fieldset align=center style="padding: 10px; width: 100%; height: 100%"><legend style="font-family: verdana; font-size: 11px">&nbsp;<b>tulemus</b>&nbsp;</legend><table width="100%" height="90%" border=1 cellpadding=2 cellspacing=1>'.$info.'</table></fieldset>';
	$data='<tr><td colspan=2><fieldset align=center style="padding: 10px; width: 100%"><legend style="font-family: verdana; font-size: 11px">&nbsp;<b>dig</b>&nbsp;</legend><textarea readonly rows=20 cols=5 style="width:100%; font-size: 12px;">'.join("\n",$buffer).'</textarea></fieldset></td></tr>';

}

?>
<STYLE TYPE=text/css>
body,td {font-family: arial; Font-size: 11px;}
.tulem {font-family: Verdana; Font-size: 13px;}
.browse {font-family: Verdana; Font-size: 11px; border: 1px solid #000000;}
</style>
<body onLoad="javascript:document.forms[0].elements[1].focus();">
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table align=center border=0 cellpadding=0 cellspacing=3 width=650 style="table-layout: fixed;">
<tr>
	<td width=250>
<fieldset align=center style="padding: 10px; width: 100%"><legend style="font-family: verdana; font-size: 11px">&nbsp;<b>p&auml;ring</b>&nbsp;</legend><div style="padding: 10px;">
	veebilehe v&otilde;i e-maili aadress:<br><input type=text name="adr" value="<?php echo $adr; ?>" style="width: 100%"><br>
server:<br>
<select class="textbox" name="server">
<?php
foreach($ns_list as $v){
	$vv=trim($v," \t.<>");
	echo '<option value="'.$vv.'"'.($server==$vv?' selected':'').'>'.$v.'</option>';
}
?>
</select>
<br>
</div>
<div align=right><input type=submit class="browse" value="::: k&auml;ivita p&auml;ring :::"></div>
</fieldset></td>
<td width=400 height="100%">
<?php echo $info; ?>
</td></tr>
<?php echo $data; ?>
</table>
</form>
</body>