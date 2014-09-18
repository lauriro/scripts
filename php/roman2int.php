<!DOCTYPE html>
<html><head>
<title>roman2int</title>
</head>
<body>

<form method=post action="">
<input type="text" name="roman"> <input type="submit">
</form>

<?php

function roman2int($str){
	$sum=0;
	$a=array('I'=>1,'V'=>5,'X'=>10,'L'=>50,'C'=>100,'D'=>500,'M'=>1000);
	$i=strlen($str);
	while($i--){
		if(isset($a[$str{$i}])){
			$num=$a[$str{$i}];
			$sum+=$num;
			while($i&&isset($a[$str{($i-1)}])&&$a[$str{($i-1)}]<$num){
				$sum-=$a[$str{--$i}];
			}
		}
	}
	return $sum;
}

if(isset($_POST['roman'])){
	echo '<b>'.$_POST['roman'].'</b>-st loen välja: '.roman2int(strtoupper($_POST['roman']));
}

?><hr><pre>

function roman2int($str){
	$sum=0;
	$a=array('I'=>1,'V'=>5,'X'=>10,'L'=>50,'C'=>100,'D'=>500,'M'=>1000);
	$i=strlen($str);
	while($i--){
		if(isset($a[$str{$i}])){
			$num=$a[$str{$i}];
			$sum+=$num;
			while($i&&isset($a[$str{($i-1)}])&&$a[$str{($i-1)}]<$num){
				$sum-=$a[$str{--$i}];
			}
		}
	}
	return $sum;
}

function x(a){for(var v,s=0,i=a.length;i--;s+=v-(v=[100,500,,,,,1,,,50,1e3,,,,,,,,,5,,10][parseInt(a.charAt(i),36)-12])>0?-v:v);return s}
function a(a){for(var s=0,m={I:1,V:5,X:10,L:50,C:100,D:500,M:1e3},v,l,i=a.length;v=m[a.charAt(--i)];s+=v<l?-v:l=v);return s}
function b(a){for(var s=0,m={I:1,V:5,X:10,L:50,C:100,D:500,M:1e3},v,l,i=a.length;i;s+=(v=m[a.charAt(--i)]|0)<l?-v:l=v);return s}
function c(a){for(var s=0,m={I:1,V:5,X:10,L:50,C:100,D:500,M:1e3},v,l,i=a.length;v=m[a.charAt(--i)];s+=v<l?-v:l=v);return s}
function d(a){for(var s=0,m={I:1,V:5,X:10,L:50,C:100,D:500,M:1e3},v,l,a=a.split("");v=m[a.pop()];s+=v<l?-v:l=v);return s}
function e(a){for(var s=0,m={I:1,V:5,X:10,L:50,C:100,D:500,M:1e3},v,l,a=a.split("");v=a.pop();s+=(v=m[v]|0)<l?-v:l=v);return s}

</pre>

</body>

</html>

