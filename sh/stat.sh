#!/bin/sh

I=1

read ROW
ROW=( $ROW )
MIN=(${ROW[@]})
MAX=(${ROW[@]})
SUM=(${ROW[@]})

while read ROW; do
	I=$(($I+1))
	J=0
	ROW=( $ROW )
	for COL in ${ROW[@]}; do
		[[ $COL -lt "${MIN[$J]}" ]] && MIN[$J]=$COL
		[[ $COL -gt "${MAX[$J]}" ]] && MAX[$J]=$COL
		SUM[$J]=$((${SUM[$J]}+$COL))
		J=$(($J+1))
	done
done

J=0
for S in ${SUM[@]}; do
	echo "col $J min: ${MIN[$J]} max: ${MAX[$J]} sum: $S avg: $(($S/$I))"
	J=$(($J+1))
done

