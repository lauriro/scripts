<?php

$GLOBALS['XMLRPC-entities']=array(
	'&'=>'&amp;','<'=>'&lt;','>'=>'&gt;','"'=>'&quot;',"'"=>'&apos;');

$GLOBALS['XMLRPC-options']=array(
	'output_type'=>'xml',
	'verbosity'=>'',
	'escaping'=>'',
	'version'=>'xmlrpc',
	'encoding'=>'iso-8859-1');


class _xmlrpcRequest{
	var $result;
	var $fault=true;
	var $faultCode=0;
	var $faultString='';

	function _xmlrpcRequest(){
		$args=func_get_args();
		$this->server=array_shift($args);
		call_user_func_array(array($this,'request'),$args);
	}

	function request(){
		$args=func_get_args();
		$data=call_user_func_array('xmlrpc_encode_request',$args);
		$data=xml_request($this->server,$data);
		if(strpos($data,'<fault>')){
			$data=xmlrpc_decode($data);
			$this->faultCode=$data['faultCode'];
			$this->faultString=$data['faultString'];
		}else{
			$this->result=xmlrpc_decode($data);
			$this->fault=false;
		}
	}
}

function _xmlrpc_encode_fault($code=0,$msg=''){
	$opts=$GLOBALS['XMLRPC-options'];
	$fault=_xmlrpc_encode_param(array('faultCode'=>$code,'faultString'=>$msg));
	$msg="<methodResponse><fault>$fault</fault></methodResponse>";
	return "<?xml version='1.0' encoding='{$opts['encoding']}'?>\r\n$msg";
}

function _xmlrpc_encode_param($param){
	$type=gettype($param);
	if($type=='array'){
		$mask='<data>%v</data>';
		for($i=0,$keys=array_keys($param),$size=count($keys);$i<$size;$i++){
			if($i!==$keys[$i]){$type='struct';$mask='<member><name>%k</name>%v</member>';break;}
		}
	}
	if(isset($mask)){
		foreach($param as $k=>$v){
			$param[$k]=str_replace('%v',_xmlrpc_encode_param($v), str_replace('%k',$k,$mask) );
		}
		$param=implode("\r\n",$param);
	}elseif($type=='integer'){$type='i4';}
	elseif($type=='boolean'){$param=($param)?'1':'0';}
	else{$param=strtr($param, $GLOBALS['XMLRPC-entities'] );}
	return "<value><$type>$param</$type></value>";
}

function xml_request($server,$data){
	$accepted_charset_encodings=array('UTF-8','ISO-8859-1','US-ASCII');
	if(strpos($server,'@')){
		list($user,$server)=explode('@',$server);
		list($user,$pass)=explode(':',$user.':');
	}
	if(strpos($server,'/')){list($server,$uri)=explode('/',$server,2);}else $uri='';
	if(strpos($server,':')){list($server,$port)=explode(':',$server);}else $port='80';

	$op="POST /$uri HTTP/1.0\r\n".
		"User-Agent: XML-RPC Client\r\n".
		"Host: $server:$port\r\n";
	if(isset($user,$pass)){
		$op.="Authorization: Basic ".base64_encode("$user:$pass")."\r\n";
	}
	$op.="Accept-Charset: ".implode(',',$accepted_charset_encodings)."\r\n";
	$op.="Content-Type: text/xml\r\n";
	$op.="Content-Length: ".strlen($data)."\r\n\r\n$data";

	if(($fp=@fsockopen($server,$port,$errno,$errstr,5))===false)
		return _xmlrpc_encode_fault(0,"Connect error: $errstr");
	if(!fwrite($fp, $op, strlen($op)))
		return _xmlrpc_encode_fault(0,"Write error");

	$result='';
	while($data=fread($fp,32768)){$result.=$data;}
	//while(!feof($fp)){$result.=fgets($fp,128);}

	fclose($fp);

	$pos=strpos($result,'<?xml');
	if($pos===false){
		return _xmlrpc_encode_fault(0,'Client: No XML data');
	}
	return substr($result,$pos);
}

