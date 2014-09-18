#!/bin/bash
#
# $Id: logrotate 3 2008-02-01 15:43:15Z iain $
#
# Generic logrotate for flat files or multilog directories.
# Format is FreeBSD rc.conf style (see http://iain.cx/src/logrotate/ or 
# example below).
#
# Changes:
# 2008-02-01: Don't clobber existing gzipped logs.
# 2007-03-04: Fixed return code checking on cat.
# 2006-11-20: Paths to everything else are configurable.
# 2006-02-17: Old file suffix is configurable.
# 2004-01-14: Added copy_xxx option.
# 2003-05-21: Path to gzip is configurable.
# 2003-05-21: One of the gzip actions didn't have return code checked.
#
# Credits:
# Toni Giorgino <toni.giorgino@unipv.it>: gzip path and error checking.
# Stephen Crosby <stevecrozz@gmail.com>: Configure other paths.
#

# Configuration:
# Edit these as appropriate:
#     $archive is where the rotated logs will go.
#     $firstly will be run before the script does anything.
#     $finally will be run before the script exits.
# Set $verbose to something to be verbose (or use the argument -v).
archive="/archives"
firstly=""
finally=""
verbose=

# Set the full paths to these if your environment requires it.
#     $date must be GNU date.
#     $gzip must be GNU gzip.
cat="cat"
cp="cp"
date="date"
grep="grep"
gzip="gzip"
mkdir="mkdir"
mv="mv"
rm="rm"

# Define your targets:
# For each xxx in $logs, you MUST define:
#   path_xxx: Directory where the logs are stored.
#   files_xxx: Files (or multilog directories) to be rotated (wildcards are OK).
# You MAY define:
#   prefix_xxx: 
#   suffix_xxx:
#     By default, logs are archived into $archive/yyyy/$xxx/yyyy-mm-dd.gz.
#     Use these variables to change this to
#     $archive/yyyy/$prefix_xxx/$xxx.$suffix_xxx/yyyy-mm-dd.gz.
#   hup_xxx: Command to be run after processing xxx.
#   touch_xxx: Set to something to touch the logfile after rotating.
#   keep_xxx: Set to something to keep the logfile and not delete it.
#   copy_xxx: Set to something to snapshot the logfile and archive the copy.
#   old_xxx: Override the suffix with which archived (kept) files will be 
#            renamed.  Default is .old.
logs="sys qmail"
path_sys="/var/log"
files_sys="messages"
hup_sys="killall klogd"
path_qmail="/var/log/qmail"
files_qmail="*"
prefix_qmail="qmail"

# No changes should be necessary past this point.
# If you need to make a change then consider sending a bug report to me@iain.cx

# If for some reason you can't get GNU date you'll need to change this to suit.
# year should return a year (eg 2001) and yesterday should be a date in 
# yyyy-mm-dd format (I suppose Solaris date could be hacked because it uses 
# strftime() but who cares? use perl or something).
# Changing these lines doesn't count as a bug wrt the previous paragraph 8-7
year=$($date +%Y --date="1 day ago")
yesterday=$($date +%Y-%m-%d --date="1 day ago")
if ! echo $year-$yesterday | $grep -q '....-....-..-..'; then
  echo "fatal: date command '$date' didn't return sensible values"
  exit 100
fi

# Keep track of errors.
errors=0

# Override verbosity level.
[ "$1" = "-v" -o "$1" = "--verbose" ] && verbose=yes

