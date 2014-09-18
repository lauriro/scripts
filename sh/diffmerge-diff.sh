#!/bin/sh

if [ ! -f "$2" -o ! -f "$5" ] ; then
	echo "One side missing: $2 vs. $5"
	exit 0
fi

#trap cleanup 1 2 3 6

#cleanup()
#{
# echo "Caught Signal ... cleaning up."
#  rm -rf /tmp/temp_*.$$
#  echo "Done cleanup ... quitting."
#  exit 1
#}


if which cygpath &> /dev/null; then
	path="$(cygpath $1)"
	old="$(cygpath --mixed --absolute "$2")"
	new="$(cygpath --mixed --absolute "$5")"
else
	path="$1"
	old="$2"
	new="$5"
fi

#echo -e "path\n$path"
#echo -e "old\n$old"
#echo -e "new\n$new"

if [ -f "$old" -a -f "$new" ] ; then
	/cygdrive/c/Program\ Files/SourceGear/DiffMerge/DiffMerge.exe -nosplash "$new" "$old" --title1="Current $path" --title2="Old"
fi
