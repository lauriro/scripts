<pre><?php

error_reporting(E_ALL);

define('LDAP_HOST','192.168.90.50');
define('LDAP_PORT','389');
define('LDAP_DEFAULT_DOMAIN','et');


class _ldapLink{
	var $_link=false;
	var $fault=false;

	function _ldapLink($host,$port,$base_dn,$default_domain){
		if(($this->_link=@ldap_connect($host,$port))===false){
			$this->fault='LDAP connection error: '.ldap_error($this->_link);
		}
		$this->base_dn=$base_dn;
		$this->default_domain=$default_domain;
	}

	function bind($user,$pass,&$groups=false){
		if(preg_match('/[\\/]+/',$user)){
			list($this->default_domain,$user)=preg_split('/[\\/]+/',$user);
		}

		if(	($res=ldap_bind($this->_link,$this->default_domain.'\\'.$user,$pass))===false &&
			($res=ldap_bind($this->_link,$user,$pass))===false ){return false;}

		ldap_set_option($this->_link,LDAP_OPT_PROTOCOL_VERSION,3);

		$data=$this->search("sAMAccountName=$user",array(
			'logoncount','givenname','sn','telephonenumber','mobile',
			'badpasswordtime','pwdlastset','lastlogontimestamp','maxPwdAge'));

		print_r($data);

		if($groups!==false){
			$res=ldap_read($this->_link,$data[0]['dn'],'(objectclass=*)',array('tokenGroups') );
			list($info)=ldap_get_entries($this->_link, $res);
			$entryID=ldap_first_entry($this->_link,$res);
			$values =ldap_get_values_len($this->_link, $entryID, "tokengroups");
			for($i=0;$i<$values["count"];$i++){
				$info['tokengroups'][$i]=$this->bin_to_str_sid($values[$i]);
			}
			$groups=$info['tokengroups'];
		}
		return $data[0];
	}

	function search($filter,$attributes){


		$res=ldap_search($this->_link,'OU=RBAC,OU=ET,DC=et,DC=ee','CN=*');
		$row=ldap_get_entries($this->_link,$res);


		$list=array();


		$entry=ldap_first_entry($this->_link,$res);
		while($entry){
			$dn=ldap_get_dn($this->_link,$entry);
			$guid=ldap_get_values_len($this->_link,$entry,'objectGUID');
			$sid=ldap_get_values_len($this->_link,$entry,'objectSid');
			$guid_str=$this->bin_to_str_guid($guid[0]);
			$sid_str=$this->bin_to_str_sid($sid[0]);

			list($name)=ldap_get_values_len($this->_link,$entry,'name');

			$list[]=array(
				'dn'=>$dn,
				'sid_str'=>$sid_str,
				'guid_str'=>$guid_str,
				'name'=>$name
			);
			$entry=ldap_next_entry($this->_link,$entry);
		}




		print_r($list);


		$res=ldap_search($this->_link,$this->base_dn,$filter,$attributes);

		$row=ldap_get_entries($this->_link,$res);

		for($i=0;$i<$row["count"];$i++){
			for($val=0;$val<$row[$i]["count"];$val++){
				$data=$row[$i][$val];
				if(in_array($data,array('badpasswordtime','pwdlastset','lastlogontimestamp','maxPwdAge'))){
					$row[$i][$data][0]=convert_time($row[$i][$data][0]);
				}
			}
		}
		return $row;
	}

	function bin_to_str_sid($binsid) {
		$hex_sid = bin2hex($binsid);
		$rev = hexdec(substr($hex_sid, 0, 2));
		$subcount = hexdec(substr($hex_sid, 2, 2));
		$auth = hexdec(substr($hex_sid, 4, 12));
		$result    = "$rev-$auth";

		for ($x=0;$x < $subcount; $x++) {
			$subauth[$x] =
				hexdec($this->little_endian(substr($hex_sid, 16 + ($x * 8), 8)));
			$result .= "-" . $subauth[$x];
		}

		// Cheat by tacking on the S-
		return 'S-' . $result;
	}

	// Converts a little-endian hex-number to one, that 'hexdec' can convert
	function little_endian($hex) {
		$result='';
		for ($x = strlen($hex) - 2; $x >= 0; $x = $x - 2) {
			$result .= substr($hex, $x, 2);
		}
		return $result;
	}