function stuff() {
  say "--->" processing target "$log"
  # Make sure these variables start afresh.
  unset path files prefix suffix touch copy keep hup old
  ret=0

  # What's our base directory?
  eval path="\$path_$log"
  if [ "$path" = "" ]; then
    echo error: \$path_$log is undefined\; skipping $log
    break
  fi
  say log directory is "$path"

  # Which files do we need to rotate?
  eval files="\$files_$log"
  if [ "$files" = "" ]; then
    echo error: \$files_$log is undefined\; skipping $log
    break
  fi
  say files glob is "\"$files\""

  # Shall we archive them under a specific directory?
  # Logs will be put under $archive/$year/$prefix.
  eval prefix="\$prefix_$log"

  # Append a suffix to directory name? (probably only useful for multilogs).
  # No checks will be made to ensure the directory name plus this suffix 
  # form a valid directory name so be sensible in picking a suffix.
  eval suffix="\$suffix_$log"
  say prefix=\"$prefix\", suffix=\"$suffix\"

  # Shall we touch the old file (syslogd needs this on Solaris)?
  # If you do this remember that the new file will be owned by root.
  # You might need to have $hup fix this.
  eval touch="\$touch_$log"
  [ "$touch" = "" ] || say we will touch new files

  # Shall we copy the files and archive the copies (instead of renaming them)?
  eval copy="\$copy_$log"
  [ "$copy" = "" ] || say we will copy files instead of renaming them

  # Kept files will be renamed with this suffix.
  eval old="\$old_$log"
  if [ "$old" = "" ]; then
    old=.old
  else
    say we will renamed old files with the $old suffix
  fi

  # Shall we keep the old files (apache wants this if you run analog afterwards)
  # If this is unset they will be removed.
  eval keep="\$keep_$log"
  [ "$keep" = "" ] || say we will keep old files

  # Do we need to kill anything?
  eval hup="\$hup_$log"

  # First rename the old files and do the hupping.
  pushd "$path" >/dev/null
  # All this is in an eval because $files can contain arbitrary shell commands.
  eval "for i in $files; do if [ -f \"\$i\" ]; then \
    if [ \"\$copy\" = \"\" ]; then \
      say renaming \$i to \$i$old; \
      $mv \"\$i\" \"\$i$old\"; \
      ret=\$((ret+\$?)); \
      [ \"\$touch\" = \"\" ] || { say touching \$i; touch \"\$i\"; } \
    else
      say copying \$i to \$i$old; \
      $cp -p \"\$i\" \"\$i$old\"; \
      ret=\$((ret+\$?)); \
    fi
  elif [ -d \"\$i\" ]; then \
    say \$i looks like a multilog directory
    do_multilog_rotate \"\$i\"; \
    ret=\$((ret+\$?)); \
  fi; done"
  if [ ! "$hup" = "" ]; then
    say running hup command "$hup"
    eval $hup
  fi

  # Unique stamp in case we need it later.
  unique="$($date +'%s').$$"

  # Now archive the old 'uns.
  say now archiving old files
  for i in *$old; do if [ -f "$i" ]; then
    j="$archive/$year/$prefix/$(basename $i)"
    j="${j%%$old}$suffix"
    say archive directory is "$j"
    $mkdir -p "$j"
    if [ -e "$j/$yesterday.gz" ]; then
      say $yesterday exists - renaming to $yesterday.$unique
      yesterday="$yesterday.$unique"
    fi
    say gzipping "$i" to $yesterday.gz
    $gzip -c "$i" > "$j/$yesterday.gz"
    let ret=$((ret+$?))
    if [ "$keep" = "" ]; then
      say gzipping successful\; removing "$i"
      $rm "$i"
    fi
  fi; done

  # Just in case.
  popd >/dev/null
  if [ $ret -gt 0 ]; then
    echo "warning: errors occurred processing target $log"
    errors=$((errors+1))
  fi
  say finished with $log
}

# Rotate out djb multilogs.
# We gzip up any .s or .u files in chronological order
function do_multilog_rotate() {
  local i="$1"
  if [ ! -d "$i" ]; then
    echo "for pity's sake how did we call do_multilog_rotate() on a non dir?"
    return 1
  fi
  pushd "$i" >/dev/null
  local errors=0
  local ret=0
  local count=0
  local logdir="$archive/$year/$prefix/$(basename $i)$suffix"
  say archive directory is "$logdir"
  $mkdir -p "$logdir"
  if [ -d "$logdir" ]; then
    for j in *.[su]; do
      count=$((count+1))
      if [ -f "$j" ]; then
        say appending "$j" to $yesterday.new
        $cat "$j" >> "$logdir/$yesterday.new"
        ret=$?
        if [ $ret = 0 ]; then $rm "$j"; fi
      elif [ "$j" = "*.[su]" ]; then
        # If there were no file this isn't necessarily an error.
        # This quick hack prevents complaints later.
        count=0
        say no old logs found in $i
      else
        # Now there really is a problem.
        echo "warning: multilog fragment $path/$i/$j is not a regular file"
        ret=1
      fi
      errors=$((errors+$ret))
    done
  else
    echo "error: failed to create $logdir"
  fi

  # Paranoia.
  if [ $errors -a -f "$logdir/$yesterday.new" ]; then
    say gzipping $yesterday.new to $yesterday.gz
    $gzip -c "$logdir/$yesterday.new" >> "$logdir/$yesterday.gz"
    if [ $? = 0 ]; then
      say gzipping successful\; removing $yesterday.new
      $rm "$logdir/$yesterday.new"
    else
      echo "warning gzip of $logdir/$yesterday.new failed"
      errors=$((errors+1))
    fi
  elif [ $errors -gt 0 ]; then
    echo "error: something bad happened trying to rotate $path/$i"
  elif [ $count -gt 0 ]; then
    echo "warning: multilog directory $path/$i was not rotated"
  elif [ ! -f current ]; then
    echo "error: $i (referenced by \$files_$log) is not a multilog directory"
  fi
  popd >/dev/null
  return $errors
}

function say() {
  [ "$verbose" = "" ] && return
  echo info: ${1+"$@"}
}

# int main(int argc, char **argv) {
[ "$firstly" = "" ] || say calling $firstly
eval $firstly
for log in $logs; do stuff; done
if [ $errors -gt 0 ]; then
  echo -n warning: $errors target
  [ $errors = 1 ] || echo -n s
  echo " had errors"
fi
say "that's all folks"
[ "$finally" = "" ] && exit $errors
say calling $finally
eval $finally
# }
