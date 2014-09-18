<?php


mysql_select_db( $dbname );
$result = mysql_query( "SHOW TABLE STATUS" );
$dbsize = 0;

while( $row = mysql_fetch_array( $result ) ) {  
	$dbsize += $row[ "Data_length" ] + $row[ "Index_length" ];
}

?>