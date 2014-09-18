<?php

header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
@set_time_limit(0);
@error_reporting (E_ALL ^ E_NOTICE);


class _pop3_Class {
	var
		$server,
		$port,
		$auth_type,
		$user,
		$pass,
		$sock,
		$box_size,
		$data=array();

	function pop3_login(){
		if(!$this->sock=@fsockopen($this->server,$this->port)) return '1';
		$rs=@fgets($this->sock,512);
		if($this->auth_type=='apop') {
			$rs=split(" ",$rs);
			$secret=md5(trim($rs[count($rs)-1]).$this->pass);
			@fputs($this->sock,'apop '.$this->user." $secret\r\n");
			if(!strstr(@fgets($this->sock,512),'+OK')) return '2';
		} else {
			@fputs($this->sock,'user '.$this->user."\r\n");
			$rs=@fgets($this->sock,512);
			@fputs($this->sock,'pass '.$this->pass."\r\n");
			if(!strstr(@fgets($this->sock,512),'+OK')) return '2';
		}
		return '3';
	}

	function pop3_check($account){
		$this->server=$account['server'];
		$this->port=$account['port'];
		$this->auth_type=$account['auth_type'];
		$this->user=$account['user'];
		$this->pass=$account['pass'];
		switch($this->pop3_login()){
			case '1': return 'Bad connection';
			case '2': return 'Authentication failed';
			case '3':
				switch($this->pop3_list()){
					case '1': return 'Stat failed!';
					case '2': return 'List failed!';
					case '3': return '+OK';
					default : return 'unknown answer';
				}
			default : return 'unknown answer';
		}
		if($this->sock) $this->pop3_quit();
	}

	function pop3_list($start=1) {

		@fputs($this->sock,"stat\r\n");
		$stat=split(" ",@fgets($this->sock,512));
		if($stat[0]!="+OK") return '1';
		$this->box_size=$stat[2];

		for($i=$start;$i<($stat[1]+1);$i++) {
			@fputs($this->sock,"list $i\r\n");
			$list=split(" ",@fgets($this->sock,512));
			if($list[0]!="+OK") return '2';
			$this->data[$i]['size']=$list[2];
			$header="";
			fputs($this->sock,"top $i 0\r\n");
			while(($rs=fgets($this->sock,512))!=".\r\n") $header.=$rs;
			
			$this->data[$i]=$this->pop3_parseheader(split("\r\n",$header));
		}
		return '3';
	}


	function pop3_parseheader($header) {

		$tag=array(
			'message-id',
			'from',
			'subject',
			'date',
			'expiry-date',
			'content-type',
			'importance',
			'sensitivity',
			'x-priority',
			'x-virus-scanned',
		);
		$return=array();
		for($j=0;$j<count($header);$j++) {
			$hd=split(":",$header[$j],2);

			if (preg_match('/^('.join('|',$tag).')$/',strtolower($hd[0]))) {
				if(preg_match_all("/\s/",$hd[0],$matches) || !$hd[1]) {
					if($last_header) $return[$last_header].="\r\n".trim($header[$j]);
				} else {
					$last_header=strtolower($hd[0]);
					$return[$last_header].=(($return[$last_header])?"\r\n":"").trim($hd[1]);
				}
			}
		}
		
		if(is_array($return)) foreach($return as $hd_name=>$hd_content) {
			$start_enc_tag=$stop_enc_tag=0;
			$pre_text=$enc_text=$post_text="";
			while(1) {
				if(strstr($hd_content,"=?") && strstr($hd_content,"?=") && substr_count($hd_content,"?")>3) {
					$start_enc_tag=strpos($hd_content,"=?");
					$pre_text=substr($hd_content,0,$start_enc_tag);
					do {
						$stop_enc_tag=strpos($hd_content,"?=",$stop_enc_tag)+2;
						$enc_text=substr($hd_content,$start_enc_tag,$stop_enc_tag);
					} while (!(substr_count($enc_text,"?")>3));
					$enc_text=explode("?",$enc_text,5);
					switch(strtoupper($enc_text[2])) {
						case "B":
							$dec_text=base64_decode($enc_text[3]);
						break;
						case "Q":
						default:
							$dec_text=quoted_printable_decode($enc_text[3]);
							$dec_text=str_replace("_"," ",$dec_text);
						break;
					}
					$post_text=substr($hd_content,$stop_enc_tag);
					if(substr(ltrim($post_text),0,2)=="=?") $post_text=ltrim($post_text);
					$hd_content=$pre_text.$dec_text.$post_text;
					$return[$hd_name]=$hd_content;
				} else break;
			}
		}
		return $return;
	}

	function pop3_quit(){
		@fputs($this->sock,"quit\r\n");
		@fclose($this->sock);
		sleep(1);
		return;
	}
}



$account=array(
	'server'=>'mail.neti.ee',
	'port'=>'110',
	'auth_type'=>'norm',
	'user'=>'ruutu',
	'pass'=>'mupf6Y',
	'mail'=>'ruutu@neti.ee'
);

session_start();


		$tag=array(
			'message-id',
			'from',
			'subject',
			'date',
			'expiry-date',
			'content-type',
			'importance',
			'sensitivity',
			'x-priority',
			'x-virus-scanned',
		);


function print_list($data){
	if (is_array($data)) foreach($data as $k=>$v){

		preg_match('/([\s\S]{0,})?\s?<([a-z\.-_]{1,}@[a-z\.-_]{5,})>$/i',$v['from'],$matches);
		$mail=htmlentities((empty($matches[2])?'unknown':$matches[2]));
		if(empty($matches[1])){ $name=$mail; $mail=''; }
		else $name=htmlentities(trim(stripslashes($matches[1]),'" '));

		$date=$v['date'];

		$importance=(empty($v['importance'])?'':$v['importance']);
		if(!empty($v['x-priority'])){
			$importance=($v['x-priority']>3?'low':($v['x-priority']<3?'high':''));
		}

		$virus=(empty($v['x-virus-scanned'])?'':'clear');

		$expiry=(empty($v['expiry-date'])?'':'TIME!');

		$attachment=($v['content-type']=='multipart/mixed;'?'FAIL':'');

		


		echo '
<table cellspacing=0 cellpadding=0 border=0 width=500 style="border: 1px solid black">
<tr>
	<td rowspan=2 width=20></td>
	<td><b>'.$name.'</b> <font color="">'.$mail.'</font> '.$date.'</td>
	<td rowspan=2 width=20></td>
</tr>
<tr>
	<td>'.htmlentities($v['subject']).' + '.$attachment.' - '.$expiry.' - ('.$importance.') '.$virus.'</td>
</tr>
</table>';

	
	}

}

