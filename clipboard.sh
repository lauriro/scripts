#!/bin/sh

try() {
	set -- "$@"
	while [ $# -gt 0 ] ; do
		command -v ${1%% *} >/dev/null 2>&1 && {
			$1
			return
		}
		shift
	done
}

##################################
# Copy to the clipboard
printf %s "$@" | try "pbcopy" "xclip -selection clipboard" "xsel -b -i" 'echo WARNING: Failed to copy clipboard'

