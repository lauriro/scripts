#!/bin/sh

localPath="$(cygpath --mixed --absolute "$2")"
basePath="$(cygpath --mixed --absolute "$1")"
remotePath="$(cygpath --mixed --absolute "$3")"
resultPath="$(cygpath --mixed --absolute "$4")"

if [ ! -f $basePath ]
then
    basePath="$(cygpath --mixed --absolute ~/diffmerge-empty)"
fi

#echo -ne "local\n$localPath\n"
#echo -ne "base\n$basePath\n"
#echo -ne "remote\n$remotePath\n"
#echo -ne "result\n$resultPath\n"

"/cygdrive/c/Program Files/SourceGear/DiffMerge/DiffMerge.exe" --merge --result="$resultPath" "$localPath" "$basePath" "$remotePath" --title1="Mine" --title2="Merged: $4" --title3="Theirs"