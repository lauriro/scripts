#!/bin/sh


nonce() {
	date +%s.$$.$RANDOM.%N | md5sum | cut -d' ' -f 1
}

encode() {
	printf "$1" | while read -n1 -r s; do
		case "$s" in
			[-_.~a-zA-Z0-9]) printf $s ;;
			*)               printf %%%02x "'$s"
		esac
	done
	printf \\n
}

decode() {
	printf "%b" "${1//%/\\x}"
}

nonce
encode "s\nb c"
decode "s%0ab%20c"




