# Avoiding a subshell

COUNT=0

exec 3< ${1-"$0"}
while read line
do
	echo "$line"
	(( COUNT++ ));
done <&3

exec 3<&-

echo "Number of lines read = $COUNT"

exit 0

exec 3< thisfile          # open "thisfile" for reading on file descriptor 3
exec 4> thatfile          # open "thatfile" for writing on file descriptor 4
exec 8<> tother           # open "tother" for reading and writing on fd 8
exec 6>> other            # open "other" for appending on file descriptor 6
exec 5<&0                 # copy read file descriptor 0 onto file descriptor 5
exec 7>&4                 # copy write file descriptor 4 onto 7
exec 3<&-                 # close the read file descriptor 3
exec 6>&-                 # close the write file descriptor 6
Note that spacing is very important here. If you place a space between the fd number and the redirection symbol then exec reverts to the original meaning:

  exec 3 < thisfile       # oops, overwrite the current program with command "3"
There are several ways you can use these, on ksh use read -u or print -u, on bash, for example:

read <&3
echo stuff >&4




# http://www.linuxtopia.org/online_books/advanced_bash_scripting_guide/x13082.html
# https://www.sysmic.org/dotclear/index.php?post/2011/08/24/Process-communication-in-shell%3A-fifo%2C-redirection-and-coproc