	// This function will convert a binary value guid into a valid string.
	function bin_to_str_guid($object_guid) {
		$hex_guid = bin2hex($object_guid);
		$hex_guid_to_guid_str = '';
		for($k = 1; $k <= 4; ++$k) {
			$hex_guid_to_guid_str .= substr($hex_guid, 8 - 2 * $k, 2);
		}
		$hex_guid_to_guid_str .= '-';
		for($k = 1; $k <= 2; ++$k) {
			$hex_guid_to_guid_str .= substr($hex_guid, 12 - 2 * $k, 2);
		}
		$hex_guid_to_guid_str .= '-';
		for($k = 1; $k <= 2; ++$k) {
			$hex_guid_to_guid_str .= substr($hex_guid, 16 - 2 * $k, 2);
		}
		$hex_guid_to_guid_str .= '-' . substr($hex_guid, 16, 4);
		$hex_guid_to_guid_str .= '-' . substr($hex_guid, 20);

		return strtoupper($hex_guid_to_guid_str);
	}
}



$ldap=new _ldapLink('192.168.90.50','389','OU=ET,DC=et,DC=ee','ET');

$groups=array();
if(!$ldap->fault && $info=$ldap->bind('lauriro','cErt0WU1',$groups)){
	echo 'OK';
}
else echo 'Viga';

print_r($info);
print_r($groups);

function checkBind($user,$password,&$groups=false){

	if(($pos=strpos($user,'\\'))===false){$domain=LDAP_DEFAULT_DOMAIN;}
	else{list($domain,$user)=explode('\\',$user);}

	if(	($res=ldap_bind($ldapconn,$domain.'\\'.$user,$password))===false &&
		($res=ldap_bind($ldapconn,$user,$password))===false ){return false;}

	ldap_set_option($ldapconn,LDAP_OPT_PROTOCOL_VERSION,3);

	$base_dn="OU=ET,DC=et,DC=ee";
	$filter="sAMAccountName=$user";



	$read = ldap_search($ldapconn,'OU=Rak-grupid,OU=ET,DC=et,DC=ee');
   $info = ldap_get_entries($ldapconn, $read);
   print_r($info);



	$read = ldap_search($ldapconn,$base_dn, $filter);


   $info = ldap_get_entries($ldapconn, $read);

   $dn=$info[0]['dn'];

   if($groups!==false){
   
		$result = ldap_read($ldapconn, $dn, "(objectclass=*)",array('tokenGroups') );

		$entry = ldap_get_entries($ldapconn, $result);
		print_r($entry);
   
   }




   //echo iconv('windows-1257','UTF-8',$info[0]['sn'][0]);


	//echo '::<b>'.ldap_get_dn($ldapconn,$info).'</b>::</br>';
   

   print_r($info);
   echo $info["count"]." entrees retournees<BR><BR>"; 
   for($ligne = 0; $ligne<$info["count"]; $ligne++)
   {
       for($colonne = 0; $colonne<$info[$ligne]["count"]; $colonne++)
       {
           $data = $info[$ligne][$colonne];
		   if(in_array($data,array('badpasswordtime','pwdlastset','lastlogontimestamp','maxPwdAge')))
			   $info[$ligne][$data][0]=convert_time($info[$ligne][$data][0]);
           echo $data.":".$info[$ligne][$data][0]."<BR>";
       }
       echo "<BR>";
   }
	ldap_close($ldapconn);

  /*
	$dn = "cn=lauriro,o=Exchange, c=EE";
	$filter=""; 
	$inforequired = array(
			"mail",
			"sn",
			"cn");

	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

	$result = ldap_read($ldapconn, "cn=lauriro,dc=et,dc=ee", "(objectclass=*)");

    $entry = ldap_get_entries($ldapconn, $result);
	print_r($entry);*/
    return true;
}





function convert_time($value){
	$dateLargeInt=$value; // nano secondes depuis 1601 !!!!
	$secsAfterADEpoch = $dateLargeInt / (10000000); // secondes depuis le 1 jan 1601
	$ADToUnixConvertor=((1970-1601) * 365.242190) * 86400; // UNIX start date - AD start date * jours * secondes
	$unixTsLastLogon=intval($secsAfterADEpoch-$ADToUnixConvertor); // Unix time stamp
	$lastlogon=date("d-m-Y H:i:s", $unixTsLastLogon); // Date formatée
	return $lastlogon;
}



?>