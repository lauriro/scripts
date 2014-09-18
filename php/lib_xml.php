<?php


function xml2array($xml,$trim=array(),$get_attributes=0){
	if(
		($p=xml_parser_create())!==false &&
		xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0) &&
		xml_parser_set_option($p,XML_OPTION_SKIP_WHITE,1) &&
		xml_parse_into_struct($p,$xml,$vals) ){
		xml_parser_free($p);
	}else{
		return false;
	}

	if(!is_array($trim)){
		$trim=array($trim);
	}

	$xml=$parent=array();
	$current=&$xml;

	foreach($vals as $v){
		if($get_attributes){
			$result=array();
			if(isset($v['attributes'])){
				$result=$v['attributes'];
			}
			if(isset($v['value']))$result['value']=$v['value'];
		}elseif(isset($v['value'])){$result=$v['value'];}else{$result='';}
		$v['tag']=strtolower($v['tag']);
		if($v['type']=='open'){
			if(in_array($v['tag'],$trim))continue;
			$parent[$v['level']-1]=&$current;
			if(is_array($current)&&(in_array($v['tag'],array_keys($current)))){
				if(isset($current[$v['tag']][0])){array_push($current[$v['tag']],$result);}
				else{$current[$v['tag']]=array($current[$v['tag']],$result);}
				$current=&$current[$v['tag']][ count($current[$v['tag']])-1 ];
			}else{
				$current[$v['tag']]=$result;
				$current=&$current[$v['tag']];
			}
		}elseif($v['type']=='complete'){
			if(isset($current[$v['tag']])){
				if( (is_array($current[$v['tag']])&&$get_attributes==0) ||
					(isset($current[$v['tag']][0])&&is_array($current[$v['tag']][0])&&$get_attributes==1)){
					array_push($current[$v['tag']],$result);
				}else{$current[$v['tag']]=array($current[$v['tag']],$result);}
			}else{$current[$v['tag']]=$result;}
		}elseif($v['type']=='close'){$current=&$parent[$v['level']-1];}
	}
	return $xml;
} 



?>