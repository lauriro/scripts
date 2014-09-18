
ip-reverse() {
	echo $1 | awk -F. '{print $4"."$3"."$2"."$1}'

	echo $1 | IFS=. read a b c d
	echo $d.$c.$b.$a
	
	set -- $*
	echo $4.$3.$2.$1
}

ip-reverse '1.2.3.4'
