<?php

$GLOBALS['XMLRPC-entities']=array(
	'&'=>'&amp;','<'=>'&lt;','>'=>'&gt;','"'=>'&quot;',"'"=>'&apos;');

$GLOBALS['XMLRPC-options']=array(
	'output_type'=>'xml',
	'verbosity'=>'',
	'escaping'=>'',
	'version'=>'xmlrpc',
	'encoding'=>'iso-8859-1');



if(!function_exists('xmlrpc_decode')){	// XML-RPC Functions
  function xmlrpc_decode($xml){
	$xml_parser=xml_parser_create();
	if(!xml_parse_into_struct($xml_parser,$xml,$vals)){
		return false;
	}
	xml_parser_free($xml_parser);

	$output=$trace=array();
	$opt=array('methodname'=>0,'fault'=>0);
	$types=array('int'=>'(int)','i4'=>'(int)','string'=>'(string)','boolean'=>'(boolean)');

	$sub='';

	$server=false;
	$server_output=array();

	$entities=array_flip(array_reverse($GLOBALS['XMLRPC-entities'],true));

	foreach($vals as $k=>$v){
		$v['tag']=strtolower($v['tag']);

		if($v['tag']=='methodcall'){$server=true;}
		elseif($server&&$v['tag']=='param'&&$v['type']=='close'){
			$server_output[]=$output;
			$output=array();
		}
		if(in_array($v['tag'],
			array('methodresponse','params','param','value','array','struct'))){
			continue;
		}
		if(isset($opt[$v['tag']])){
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
}	// XML-RPC Functions



function _xmlrpc_encode_fault($code=0,$msg=''){
	$opts=$GLOBALS['XMLRPC-options'];
	$fault=_xmlrpc_encode_param(array('faultCode'=>$code,'faultString'=>$msg));
	$msg="<methodResponse><fault>$fault</fault></methodResponse>";
	return "<?xml version='1.0' encoding='{$opts['encoding']}'?>\r\n$msg";
}

function _xmlrpc_params($params,$msg=''){
	foreach($params as $k=>$v){
		$v=_xmlrpc_encode_param($v);
		$msg.="<param>$v</param>\r\n";
	}
	return "<params>\r\n$msg</params>\r\n";
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

function xmlrpc_execute($map,$data=null){
	$opts=$GLOBALS['XMLRPC-options'];
	if($data===null){
		$data=isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:'';
	}
	$data=trim(substr($data,strpos($data,'<')));



	if(empty($data))return _xmlrpc_encode_fault(0,'No data');

	list($method,$data)=xmlrpc_decode($data);

	if(!$method)return _xmlrpc_encode_fault(0,'No method');

	if(!isset($map[$method]))return _xmlrpc_encode_fault(0,"Unknown method: $method");

	list($fault,$data)=call_user_func_array($map[$method],$data);

	if($fault!==false)return _xmlrpc_encode_fault($fault,$data);
	$msg="<methodResponse>\r\n";
	$msg.=_xmlrpc_params(array($data));
	$msg.="</methodResponse>\r\n";
	return "<?xml version='1.0' encoding='{$opts['encoding']}'?>\r\n$msg";
}


$map=array(
	'SpeedTouch.info'=>'st_info',
	'SpeedTouch.upgrade'=>'st_info'
);

echo xmlrpc_execute($map);



function st_info(){
	$faultCode=false;
	$faultString='';
	$result='tehtud';


	if($faultCode!==false)$result=$faultString;
	return array($faultCode,$result);
}


?>