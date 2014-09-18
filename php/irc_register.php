<?php




function send_error($msg){
$to='henno@topauto.ee';							// Aadress, kuhu lähevad veateated!
$ip=$proxy='';
if(getenv('HTTP_X_FORWARDED_FOR')){
$b=explode(',',getenv('HTTP_X_FORWARDED_FOR').','.getenv('REMOTE_ADDR'));
$ip=array_shift($b);
$c=array();
foreach($b as $d){
$c[]=$d.' ['.($d!='unknown'?@gethostbyaddr($d):$d).']';
}
$proxy=implode(', ',array_reverse($c));
}else $ip=getenv('REMOTE_ADDR');
$host=($ip!='unknown')?@gethostbyaddr($ip):$ip;

$subject="[Registration error]";
$message="$msg\n\n\n".date('d.M/y H:i:s - - - ').$host.' ['.$ip.'] Proxy: '.$proxy;
$headers='From: henno@topauto.ee';
mail($to,$subject,$message,$headers);
}

class _irc_Class {
	var
	$reg_command='NS REGISTER ',				// Registreerimise käsu alias!
	$server='www.lapsemure.ee',
	$port='6667',
	$nick,$msg,$sock;

	function irc_connect($nick){
		if(!$this->sock){
			$this->nick=$nick;
			if($this->sock=@fsockopen($this->server,$this->port)){
				fwrite($this->sock,"USER phpScript \"nullnet.net\" \"192.168.0.102\" :phpScript\n");
				fwrite($this->sock,"NICK ".$this->nick."\n");
				while(!feof($this->sock)){
					$data=explode(" ",fgets($this->sock,1024));
					if($data[0]=='PING') fwrite($this->sock,'PONG '.$data[1]."\n");
					elseif($data[1]=='433'){
						$this->msg='Selline nimi juba ONLINE!';
						return false;
					}elseif(($data[1]=='376')||($data[1]=='422')) return true;
				}
			}else return false;
		}
		return true;
	}

	function register_nick($nick,$pass,$mail){
		if($this->irc_connect($nick)){
			if($nick!=$this->nick) fwrite($this->sock,'NICK '.$nick."\n");
			sleep(1);
			fwrite($this->sock,$this->reg_command.$pass.' '.$mail."\n");
			$log='';
			while(!feof($this->sock)){
				$data=explode(" ",fgets($this->sock,1024));
				$log+=join('',$data);
				if($data[0]=='PING') fwrite($this->sock,'PONG '.$data[1]."\n");
				elseif($data[1]=='NOTICE'&&$data[5]=='registered') return true;
				elseif($data[1]=='NOTICE'&&$data[3]=='Unknown'){
					$this->msg='Tundmatu viga';
					send_error($this->msg."\nn:".$nick."\np:".$pass."\nm:".$mail);
					return false;
				}elseif($data[1]=='NOTICE'&&$data[6]=='registered'){
					$this->msg='Nimi "'.$nick.'" on enne registreeritud';
					send_error($this->msg."\nn:".$nick."\np:".$pass."\nm:".$mail);
					return false;
				}elseif($data[1]=='433'){
					$this->msg='Selline nimi juba ONLINE!';
					return false;
				}
			}
			return false;
		} else {
			$this->msg='IRC serveriga "'.$this->server.':'.$this->port.'" ei saanud ühendust!';
			send_error($this->msg."\nn:".$nick."\np:".$pass."\nm:".$mail);
			return false;
		}
	}

	function irc_quit(){
		@fputs($this->sock,"QUIT :bye\n");
		sleep(1);
		@fclose($this->sock);
		return true;
	}
}



$irct=new _irc_Class;

$irct->register_nick( str_replace("\'", "''", $username) , $HTTP_POST_VARS['new_password'] , str_replace("\'", "''", $email) );
$irct->irc_quit();



?>