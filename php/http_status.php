<?php
//just a simple script togive you an ideea abou what PHP can do
function ServerInfo($ServerURL)
{
  $server = fsockopen($ServerURL,80,&$errno,&$errstr);
  stream_set_timeout($server,2);
  if(!$server)
  {
    $WebServer= "Error: $errstr ($errno)<br>";
  }
  else
  {
    fputs($server, "GET / HTTP/1.0\n\n");
    while(!feof($server))
    {
      $WebServer=fgets($server,4096);
	  error_log($WebServer."\n",3,'info.log');
      if (ereg( "^Server:",$WebServer))
      {
        $WebServer=trim(ereg_replace( "^Server:", "",$WebServer));
        break;
      }
    }
    fclose($server);
  }
  return($WebServer);
}
if (!empty($_POST['ServerURL'])) { $WebServer=ServerInfo($_POST['ServerURL']); }
?><HTML>
<HEAD>
<TITLE>Server Info</TITLE>
</HEAD>
<BODY bgcolor=#c0c0c0 ><pre>
<?PHP
print_r($_SERVER);
//back();
if (!empty($_POST['ServerURL']))
{ echo( "<font color=darkblue size=4><b><PRE>Server ".$_POST['ServerURL']." is running $WebServer.</PRE></font>"); } ?>
<br><br>
<FORM ACTION="<?php echo($_SERVER['PHP_SELF']); ?>" METHOD="post">
  <font color="darkblue"><b>http://</b></font><input TYPE="text" NAME="ServerURL" SIZE="40" MAXLENGTH="100">
  <INPUT TYPE=hidden NAME="WebServer" VALUE="">
  <INPUT TYPE=submit VALUE="Spy this Server!"><INPUT TYPE=reset VALUE="Reset">
</FORM>
</BODY>
</HTML>