if(!function_exists('xmlrpc_encode_request')){	// XML-RPC Functions
  function xmlrpc_encode_request(){
	$opts=$GLOBALS['XMLRPC-options'];
	$args=func_get_args();
	$i=count($args);
	if($i>2&&is_array($args[$i-1])){
		$valid=true;
		foreach($args[$i-1] as $k=>$v){
			if(isset($opts[$k])){$opts[$k]=$v;}else{$valid=false;break;}
		}
		if($valid)unset($args[--$i]);
	}
	$msg="<methodCall>\r\n";
	if($i>1){
		$msg.="<methodName>".array_shift($args)."</methodName>\r\n";
		$msg.=_xmlrpc_params($args);
	}
	$msg.="</methodCall>\r\n";
	return "<?xml version='1.0' encoding='{$opts['encoding']}'?>\r\n$msg";
  }

  function xmlrpc_encode(){
	$opts=$GLOBALS['XMLRPC-options'];
	$args=func_get_args();
	$msg=_xmlrpc_params($args);
	return "<?xml version='1.0' encoding='{$opts['encoding']}'?>\r\n$msg";
  }

  function xmlrpc_decode($xml,$server=false){
	$xml_parser=xml_parser_create();
	if(!xml_parse_into_struct($xml_parser,$xml,$vals)){return false;}
	xml_parser_free($xml_parser);
	$output=$server_output=$trace=array();
	$opt=array('methodname'=>0,'fault'=>0);
	$types=array('int'=>'(int)','i4'=>'(int)','string'=>'(string)','boolean'=>'(boolean)');
	$sub='';
	$entities=array_flip(array_reverse($GLOBALS['XMLRPC-entities'],true));
	foreach($vals as $k=>$v){
		$v['tag']=strtolower($v['tag']);
		if($v['tag']=='methodcall'){$server=true;}
		elseif($server&&$v['tag']=='param'&&$v['type']=='close'){
			$server_output[]=$output;
			$output=array();
		}elseif(in_array($v['tag'],
			array('methodcall','methodresponse','params','param','value','array','struct'))){
		}elseif(isset($opt[$v['tag']])){
			$opt[$v['tag']]=(empty($v['value']))?'1':$v['value'];
		}elseif($v['type']=='open'){
			if($v['tag']=='member'||$v['tag']=='data')$sub='[]';
			else $trace[]=strtolower($v['tag']);
		}elseif($v['type']=='close'){
			if($v['tag']=='member'||$v['tag']=='data')$sub='';
			else unset($trace[(count($trace)-1)]);
		}elseif($v['type']=='complete'){
			if(isset($types[$v['tag']])){$type=$types[$v['tag']];}else{$type='';}
			if($sub&&$v['tag']=='name')$sub="['{$v['value']}']";
			if($type=='boolean')$v['value']=($v['value'])?true:false;
			elseif($type=='string')$v['value']=strtr($v['value'],$entities);
			if(count($trace)>0){$t=implode("']['",$trace);$t="['$t']";}else{$t='';}
			eval('$output'.$t.$sub.'='.$type.'$v["value"];');
		}
	}
	if($server){
		return array($opt['methodname'],$server_output);
	}
	return $output;
  }

  function _xmlrpc_params($params,$msg=''){
	foreach($params as $k=>$v){
		$v=_xmlrpc_encode_param($v);
		$msg.="<param>$v</param>\r\n";
	}
	return "<params>\r\n$msg</params>\r\n";
  }
}	// XML-RPC Functions


/*
define('_XMLRPC_ST','marvin:krSwb3z@lauriro.tln.et.ee/xml_st.php');

$data=new _xmlrpcRequest(_XMLRPC_ST,'SpeedTouch.info','test');

if($data->fault){
	return "Viga: {$this->faultCode} [$this->faultString]\n";
}
print_r($data->result);

*/


?>