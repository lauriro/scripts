#!/bin/sh

kala() {
	while read line; do
		echo "rida $line"
	done
}

kala<(echo ok)

validname()
 case $1 in
   [!a-zA-Z_]* | *[!a-zA-Z0-9_]* ) return 1;;
 esac

setvar()
{
  validname "$1" || return 1
  eval "$1=\$2"
}

getvar()
{
  validname "$1" || return 1
  eval "printf '%s\n' \"\${$1}\""
}


## sample run
n=whatever
setvar board_$n Testing || { echo bad; exit 1; }
getvar board_$n


$ time find . -type d | awk '{SUM += 1; print gsub("/","",$0)} END {print "Sum " SUM}' | sort | uniq -c