switch($_REQUEST['do']){
	case 'check':
		$mail=new _pop3_Class;
		if($mail->pop3_check($account)=='+OK'){
			$_SESSION['mailbox_data']=$mail->data;
			header('Location: '.basename($_SERVER['PHP_SELF']));
			exit;
		}
		break;
	case 'null':
		$_SESSION=array();
		header('Location: '.basename($_SERVER['PHP_SELF']));
		break;

	default:
		print_list($_SESSION['mailbox_data']);

		echo 'OK!'.time();
		echo '<a href="'.basename($_SERVER['PHP_SELF']).'?do=check">check </a>';
		echo '<a href="'.basename($_SERVER['PHP_SELF']).'?do=null"> null</a>';
	exit;

}


exit;
$SECTION_RIGHT="";
$SECTION_LEFT=array(
 "0"=>"Login",
 "1"=>"Check Login",
 "2"=>"Inbox - ".count($_SESSION[MESSAGES][CONTENT])." message".((count($_SESSION[MESSAGES][CONTENT])>1)?"s":"")." - ".sprintf("%.2f",$_SESSION[MESSAGES][SIZE]/1024)." KB",
 "3"=>"Read message",
 "4"=>"Compose new mail",
 "5"=>"Delete messages",
 "6"=>"Send message",
 "999"=>"Credits"
);
$COLORSET=array(
 "GREY"=>array("GROUND"=>"#999999","DARK"=>"#DFDFDF","MEDIUM"=>"#E8E8E8","LIGHT"=>"#F7F7F7","LINE"=>"#7F7F7F","LINKS"=>"#000000"),
 "VIOLET"=>array("GROUND"=>"#CA6597","DARK"=>"#F6C5DB","MEDIUM"=>"#F3DAE8","LIGHT"=>"#FDEEF6","LINE"=>"#FE00BF","LINKS"=>"#FE4100"),
 "GREEN"=>array("GROUND"=>"#64C969","DARK"=>"#C4F5C9","MEDIUM"=>"#D9F2D9","LIGHT"=>"#EDFCED","LINE"=>"#2FFE00","LINKS"=>"#F21DAB"),
 "BLUE"=>array("GROUND"=>"#6699CC","DARK"=>"#C7DDF8","MEDIUM"=>"#DBEAF5","LIGHT"=>"#F0F8FF","LINE"=>"#00BFFF","LINKS"=>"#3E00FE"),
 "BROWN"=>array("GROUND"=>"#C98A64","DARK"=>"#F5D9C4","MEDIUM"=>"#F2E1D9","LIGHT"=>"#FCF2ED","LINE"=>"#FE2300","LINKS"=>"#2727F9")
);
$CHARSETS = array("afrikaans-iso-8859-1"=>"iso-8859-1", "afrikaans-utf-8"=>"utf-8", "albanian-iso-8859-1"=>"iso-8859-1","english-utf-8"=>"utf-8",
 "estonian-iso-8859-1"=>"iso-8859-1", "estonian-utf-8"=>"utf-8", "finnish-iso-8859-1"=>"iso-8859-1", "finnish-utf-8"=>"utf-8", "french-iso-8859-1"=>"iso-8859-1", "french-utf-8"=>"utf-8", "galician-iso-8859-1"=>"iso-8859-1",
"turkish-utf-8"=>"utf-8", "ukrainian-utf-8"=>"utf-8", "ukrainian-windows-1251"=>"windows-1251");

if(!$_SESSION[COLORS]) $_SESSION[COLORS]=$DEFAULT_COLORSET;
if(!$_SESSION[FONTSIZE]) $_SESSION[FONTSIZE]=$DEFAULT_FONTSIZE;
if(!$_SESSION[CHARSET]) $_SESSION[CHARSET]=$DEFAULT_CHARSET;

if($_GET[colorset]) $_SESSION[COLORS]=$_GET[colorset];
$COLORS=$_SESSION[COLORS];
if($_GET[fontsize]) $_SESSION[FONTSIZE]=$_GET[fontsize];
if($_POST[charset]) $_SESSION[CHARSET]=$_POST[charset];

