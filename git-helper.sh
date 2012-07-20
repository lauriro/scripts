#!/bin/sh
#
# Makefile helper functions
#
# Usage: ./git-helper.sh {todo|clean|authors|changelog|news|thanks|version}
#
#
# THE BEER-WARE LICENSE
# =====================
#
# <lauri@rooden.ee> wrote this file. As long as you retain this notice you 
# can do whatever you want with this stuff at your own risk. If we meet some 
# day, and you think this stuff is worth it, you can buy me a beer in return.
# -- Lauri Rooden -- https://github.com/lauriro/scripts
#
#
# Dependencies
# ============
#
# The following is a list of compile dependencies for this project. These
# dependencies are required to compile and run the application:
#   - Unix tools: sed
#
#


title() {
	printf "\n%s\n%0*d\n" "$1" ${#1} | sed "\$s/0/${2-=}/g"
}

tag_title() {
	DATE=$(git show -s --format="%ci" $1 | tail -n1)
	NAME=$(git describe --tags --exact-match $1 2>/dev/null);
	[ "$NAME" ] && title "${DATE%% *} version $NAME" "-" && echo ""
}

comments() {
	grep -IrinE "^[ 	]*(//|/\*|##*|--+)[ 	]*($1)" ${SRC-*} |
	sed -E -e "s,^([^:]*:[0-9]*):[ 	/*#-]*$2(.*),  * \2 \[\1\],"
}



#- Usage: make COMMAND
#- 
#- The most commonly used make commands are:
#- 
#-    help            Default target. Displays this menu
#-    todo            Show TODOs from comments
#-    clean           Remove untracked files
#- 

git_todo() {
	printf '\nList of planned enhancements and bugs.\n\n'
	comments 'TODO|FIXME|BUG(BUG)?' | sort -r
}

git_clean() {
	git clean -xdf
}


#- Documentation commands are:
#- 
#-    authors         Create AUTHORS file from git log
#-    changelog       Create ChangeLog file from git log
#-    news            Create NEWS file from git log
#-    thanks          Create THANKS file from source
#-    version         Create VERSION file from git
#- 

git_authors() {
	printf '\nAuthors ordered by number of commits.\n\n' > AUTHORS
	git shortlog -sen | sed -E -e 's/[ 	]*([0-9]*)[	 ]*(.*)/  * \2 (\1)/g' >> AUTHORS
	cat AUTHORS
}

git_changelog() {
	title 'ChangeLog' > ChangeLog

	# insert blank line after header when HEAD is not tagged
	git describe --tags --exact-match HEAD >/dev/null 2>&1 || echo "" >> ChangeLog;

	git log --no-merges --pretty='tformat:%h|%d|%s (%aN)' | while IFS='|' read HASH TAG MSG; do
		[ -n "$TAG" ] && tag_title $HASH
		echo "  * $MSG"
	done >> ChangeLog;
	cat ChangeLog
}

git_news() {
	title 'News' > NEWS
	git tag -l v* | sort -r | while read TAG; do
		tag_title $TAG
		git cat-file tag $TAG | tail -n+6
		echo ''
	done >> NEWS
	cat NEWS
}

git_thanks() {
	printf '\nThanks for acknowledgments (ordered by alphabet).\n\n' > THANKS
	comments 'THANKS' 'THANKS[: ]*' | sort >> THANKS
	cat THANKS
}

git_version() {
	# git describe --long --dirty 2>/dev/null > VERSION || echo "Initial version-g$(git rev-parse --short HEAD)" > VERSION
	echo "$(git describe --long 2>/dev/null || echo "Initial version-g$(git rev-parse --short HEAD)")$(git diff --no-ext-diff --quiet --exit-code || echo '-dirty')" > VERSION
	echo "Version: $(cat VERSION)"
}

git_activity() {
	q="CREATE TABLE IF NOT EXISTS stat (ts INT PRIMARY KEY, author TEXT, files int, insertions int, deletions int);";
	insert=$(git log --no-merges --shortstat --pretty="tformat:%at,'%an',." master..HEAD |
		sed -E -n '/,./{N;N;s/(.*),\.[^0-9]*([0-9]+)[^0-9]*([0-9]+)[^0-9]*([0-9]+)[^0-9]*/INSERT OR REPLACE INTO stat VALUES(\1,\2,\3,\4);/p}');
	select="SELECT strftime('%Y.%W', ts, 'unixepoch' ) as W, author, sum(insertions) AS i, sum(deletions) AS d FROM stat group by W, author;";
	echo "$q $insert $select" | sqlite3 .git/activity.db;
	#$ git log --date=iso8601 --pretty=format:"%ad" | cut -c12-13 | sort | uniq -c > heatmap_hour.txt
	#$ git log --date=rfc2822 --pretty=format:"%ad" | cut -c1-3 | sort | uniq -c > heatmap_weekday.txt
	#$ git log --date=short --pretty=format:"%ad" | sort -r | uniq -c > heatmap_date.txt
	#$ git log --date=iso8601 --pretty=format:"%ad" | cut -c1-10 | sort -r | uniq -c > heatmap_date.txt

	#$ git ls-tree --name-only -r HEAD | xargs -i@ git blame -we @ | cut -d' ' -f2 | sort | uniq -c

	#$ git log --date=short --pretty=format:"%x00%ad" --shortstat | tr -s "\n,\0" "  \n" | cut -d " " -f 1,2,5,7

	# number of commits
	#$ git shortlog -s --all | { SUM=0; while read NUM NAME; do SUM=$(($SUM+$NUM)); done; echo $SUM; }

}

[ "$1" ] && "git_$1"


