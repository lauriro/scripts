<ol><?php

error_reporting ('E_ALL');

exec("du --max-depth=1 --block-size=1048576 --total /home/la01/lauri/", $output);

exec("du --help", $output);
foreach ($output as $k=>$line) {
	echo '<li>'.$line.'<br>';
}

//echo '<hr>'.date('G:i');
?></ol>