$WIZ=$_SERVER['PHP_SELF'];
if($_REQUEST[op]==1 && $_POST[username]) setcookie("username", $_POST[username]);
if($_REQUEST[op]==1 && $_POST[email]) setcookie("email", $_POST[email]);
if((!$_SESSION[AUTH] && $_REQUEST[op]>1 && $_REQUEST[op]<100) || !$_REQUEST[op]) $_REQUEST[op]=0;
if(!$_SESSION[MPP]) $_SESSION[MPP]=$DEFAULT_MAIL_PER_PAGE;
$BOUNDARY="----------NamekoWebmailBoundary";
$LEGAL_CHARS="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789._@-";
$SOCK="";
?>
<html>
<head>
 <title>pop3</title>
 <meta http-equiv="Content-Type" content="text/html; charset=<?=$CHARSETS[$_SESSION[CHARSET]]?>" />
 <style type="text/css"><!--
  BODY,TABLE,TR,TD,INPUT,TEXTAREA,OPTION,SELECT { font-family:tahoma,sans-serif;font-size:<?=$_SESSION[FONTSIZE]?>pt;color:#333333;text-decoration:none; }
  A:LINK,A:VISITED,.title { font-family:tahoma,sans-serif;font-size:<?=$_SESSION[FONTSIZE]?>pt;color:<?=$COLORSET[$COLORS][LINKS]?>;text-decoration:none; }
  A:HOVER { text-decoration:underline; }
 --></style>
</head>

<body>
   <table height="100%" cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="<?=$COLORSET[$COLORS][LIGHT]?>">
    <tr valign="middle" height="20"><td style="border-bottom:1pt solid <?=$COLORSET[$COLORS][LINE]?>"><table cellspacing="0" cellpadding="0" width="100%" height="100%" border="0"><tr valign="middle">
     <td width="35%" style="font-size:<?=($_SESSION[FONTSIZE]+2)?>pt;"><b>&nbsp; .: <?=$SECTION_LEFT[$_REQUEST[op]]?> :.</b></td>
     <td width="65%" align="right"><b><?=$SECTION_RIGHT?></b> &nbsp;</td>
    </tr></table></td></tr>
    <tr>
<?

//print_r($_SESSION);
switch($_REQUEST['op']) {

//1:CHECK LOGIN
case "1":
 $_SESSION['start']="";
 $auth_fields=array("username","password","email","server","auth_type");
 foreach($auth_fields as $af) {
  if($_POST[$af]) $_SESSION[$af]=$_POST[$af];
  if(!$_SESSION[$af]) $fields_not_filled=1;
 }
 echo("<td valign='top'><p>&nbsp;</p><p>&nbsp;</p>");
 if($fields_not_filled) {
  ShowMessage("ERROR!","<p>You must fill all the fields of the login form!</p>\n<p><a href='$WIZ'><b>Try again</b></a></p>");
 } else {
  if(is_array($USERS_LIST) && count($USERS_LIST)>0 && (($USERS_POLICY=="allow" && in_array($_SESSION[username],$USERS_LIST)) || ($USERS_POLICY!="allow" && !in_array($_SESSION[username],$USERS_LIST)))) {
   ShowMessage("ERROR!","<p>Access denied for user <b>$_SESSION[username]</b> by access control list!</p>\n<p><a href='$WIZ'><b>Try again (with another user)</b></a></p>");
  } else {
   switch(POP3OpenConnectionAndLogin()) {
    case "1": //BAD CONNECTION
     ShowMessage("ERROR!","<p>Connect to address ".$_SESSION[server].":<br>connection refused</p>\n<p><a href='$WIZ'><b>Try again</b></a></p>");
     break;
    case "2": //LOGIN FAILED
     ShowMessage("ERROR!","<p>Authentication failed (bad password?)<br>Connection closed by foreign host.</p>\n<p><a href='$WIZ'><b>Try again</b></a></p>");
     break;
    case "3": //LOGIN OK, RETRIEVE MESSAGES
     $retr_code=POP3RetrieveHeaders(count($_SESSION[MESSAGES][CONTENT])+1);
     switch($retr_code) {
      case "31":
       $_SESSION[AUTH]=1;
       ShowMessage("+OK","<p>Messages fully retrieved!<br>Waiting while creating the GUI...</p>\n<p>If you are not automatically redirect to the next page into 5 seconds,<br><a href='$WIZ?op=2'><b>click here</b></a>!</p>\n<script language='JavaScript'>window.location='$WIZ?op=2'</script>");
       break;
      default: //UNKNOWN ANSWER
       ShowMessage("ERROR!","<p>Retrieving messages from server ".$_SESSION[server].":<br>unknown answer from the server ($retr_code)</p>\n<p><a href='$WIZ'><b>Try again</b></a></p>");
       break;
     }
     break;
    default: //UNKNOWN ANSWER
     ShowMessage("ERROR!","<p>Connect to address ".$_SESSION[server].":<br>unknown answer from the server</p>\n<p><a href='$WIZ'><b>Try again</b></a></p>");
     break;
   }
   if($SOCK) POP3CloseConnection();
  }
 }
 echo("</td>");
 break;

//2:SHOW MESSAGE LIST
case "2":
 $msgnum=count($_SESSION[MESSAGES][CONTENT]);
 if($_GET[toggle_delete]) $_SESSION[MESSAGES][CONTENT][$_GET[toggle_delete]][DELETE]*=(-1);
 if(isset($_GET[change_limit])) $_SESSION['start']=$_GET[change_limit];
 if($_GET[change_mpp]) {
  $_SESSION[MPP]=$_GET[change_mpp];
  $_SESSION['start']=0;
 }
 if($_POST[auto_mark_deletion]) {
  for($i=0;$i<$msgnum;$i++) {
   $header=MessageParseHeader(split("\r\n",$_SESSION[MESSAGES][CONTENT][$i][HEADER]));
   if(stristr($header[subject],$_POST[auto_mark_deletion]) || stristr($header[from],$_POST[auto_mark_deletion])) $_SESSION[MESSAGES][CONTENT][$i][DELETE]=$message[DELETE]=1;
  }
 }
 if($_GET[mark_all_messages]) {
  for($i=$msgnum;$i>0;$i--) $_SESSION[MESSAGES][CONTENT][$i][DELETE]=$_GET[mark_all_messages];
 }
 $marked_messages_num=0;
 for($i=0;$i<($msgnum+1);$i++) if($_SESSION[MESSAGES][CONTENT][$i][DELETE]>0) $marked_messages_num++;
 echo("<td align='center' valign='top' style='padding:10pt;'><br>
  <table width='100%' cellspacing='0' cellpadding='0' border='0' style='border-collapse:collapse;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'>
   <tr bgcolor='".$COLORSET[$COLORS][DARK]."'>
    <td width='1%' class='title' style='padding:5pt;'>&nbsp;</td>
    <td width='23%' class='title' style='padding:5pt;'><b>From</b></td>
    <td width='50%' class='title' style='padding:5pt;'><b>Subject</b></td>
    <td width='20%' class='title' style='padding:5pt;'><b>Date</b></td>
    <td width='6%' class='title' style='padding:5pt;' align='right'><b>Size</b></td>
   </tr>");
 $i=($_SESSION['start'])?$_SESSION['start']:0;
 $i_max=(($i+$_SESSION[MPP])>$msgnum)?$msgnum:$i+$_SESSION[MPP];
 if($msgnum>0) {
  for($i;$i<$i_max;$i++) {
   $ir=$msgnum-$i;
   $header=MessageParseHeader(split("\r\n",$_SESSION[MESSAGES][CONTENT][$ir][HEADER]));
   while(strstr($header[date],"  ")) $header[date]=str_replace("  "," ",$header[date]);
   $messageDate=split(" ",$header[date]);
   echo("<tr bgcolor='".$COLORSET[$COLORS][MEDIUM]."' onMouseOver='javascript:this.style.backgroundColor=\"".$COLORSET[$COLORS][LINE]."\"' onMouseOut='javascript:this.style.backgroundColor=\"".$COLORSET[$COLORS][MEDIUM]."\"'>
	 <td width='1%' style='padding:0pt 5pt 0pt 5pt;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'><input type='checkbox' name='del[$ir]' value='1' ".(($_SESSION[MESSAGES][CONTENT][$ir][DELETE]>0)?"checked":"")." onClick='javascript:window.location=\"$WIZ?op=2&toggle_delete=$ir\"'></td>
    <td width='23%' style='padding:5pt;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'>".htmlentities($header[from])."</td>
    <td width='50%' style='padding:5pt;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'><a href='$WIZ?op=3&id=$ir' style='".(($_SESSION[MESSAGES][CONTENT][$ir][READ]==1)?"":"font-weight:bold;")."'>".((stristr($header["content-type"],"multipart/mixed"))?"[ATT]":"")." ".(($header[subject])?htmlentities($header[subject]):"[no subject]")."</a></td>
    <td width='20%' style='padding:5pt;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'>".sprintf("%02d",$messageDate[1])." $messageDate[2] $messageDate[3]</td>
    <td width='6%' style='padding:5pt;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;' align='right'>".sprintf("%.2f",$_SESSION[MESSAGES][CONTENT][$ir][SIZE]/1024)." KB</td>
    
   </tr>");
  }
 } else {
  echo("<tr bgcolor='".$COLORSET[$COLORS][MEDIUM]."'>
   <th width='100%' colspan='5' style='padding:5pt;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'><br><br>Inbox empty<br><br>&nbsp;</th>
  </tr>");
 }
 echo("<tr bgcolor='".$COLORSET[$COLORS][DARK]."'><td class='title' style='padding:5pt;' colspan='5'>
  <b>Tools</b><br>
   Show <select onChange='javascript:window.location=\"$WIZ?op=2&change_mpp=\"+this.value' style='height:15pt;'>");
   for($k=5;$k<51;$k+=5) echo("<option value='$k' ".(($_SESSION[MPP]==$k)?"selected":"").">$k</option>");
   echo("</select> messages per page, <select onChange='javascript:window.location=\"$WIZ?op=2&change_limit=\"+this.value' style='height:15pt;'>");
   if(!$msgnum) echo("<option>no messages in inbox</option>");
   else { for($k=0;$k<$msgnum;$k+=$_SESSION[MPP]) echo("<option value='$k' ".(($_SESSION['start']==$k)?"selected":"").">from ".($k+1)." to ".((($k+$_SESSION[MPP])>$msgnum)?$msgnum:($k+$_SESSION[MPP]))."</option>"); }
   echo("</select> <input type='button' value='&lt; Prev' style='height:15pt;width:50pt;' onClick='javascript:window.location=\"$WIZ?op=2&change_limit=".($_SESSION['start']-$_SESSION[MPP])."\"' ".(($_SESSION['start']<$_SESSION[MPP])?"disabled":"")."> <input type='button' value='Next &gt;' style='height:15pt;width:50pt;' onClick='javascript:window.location=\"$WIZ?op=2&change_limit=".($_SESSION['start']+$_SESSION[MPP])."\"' ".((($_SESSION['start']+$_SESSION[MPP])>$msgnum)?"disabled":"")."><br>
   <form method='post' action='$WIZ?op=2'>Auto mark for deletion messages that contains <input type='text' name='auto_mark_deletion' style='width='40pt;height:15pt;'> in From or Subject field <input type='submit' value='Mark' style='height:15pt;'></form>
   <input type='button' value='Mark all messages' style='height:15pt;' onClick='javascript:window.location=\"$WIZ?op=2&mark_all_messages=1\"'>
   <input type='button' value='Unmark all messages' style='height:15pt;' onClick='javascript:window.location=\"$WIZ?op=2&mark_all_messages=-1\"'>
   <input type='button' value='Delete marked messages ($marked_messages_num)' style='height:15pt;' onClick='javascript:if(confirm(\"Do you really want to delete all marked messages?\")) window.location=\"$WIZ?op=5\"' ".(($marked_messages_num<1)?"disabled":"").">
  </td></tr>
 </table>\n</td>");
 break;

//3:READ MAIL
case "3":
 if($_GET[toggle_delete]) $_SESSION[MESSAGES][CONTENT][$_GET[toggle_delete]][DELETE]*=(-1);
 $_SESSION[MESSAGES][CONTENT][$_GET[id]][READ]=1;
 $getid=$_GET[id];
 $content_id_to_filename=array();
 $header=MessageParseHeader(split("\r\n",$_SESSION[MESSAGES][CONTENT][$getid][HEADER]));
 echo("<td align='center' valign='top' style='padding:10pt;'><br>
  <table width='100%' cellspacing='0' cellpadding='0' border='0' style='border-collapse:collapse;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'>
   <tr bgcolor='".$COLORSET[$COLORS][DARK]."' valign='top'>
    <td width='75%' style='padding:5pt;'>
     <div style='padding-bottom:5pt;'><b><big>".htmlentities($header[subject])."</big></b></div>
     <table border='0' width='100%' cellspacong='2' cellpadding='1'>
      <tr><td><b>From:</b> ".htmlentities($header[from])."</td></tr>
      <tr><td><b>To:</b> ".htmlentities($header[to])."</td></tr>
      <tr><td><b>Date:</b> ".htmlentities($header[date])."</td></tr>");
      if($header["disposition-notification-to"]) echo("<tr><td><font color='#DD0000'><b>Sender ask for read notification to address ".htmlentities($header["disposition-notification-to"])."!</b></font></td></tr>");
      echo("<tr><td colspan='2'><input type='button' value='Show/hide other headers' style='width:200pt;height:15pt;' onClick='javascript:document.all.allheaders.style.display=(document.all.allheaders.style.display==\"none\")?\"\":\"none\"'></td></tr>
     </table>
     <table border='0' width='100%' cellspacong='2' cellpadding='1' style='display:none;' id='allheaders'>");
 $_SESSION[refw][date]=htmlentities($header[date]);
 $_SESSION[refw][from]=GetAddressFromFromHeader($header[from]);
 $_SESSION[refw][subject]=htmlentities($header[subject]);
 foreach($header as $hd_name=>$hd_content) if(($hd_name!="from") && ($hd_name!="subject") && ($hd_name!="to") && ($hd_name!="date") && (trim($hd_content)!="")) echo("<tr><td><table border='0' cellpadding='0' cellspacing='0'><tr valign='top'><td style='padding-right:5pt;'><b>".ucfirst($hd_name).":<b></td><td>".nl2br(htmlentities($hd_content))."</td></tr></table></td></tr>\n");
 if($_SESSION[MESSAGES][CONTENT][$_GET[id]][DELETE]>0) {
  $del_message="<font color='#FF0000'><b>This message is marked for deletion</b></font><br>";
  $del_button="Unmark";
 } else {
  $del_button="Mark";
 }
 echo("</table>
    </td>
    <td width='25%' style='padding:5pt;' align='right'>
     $del_message
     <input type='button' value='Reply' style='width:130pt;' onClick='window.location=\"$WIZ?op=4&act=re\"'><br>
     <input type='button' value='Forward' style='width:130pt;' onClick='window.location=\"$WIZ?op=4&act=fw\"'><br>
     <input type='button' value='$del_button for deletion' style='width:130pt;' onClick='javascript:window.location=\"$WIZ?op=3&id=$_GET[id]&toggle_delete=$_GET[id]\"'><br>
     <input type='button' value='Back to Inbox' style='width:130pt;' onClick='javascript:window.location=\"$WIZ?op=2\"'><br>
    </td>
   </tr>
  </table>
  <div align='left'>");
 $message=MessageRetrieveContent($header,POP3RetrieveMessage($_GET[id]));
 if(count($message["attachments"])) {
  echo("<br><table width='100%' cellspacing='0' cellpadding='0' border='0' style='border-collapse:collapse;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'><tr bgcolor='".$COLORSET[$COLORS][DARK]."' valign='top'><td><b>Attachments</b>:<br>\n");
  foreach($message["attachments"] as $atc) {
   $ctparts=split(";",$atc["header"]["content-type"]);
   foreach($ctparts as $ctpart) {
    if(strstr($ctpart,"name=")) {
     $filename=split("=",$ctpart);
     $filename=str_replace("\"","",$filename[1]);
    }
   }
   for($d=0;$d<strlen($filename);$d++) {
    if(!strstr($GLOBALS[LEGAL_CHARS],$filename[$d])) $filename[$d]="_";
   }
   echo("&nbsp; &middot; <a href='tmp_nameko/$filename' target='_blank'>$filename</a><br>");
   if($attach_handle=@fopen("tmp_nameko/$filename","wb")) @fwrite($attach_handle,$atc[content]);
   @fclose($attach_handle);
   if(is_file("tmp_nameko/$filename")) @chmod("tmp_nameko/$filename",0600);
   if($atc["header"]["content-id"]) {
    $content_id=substr($atc["header"]["content-id"],1,-1);
    $content_id_to_filename=array_merge($content_id_to_filename,array("cid:$content_id"=>"tmp_nameko/$filename"));
   }
  }
  echo("</td></tr></table>\n");
 }
 echo("<p>");
 $button_show_plain="<input type='button' value='Show text/plain message' style='width:130pt;' onClick='javascript:window.location=\"$WIZ?op=3&id=$_GET[id]\"'><br><br>\n";
 $button_show_html="<input type='button' value='Show HTML message' style='width:130pt;' onClick='javascript:window.location=\"$WIZ?op=3&id=$_GET[id]&show_html=1\"'><br><br>\n";
 if($message["text-plain"] && $message["text-html"]) {
  if($_GET[show_html]) echo($button_show_plain . strtr($message["text-html"],$content_id_to_filename));
  else echo($button_show_html . $message["text-plain"]);
 } elseif($message["text-plain"] && !$message["text-html"]) {
  echo($message["text-plain"]);
 } elseif(!$message["text-plain"] && $message["text-html"]) {
  echo(strtr($message["text-html"],$content_id_to_filename));
 } else {
  echo("<i>This e-mail doesn't contain any text.</i>");
 }
 echo("</p></div></td>");
 $_SESSION[refw][text]=split("\n",wordwrap(strip_tags($message["text-plain"]), 60, "\n", 1));
 for($x=0;$x<count($_SESSION[refw][text]);$x++) $_SESSION[refw][text][$x]="> ".$_SESSION[refw][text][$x];
 $_SESSION[refw][text]=implode("\n",$_SESSION[refw][text]);
 break;

//4:COMPOSE MAIL
case "4":
 if($_GET[act]) {
  $refwtext="Original message sent on the ".$_SESSION[refw][date]." by ".$_SESSION[refw][from]."\n".$_SESSION[refw][text];
  if($_GET[act]=="re") {
   $refwto=$_SESSION[refw][from];
   $refwsubject = (preg_match("/re:/i",$_SESSION[refw][subject]) ? "" : "Re: ") . $_SESSION[refw][subject];
  } elseif ($_GET[act]=="fw") {
   $refwsubject = (preg_match("/fw:/i",$_SESSION[refw][subject]) ? "" : "Fw: ") . $_SESSION[refw][subject];
  }
 }
 echo("<td valign='top' style='padding:10pt;'><form method='post' action='$WIZ?op=6' enctype='multipart/form-data'>
  <table width='100%' cellspacing='0' cellpadding='0' border='0' style='border-collapse:collapse;border:solid ".$COLORSET[$COLORS][LINE].";border-width:1pt 0pt 1pt 0pt;'><tr valign='top' bgcolor='".$COLORSET[$COLORS][DARK]."'>
   <td width='75%'><table width='100%' cellspacing='0' cellpadding='4' border='0'>
    <tr>
     <th align='right'>From: </th>
     <td><input type='text' name='from' style='width:300pt;' value='$_SESSION[email]'></td>
    </tr>
    <tr>
     <th align='right'>To: </th>
     <td><input type='text' name='to' style='width:300pt;' value='$refwto'></td>
    </tr>
    <tr>
     <th align='right'>Cc: </th>
     <td><input type='text' name='cc' style='width:300pt;'></td>
    </tr>
    <tr>
     <th align='right'>Bcc: </th>
     <td><input type='text' name='bcc' style='width:300pt;'></td>
    </tr>
    <tr>
     <th align='right'>Subject: </th>
     <td><input type='text' name='subject' style='width:300pt;' value='$refwsubject'></td>
    </tr>
   </table></td>
   <td width='25%'><table width='100%' cellspacing='0' cellpadding='4' border='0'>
    <tr><td><b>Options:</b></td></tr>
    <tr><td><input type='checkbox' name='notification' value='1'> Ask for notification</td></tr>
    <tr><td><b>Attachments:</b></td></tr>
    <tr><td>
     <b>#1</b> <input type='file' name='atc[0]'><br>
     <b>#2</b> <input type='file' name='atc[1]'><br>
     <b>#3</b> <input type='file' name='atc[2]'>
    </td></tr>
   </table></td>
  </tr></table>
  <p><b>Message</b><br><textarea name='message' style='width:100%;height:300pt;'>$refwtext</textarea></p>
  <p align='center'><input type='submit' value='Send e-mail' style='width:130pt;'> <input type='reset' value='Reset form' style='width:130pt;'> <input type='button' value='Back to Inbox' style='width:130pt;' onClick='javascript:window.location=\"$WIZ?op=2\"'><br>
 </form></td>");
 break;

//5:DELETE MESSAGES
case "5":
 echo("<td valign='top'><br>");
 POP3DeleteMessages();
 echo("<p><input type='button' value='Back to Inbox' style='height:15pt;' onClick='javascript:window.location=\"$WIZ?op=1\"'>
  <script language='javascript'>window.location=\"$WIZ?op=1\"</script>
 </td>");
 break;

//6:SEND EMAIL
case "6":
 echo("<td valign='top'><p>&nbsp;</p><p>&nbsp;</p>");
 $email_fields=array("from","to","cc","bcc");
 foreach($email_fields as $ef) {
  $eas=split('[;,]',$_POST[$ef]);
  foreach($eas as $ea) {
   if($ea=trim($ea)) {
    if(!ValidateEmailAddress($ea)) {
     $invalid_email_address.="<b>$ea</b> in <b>".ucfirst($ef)."</b> field<br>";
     echo("<br>");
    }
    $email_address[$ef].="$ea, ";
   }
  }
  $email_address[$ef]=substr($email_address[$ef],0,-2);
 }
 if($invalid_email_address || !$email_address[to]) {
  ShowMessage("ERROR!","<p>Error during delivering mail.</p>\n<p>E-mail address(es)<br>$invalid_email_address is(are) not valid e-mail address(es).</p>\n<p><a href='javascript:history.back()'><b>Back</b></a></p>");
 } else {
  $content_type="text/plain";
  $message=explode("\n",$_POST[message]);
  foreach($message as $line) $text.=stripslashes(rtrim($line))."\n";
  for($i=0;$i<3;$i++) {
   if($_FILES['atc']['name'][$i]) {
    $content_type="multipart/mixed;\n  charset=\"iso-8859-1\";\n  boundary=\"$BOUNDARY\"";
    $headertext="This is a multi-part message in MIME format.\n\n--$BOUNDARY\nContent-Type: text/plain;\n  charset=\"iso-8859-1\"\nContent-Transfer-Encoding: quoted-printable\n\n";
    $footertext="--$BOUNDARY--\n";
    $fattach=@fopen($_FILES['atc']['tmp_name'][$i],"rb");
    $attach=chunk_split(base64_encode(@fread($fattach,@filesize($_FILES['atc']['tmp_name'][$i]))));
    @fclose($fattach);
    $text.="\n--$BOUNDARY\nContent-Type: ".$_FILES['atc']['type'][$i].";\n  charset=\"iso-8859-1\";\n  name=\"".$_FILES['atc']['name'][$i]."\"\nContent-Transfer-Encoding: base64\nContent-Disposition: attachment; filename=\"".$_FILES['atc']['name'][$i]."\"\n\n$attach\n";
   }
  }
  $text=$headertext.$text.$footertext;
  $headermail="From: $email_address[from]\nReply-to: $email_address[from]\n".(($email_address[cc])?"Cc: $email_address[cc]\n":"").(($email_address[bcc])?"Bcc: $email_address[bcc]\n":"")."MIME-Version: 1.0\nContent-Type: $content_type\nX-Sender-IP-Address: $_SERVER[REMOTE_ADDR]\nX-Mailer: $VER[NAME] $VER[MAJOR].$VER[MINOR]\n";
  if($_POST[notification]) $headermail.="Disposition-Notification-To: $email_address[from]\n";

  //Send e-mail
  if($EXTERNAL_SMTP_SERVER) {
    //send mail with external SMTP server
    $SMTPMail = new NamekoSendMailWithExternalSMTPServer($EXTERNAL_SMTP_SERVER);
    $SMTPMail->setMailParameters($email_address[from], $email_address[to], $headermail, $_POST[subject], $text);
    $sentMailStatus = $SMTPMail->sendMailThroughExternalSMTPServer();
  } else {
    //send mail with PHP build-in mail() command
    $sentMailStatus = @mail("$email_address[to]","$_POST[subject]","$text","$headermail","-f$email_address[from]");
  }

  //Output sending mail result
  if($sentMailStatus) {
   ShowMessage("SUCCESS!","<p>Mail correctly delivered.</p>\n<p><a href='$WIZ?op=2'><b>Back to Inbox</b></a></p>");
  } else {
   ShowMessage("ERROR!","<p>Error during delivering mail.</p>\n<p>Mail command failed.</p>\n<p><a href='javascript:history.back()'><b>Back</b></a></p>");
  }

 }
 echo("</td>");
 break;

//999:CREDITS
case "999":
 echo("<td valign='top'>
  <p><br><b>Maintainer</b>:<br>
   &nbsp; <span class='title'>Marco Avidano</span>
  </p>
 </td>");
 break;

//DEFAULT:LOGIN
default:
 $temp_fontsize=$_SESSION[FONTSIZE];
 $temp_colorset=$_SESSION[COLORS];
 $temp_charset=$_SESSION[CHARSET];
 session_unset();
 $_SESSION[COLORS]=$temp_colorset;
 $_SESSION[FONTSIZE]=$temp_fontsize;
 $_SESSION[CHARSET]=$temp_charset;
 echo("<td align='center' valign='middle'>");
 if(!FlushTmpDir()) {
  echo("<p><font color='#F00000'><b>An error occour trying to flush the temporary directory.<br>The attachment download should not work.<br>You can fix this error creating a directory<br>called &quot;tmp_nameko&quot; where you put the $VER[NAME] script.<br>For any request contact <a href='$VER[URL]' target='_blank'>$VER[WEB]</a>.</b></font></p>\n");
 }
 echo("<form action='$WIZ?op=1' method='post'>
   <table cellspacing='0' cellpadding='0' border='0' style='border-collapse:collapse;border:1pt solid ".$COLORSET[$COLORS][LINE].";'>
    <tr><th style='padding:5pt;' bgcolor='".$COLORSET[$COLORS][DARK]."'>Welcome to $VER[NAME] $VER[MAJOR].$VER[MINOR]</th></tr>
    <tr><td style='padding:5pt 5pt 2pt 5pt;' align='right'><b>Username</b> &nbsp; <input type='text' name='username' value='$_COOKIE[username]' style='width:200pt;'></td></tr>
    <tr><td style='padding:2pt 5pt 2pt 5pt;' align='right'><b>Password / Secret</b> &nbsp; <input type='password' name='password' style='width:200pt;'></td></tr>
    <tr><td style='padding:2pt 5pt 2pt 5pt;' align='right'><b>E-mail address</b>  &nbsp; <input type='text' name='email' value='$_COOKIE[email]' style='width:200pt;'></td></tr>
    <tr><td style='padding:2pt 5pt 2pt 5pt;' align='right'><b>Auth method</b>  &nbsp; <select name='auth_type' style='width:200pt;'><option value='standard'>Standard user/pass</option><option value='apop'>APOP method</option></select></td></tr>
    <tr><td style='padding:2pt 5pt 2pt 5pt;' align='right'><b>Charset</b>  &nbsp; <select name='charset' style='width:200pt;'>");
      foreach($CHARSETS as $name=>$code) echo("<option value='$name' ".(($name==$_SESSION[CHARSET])?"selected":"").">$name</option>\n");
    echo("</select></td></tr>
    <tr><td style='padding:2pt 5pt 2pt 5pt;' align='right'><b>Server</b> &nbsp; ");
 if($SERVERS) {
  echo("<select name='server' style='width:200pt;'>");
  foreach($SERVERS as $server_name=>$server_addr) echo("<option value='$server_addr'>$server_name</option>\n");
  echo("</select>");
 } else {
  echo("<input type='text' name='server' style='width:200pt;'>");
 }
 echo("</td></tr>
    <tr><th style='padding:5pt;'><input type='submit' value='L O G I N' style='width:200pt;'></td></tr>
   </table>
  </form>
 </td>");
 break;
}
?>
    </tr>
    <tr valign="middle" height="20"><td style="border-top:1pt solid <?=$COLORSET[$COLORS][LINE]?>"><table cellspacing="0" cellpadding="0" width="100%" height="100%" border="0"><tr valign="middle">
     <td width="90" style="font-size:<?=($_SESSION[FONTSIZE]+2)?>pt;"><b>&nbsp; .: Utils :.</b></td>
     <td align="right"><b>
      Font size: <select name="fontsize" onChange="javascript:window.location='<?=$WIZ?>?op=<?=$_REQUEST[op]?>&id=<?=$_REQUEST[id]?>&fontsize='+this.value"><? for($i=7;$i<16;$i++) echo("<option value='$i' ".(($i==$_SESSION[FONTSIZE])?"selected":"").">$i pt</option>\n"); ?></select> &nbsp; | &nbsp;
      Colorset: <select name="colorset" onChange="javascript:window.location='<?=$WIZ?>?op=<?=$_REQUEST[op]?>&id=<?=$_REQUEST[id]?>&colorset='+this.value"><? foreach($COLORSET as $cs_n=>$cs_d) echo("<option value='$cs_n' ".(($cs_n==$COLORS)?"selected":"").">$cs_n</option>\n"); ?></select> &nbsp; | &nbsp;
      Functions: <a href='<?=$WIZ?>'>Login/Logout</a> |
      <? if($_SESSION[MESSAGES]) echo("
       <a href='$WIZ?op=1'>Check for new mail</a> |
       <a href='$WIZ?op=2'>Back to Inbox</a> |
       <a href='$WIZ?op=4'>Compose new e-mail</a> |
      ");
      ?>
      <a href='<?=$WIZ?>?op=999'>Credits</a> |
     </b></td>
    </tr></table></td></tr>
   </table>
</body>
</html>

<?
function ShowMessage($title,$message) {
 $COLORSET=$GLOBALS[COLORSET];
 $COLORS=$GLOBALS[COLORS];
 echo("<table width='250' align='center' cellspacing='0' cellpadding='0' border='0' style='border-collapse:collapse;border:1pt solid ".$COLORSET[$COLORS][LINE].";'>
  <tr><th style='padding:5pt;' bgcolor='".$COLORSET[$COLORS][DARK]."'>$title</th></tr>
  <tr><td style='padding:10pt;' align='center'>$message</td></tr>
 </table>");
 return;
}

function POP3OpenConnectionAndLogin() {
 if(!$GLOBALS[SOCK]=@fsockopen($_SESSION[server],110)) return "1";
 $rs=@fgets($GLOBALS[SOCK],512);
 if($_SESSION[auth_type]=="apop") {
  $rs=split(" ",$rs);
  $secret=md5(trim($rs[count($rs)-1]).$_SESSION[password]);
  @fputs($GLOBALS[SOCK],"apop ".$_SESSION[username]." $secret\r\n");
  if(!strstr(@fgets($GLOBALS[SOCK],512),"+OK")) return "2";
 } else {
  @fputs($GLOBALS[SOCK],"user ".$_SESSION[username]."\r\n");
  $rs=@fgets($GLOBALS[SOCK],512);
  @fputs($GLOBALS[SOCK],"pass ".$_SESSION[password]."\r\n");
  if(!strstr(@fgets($GLOBALS[SOCK],512),"+OK")) return "2";
 }
 return "3";
}

function POP3RetrieveHeaders($start) {
 if($start==0) $start=1;
 @fputs($GLOBALS[SOCK],"stat\r\n");
 $stat=split(" ",@fgets($GLOBALS[SOCK],512));
 if($stat[0]!="+OK") return "391";
 $_SESSION[MESSAGES][SIZE]=$stat[2];
 for($i=$start;$i<($stat[1]+1);$i++) {
  @fputs($GLOBALS[SOCK],"list $i\r\n");
  $list=split(" ",@fgets($GLOBALS[SOCK],512));
  if($list[0]!="+OK") return "392";
  $_SESSION[MESSAGES][CONTENT][$i][SIZE]=$list[2];
  $header="";
  fputs($GLOBALS[SOCK],"top $i 0\r\n");
  while(($rs=fgets($GLOBALS[SOCK],512))!=".\r\n") $header.=$rs;
  $_SESSION[MESSAGES][CONTENT][$i][HEADER]=$header;
  $_SESSION[MESSAGES][CONTENT][$i][READ]=0;
  $_SESSION[MESSAGES][CONTENT][$i][DELETE]=(-1);
 }
 return "31";
}

function POP3RetrieveMessage($i) {
 POP3OpenConnectionAndLogin();
 fputs($GLOBALS[SOCK],"retr $i\r\n");
 if(strstr(@fgets($GLOBALS[SOCK],512),"+OK")) {
  while(($rs=@fgets($GLOBALS[SOCK],512))!="\r\n") { }
  while(($rs=@fgets($GLOBALS[SOCK],512))!=".\r\n") $message[]=trim($rs);
 } else {
  $message="Error trying to retrieve message ID <b>#$i</b>";
 }
 if($GLOBALS[SOCK]) POP3CloseConnection();
 return $message;
}

function POP3DeleteMessages() {
 POP3OpenConnectionAndLogin();
 for($i=1,$j=1;$i<count($_SESSION[MESSAGES][CONTENT])+1;$i++) {
  if($_SESSION[MESSAGES][CONTENT][$i][DELETE]>0) {
   fputs($GLOBALS[SOCK],"dele $i\r\n");
   $del=split(" ",fgets($GLOBALS[SOCK],512));
   echo(((trim($del[0])=="+OK")?"Message number <b>$i</b> deleted":"Error deleting message number <b>$i</b>")."<br>\n");
  } else {
   $new_message_list[$j]=$_SESSION[MESSAGES][CONTENT][$i];
   $j++;
  }
 }
 $_SESSION[MESSAGES][CONTENT]=$new_message_list;
 echo("<p><b>Wait while realoading messages...</b></p>\n");
 if($GLOBALS[SOCK]) POP3CloseConnection();
}

function POP3CloseConnection() {
 @fputs($GLOBALS[SOCK],"quit\r\n");
 @fclose($GLOBALS[SOCK]);
 sleep(1);
 return;
}

function MessageParseHeader($header) {
 for($j=0;$j<count($header);$j++) {
  $hd=split(":",$header[$j],2);
  if(preg_match_all("/\s/",$hd[0],$matches) || !$hd[1]) {
   if($last_header) $parsed_header[$last_header].="\r\n".trim($header[$j]);
  } else {
   $last_header=strtolower($hd[0]);
   $parsed_header[$last_header].=(($parsed_header[$last_header])?"\r\n":"").trim($hd[1]);
  }
 }
 if(is_array($parsed_header)) foreach($parsed_header as $hd_name=>$hd_content) {
  $start_enc_tag=$stop_enc_tag=0;
  $pre_text=$enc_text=$post_text="";
  while(1) {
   if(strstr($hd_content,"=?") && strstr($hd_content,"?=") && substr_count($hd_content,"?")>3) {
    $start_enc_tag=strpos($hd_content,"=?");
    $pre_text=substr($hd_content,0,$start_enc_tag);
    do {
     $stop_enc_tag=strpos($hd_content,"?=",$stop_enc_tag)+2;
     $enc_text=substr($hd_content,$start_enc_tag,$stop_enc_tag);
    } while (!(substr_count($enc_text,"?")>3));
    $enc_text=explode("?",$enc_text,5);
    switch(strtoupper($enc_text[2])) {
     case "B":
      $dec_text=base64_decode($enc_text[3]);
      break;
     case "Q":
     default:
      $dec_text=quoted_printable_decode($enc_text[3]);
      $dec_text=str_replace("_"," ",$dec_text);
      break;
    }
    $post_text=substr($hd_content,$stop_enc_tag);
    if(substr(ltrim($post_text),0,2)=="=?") $post_text=ltrim($post_text);
    $hd_content=$pre_text.$dec_text.$post_text;
    $parsed_header[$hd_name]=$hd_content;
   } else break;
  }
 }
 return $parsed_header;
}

function MessageRetrieveContent($header,$message) {
 $content_transfer_encoding=strtolower(trim($header["content-transfer-encoding"]));
 if($content_transfer_encoding=="") $content_transfer_encoding="8bit";
 $message=TextDecode($content_transfer_encoding,$message);

 $content_type=split(";",$header["content-type"],2);
 for($i=0;$i<count($content_type);$i++) $content_type[$i]=trim($content_type[$i]);
 if($content_type[0]=="") $content_type[0]="text/plain";
 if(stristr($content_type[0],"multipart/") || stristr($content_type[0],"message/")) $content_type[0]="multipart";
 if($header["content-disposition"] && !stristr($header["content-disposition"],"inline")) {
  if($header) $GLOBALS[parsed_message]["attachments"][]=array("header"=>$header, "content"=>@implode("\n",$message));
 } else {
  switch(trim(strtolower($content_type[0]))) {
   case "text/plain":
    $message=nl2br(htmlentities(implode("\n",$message)));
    if(trim($message)) $GLOBALS[parsed_message]["text-plain"]=$message;
    break;
   case "text/html":
    $GLOBALS[parsed_message]["text-html"]=implode("\n",$message);
    break;
   case "multipart":
    $content_type[1]=split(";",$content_type[1]);
    foreach($content_type[1] as $ct_pars) {
     if(stristr($ct_pars,"boundary")) {
      $ct_pars=split("=",trim($ct_pars),2);
      if(strtolower($ct_pars[0])=="boundary") $boundary=str_replace("\"","",$ct_pars[1]);
     }
    }
    if($boundary) {
     $parts=MessageSplitMultipart($boundary,$message);
     foreach($parts as $part) ParsePart($part);
    } else {
     $GLOBALS[parsed_message]["text-plain"]="<p>".$GLOBALS[VER][NAME]." ".$GLOBALS[VER][MAJOR].".".$GLOBALS[VER][MINOR]."<br>Sorry, I'm unable to read this mail.<br>Please, report this error to <a href='http://wiz.homelinux.net/bugreport.php?prj=Nameko'>Wiz's Shelf Staff</a></p>";
    }
    break;
   default:
    if($header) $GLOBALS[parsed_message]["attachments"][]=array("header"=>$header, "content"=>@implode("\n",$message));
    break;
  }
 }
 return($GLOBALS[parsed_message]);
}

function MessageSplitMultipart($boundary,$text) {
 $parts=array();
 $tmp=array();
 foreach($text as $line) {
  if(strstr($line,"--$boundary")) {
   $parts[]=$tmp;
   $tmp=array();
  } else $tmp[]=$line;
 }
 for($i=0;$i<count($parts);$i++) $parts[$i]=explode("\n",trim(implode("\n",$parts[$i])));
 return $parts;
}

function ParsePart($text) {
 $headerpart=array();
 $contentpart=array();
 foreach($text as $riga) {
  if(!$riga) $noheader++;
  if($noheader) $contentpart[]=$riga;
  else $headerpart[]=$riga;
 }
 if(count($contentpart)==0) {
  $contentpart=$headerpart;
  $headerpart=array();
 }
 MessageRetrieveContent(MessageParseHeader($headerpart),explode("\n",trim(implode("\n",$contentpart))));
}

function TextDecode($encoding,$text) {
 switch($encoding) {
  case "quoted-printable":
   $dec_text=explode("\n",quoted_printable_decode(implode("\n",$text)));
   break;
  case "base64":
   for($i=0;$i<count($text);$i++) $text[$i]=trim($text[$i]);
   $dec_text=explode("\n",base64_decode(@implode("",$text)));
   break;
  case "7bit":
  case "8bit":
  case "binary":
  default:
   $dec_text=$text;
   break;
 }
 return $dec_text;
}

function ValidateEmailAddress($addr) {
 $addr_len=strlen($addr);
 $at_pos=strpos($addr,"@");
 $lastdot_pos=strrpos($addr,".");
 for($i=0;$i<$addr_len;$i++) {
  if(!strstr($GLOBALS[LEGAL_CHARS],$addr[$i])) return 0;
 }
 if(($addr[0]==".") || ($addr[0]=="-") || ($at_pos<1) || ($at_pos>($addr_len-5)) || ($at_pos>($lastdot_pos-2)) || ($lastdot_pos>$addr_len-3) || (substr_count($addr,"@")>1)) return 0;
 return 1;
}

function GetAddressFromFromHeader($addr) {
 $atpos=strpos($addr,"@");
 $minpos=strpos($addr,"<");
 $majpos=strpos($addr,">");
 $fromstart=0;
 $fromend=strlen($addr);
 if($minpos<$atpos && $majpos>$atpos) {
  $fromstart=$minpos+1;
  $fromend=$majpos;
 }
 return substr($addr,$fromstart,$fromend-$fromstart);
}

function FlushTmpDir() {
 if(!is_dir("tmp_nameko")) @mkdir("tmp_nameko",0700);
 if(is_dir("tmp_nameko")) {
  $tmpdir=opendir("tmp_nameko");
  while(($tmpfile=readdir($tmpdir))!=false) {
   if(($tmpfile!="..")&&($tmpfile!=".")) {
    $tmpfiledate=filectime("tmp_nameko/$tmpfile");
    if((filectime("tmp_nameko/$tmpfile"))<(time()-300)) @unlink("tmp_nameko/$tmpfile");
   }
  }
  closedir($tmpdir);
  return 1;
 } else {
  return 0;
 }
}




//===============================================
// NamekoSendMailWithExternalSMTPServer Class
//===============================================

class NamekoSendMailWithExternalSMTPServer {
  var $SMTPServer;
  var $sender;
  var $destination;
  var $headers;
  var $body;
  var $SMTPConnection;

  function NamekoSendMailWithExternalSMTPServer($SMTPServer) {
    $this->SMTPServer = $SMTPServer;
  }

  function setMailParameters($sender, $destionation, $headers, $subject, $body) {
    $this->sender = $sender;
    $this->destination = $destionation;
    $this->headers = "Subject: $subject\nTo: $destionation\n$headers";
    $this->body = $body;
  }

  function sendMailThroughExternalSMTPServer() {
    if(!$this->sender || !$this->destination || !$this->headers || !$this->body) return false;
    if($this->_openSTMPConnection() && $this->_sendMailFromCommand() && $this->_sendRcptToCommand() && $this->_sendDataCommand()) {
      $this->_closeSTMPConnection();
      return true;
    } else {
      return false;
    }
  }

  function _openSTMPConnection() {
    $this->SMTPConnection = @fsockopen($this->SMTPServer, 25);
    if(!$this->SMTPConnection) return false;
    $rs = @fgets($this->SMTPConnection, 512);
    @fputs($this->SMTPConnection, "helo $_ENV[hostname]\r\n");
    $rs = @fgets($this->SMTPConnection, 512);
    return true;
  }

  function _sendMailFromCommand() {
    @fputs($this->SMTPConnection, "mail from: $this->sender\r\n");
    if(strstr(@fgets($this->SMTPConnection, 512), "250")) return true;
    else return false;
  }

  function _sendRcptToCommand() {
    @fputs($this->SMTPConnection, "rcpt to: $this->destination\r\n");
    if(strstr(@fgets($this->SMTPConnection, 512), "250")) return true;
    else return false;
  }

  function _sendDataCommand() {
    @fputs($this->SMTPConnection, "data\r\n");
    if(strstr(@fgets($this->SMTPConnection, 512), "354")) {
      @fputs($this->SMTPConnection, "$this->headers\r\n\r\n$this->body\r\n.\r\n");
      if(strstr(@fgets($this->SMTPConnection, 512), "250")) return true;
      else return false;
    }
    else return false;
  }

  function _closeSTMPConnection() {
    @fputs($this->SMTPConnection, "quit\r\n");
    $rs = @fgets($this->SMTPConnection, 512);
  }
}
?>
