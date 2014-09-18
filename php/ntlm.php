<?php
$headers = apache_request_headers();
if ($_SERVER['HTTP_VIA'] != NULL){
    echo "Proxy bypass!";
}
elseif($headers['Authorization'] == NULL){    

        header( "HTTP/1.0 401 Unauthorized" );          
        header( "WWW-Authenticate: NTLM" );
        exit;

    }

    if(isset($headers['Authorization']))
    {       
        if(substr($headers['Authorization'],0,5) == 'NTLM '){   
    
            $chaine=$headers['Authorization'];                  
            $chaine=substr($chaine, 5);             
            $chained64=base64_decode($chaine);      
            
            if(ord($chained64{8}) == 1){                    
            
                if (ord($chained64[13]) != 178){
                    echo "NTLM Flag error!";
                    header ("Location: https://yoursite.com/moodle/");
                }
    
                $retAuth = "NTLMSSP".chr(000).chr(002).chr(000).chr(000).chr(000).chr(000).chr(000).chr(000);
                $retAuth .= chr(000).chr(040).chr(000).chr(000).chr(000).chr(001).chr(130).chr(000).chr(000);
                $retAuth .= chr(000).chr(002).chr(002).chr(002).chr(000).chr(000).chr(000).chr(000).chr(000);
                $retAuth .= chr(000).chr(000).chr(000).chr(000).chr(000).chr(000).chr(000);
                
                $retAuth64 =base64_encode($retAuth); 
                $retAuth64 = trim($retAuth64);     
                header( "HTTP/1.0 401 Unauthorized" );     
                header( "WWW-Authenticate: NTLM $retAuth64" ); 
                exit;
            
            }
            
            else if(ord($chained64{8}) == 3){

                $lenght_domain = (ord($chained64[31])*256 + ord($chained64[30])); 
                $offset_domain = (ord($chained64[33])*256 + ord($chained64[32]));
                $domain = str_replace("\0","",substr($chained64, $offset_domain, $lenght_domain)); 
                

                $lenght_login = (ord($chained64[39])*256 + ord($chained64[38])); 
                $offset_login = (ord($chained64[41])*256 + ord($chained64[40])); 
                $login = str_replace("\0","",substr($chained64, $offset_login, $lenght_login)); 
$moodlentlogin=$login;
                if ( $login != NULL){

                }
                else{
                    header ("Location: https://yoursite.com/moodle/");
                }
            }
        }
}
?>



<form action="https://yoursite.com/moodle/login/index.php" method="post" name="login" id="form" target="_blank">
  <p><input type="text" name="username" value="<?php echo $moodlentlogin ?>">
  <p><input type="hidden" name="password" value="">
<a href="https://yoursite.com/moodle/" There was an error. Contact your Administrator or here to continue.</a>
<script language="JavaScript">
function Validate()
{
document.login.submit();
self.opener = this;
self.close();
}
Validate();
</script>

</form>