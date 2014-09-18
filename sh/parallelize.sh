#!/bin/sh

HOSTS="holly holly holly holly hilly hilly hilly hilly parasite parasite parasite parasite paradise paradise paradise paradise xenon uranium krypton gallium lithium nickel chromium polonium aluminum chlorine indium tin helium iron mercury silver"

DESTDIR=/export/home/jls9398/data/

# Machine pool size
POOL=`echo $HOSTS | wc -w`

# How many arguments we have
ARGC=$#

# usage: process dsthost srcfile
process() {
	sh << SHELL &
echo "Process sent to $1 ($2)"
cat $1 | ssh -o "StrictHostKeyChecking no" jls9398@$2 "perl bin/parsehttp" 2>&1 > data/rrdupdates.$2.\$\$ | sed -e "s,^,$3: $2($1): ,"
echo "Finished: $1 ($2) 
SHELL
}

# Multiply hosts until we have a large enough list for $# calls
MUL=`expr $ARGC / $POOL + 1`

i=0
while [ "$i" -lt $MUL ]; do
	HOSTPOOL="$HOSTPOOL $HOSTS"
	i=`expr $i + 1`
done

CUR=0

mkdir -p $DESTDIR
echo "Main pid: $$"
while [ $# -gt 0 ]; do
	CUR=`expr $CUR + 1`
	process $1 `echo $HOSTPOOL | awk "{print \\$${CUR}}"` $CUR
	shift
done

echo "Waiting for all tasks to be completed"
wait
