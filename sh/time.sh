#!/bin/bash
script_start=$(date +%s)
LOG=time.log
 
 
duration()
{
  start=$(date +%s)
  $*
  finish=$(date +%s)
  let total="$finish - $start"
  echo "Function $1 took $total seconds" >>$LOG
}
 
foo()
{
  echo foo
  sleep 2
}
 
duration foo
 
script_finish=$(date +%s)
let total="$finish - $start"
echo "Script Finished!  Script took $total seconds"
