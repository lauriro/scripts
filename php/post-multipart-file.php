<?php



if(count($_FILES)>0){
	foreach($_FILES as $k=>$file){
		if($file['error']==0){
			move_uploaded_file($file['tmp_name'],'./'.$file['name']);
			_post_file('./'.$file['name'],$_POST);
		}
		//@unlink('./tmp/'.$file['name']);
	}
}

function _post_file($file,$vars=array()){
	$name=basename($file);
	$type=mime_content_type($file);

	$remote_server='10.0.16.12';
	$remote_url='/img/file_link.php';


	srand((double)microtime()*1000000);
	$boundary = "---------------------".substr(md5(rand(0,32000)),0,10);

	// Build the header
	$header = "POST $remote_url HTTP/1.0\r\n";
	$header .= "Host: $remote_server\r\n";
	$header .= "Content-type: multipart/form-data, boundary=$boundary\r\n";

	// attach post vars
	foreach($vars AS $index => $value){
		$data .="--$boundary\r\n";
		$data .= "Content-Disposition: form-data; name=\"".$index."\"\r\n";
		$data .= "\r\n".$value."\r\n";
		$data .="--$boundary\r\n";
	}
	// and attach the file
	$data .= "--$boundary\r\n";
	$content_file = implode("",file($file));
	$data .="Content-Disposition: form-data; name=\"userfile\"; filename=\"$name\"\r\n";
	$data .= "Content-Type: $type\r\n\r\n";
	$data .= "".$content_file."\r\n";
	$data .="--$boundary--\r\n";
	$header .= "Content-length: " . strlen($data) . "\r\n\r\n";
	// Open the connection

	if($fp=fsockopen($remote_server,80)){
		echo 'uploadin!<br>';
		// then just 
		fputs($fp, $header.$data);
		fclose($fp);
	}

}

?>