#!/bin/sh
#
# Tool for deploying apps to Google App Engine server
#
# Usage: ./google_appcfg.sh {update|rollback|setdefault} <directory>
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
#   - sha1sum
#   - Unix tools: cat, curl, cut, grep, sed
#
#


usage() {
	echo "Usage: $0 {update|rollback|setdefault} <directory>"
	exit 1
}


COOKIE_FILE=".google_cookie"
TEMP_FILE=".google_temp"
STATIC_FILES=".google_static"
API_URL="http://appengine.google.com/api/appversion/"


APP_DIR=${2:-.}
APP_FILE="${APP_DIR%/}/app.yaml"

[ ! -d $APP_DIR  ] && echo "$APP_DIR is not a directory" && exit 1
[ ! -r $APP_FILE ] && echo "$APP_FILE not found" && exit 1


APP_ID=$(grep -e "^application:" $APP_FILE | cut -f2 -d" ")
APP_VER=$(grep -e "^version:" $APP_FILE | cut -f2 -d" ")

[ -f $APP_ID  ] && echo "application not found in $APP_FILE" && exit 1
[ -f $APP_VER ] && echo "version not found in $APP_FILE" && exit 1


printf "\nApplication: $APP_ID, version $APP_VER\n"


google_auth () {
	. google-clientlogin.sh
	curl -sc $COOKIE_FILE "http://appengine.google.com/_ah/login?continue=http%3A%2F%2Flocalhost%2F&auth=$(google_clientlogin)" >/dev/null
}

google_rpc () {
	url="${API_URL}$1?app_id=$APP_ID&version=$APP_VER$3"
	response=$(curl -s -w "\\n%{http_code}" -X POST -b $COOKIE_FILE -c $COOKIE_FILE -H "X-Appcfg-Api-Version: 1" -H "Content-Type: application/octet-stream" ${2:-"-d ''"} $url)
	code=$(echo "$response" | sed -n '$p')
	msg=$(echo "$response" | sed '$d')
		echo "$msg" | sed -e 's/.\{50,72\} /&\n/g' -e 's/\.py/.sh/g' 1>&2
		#printf "\nReq:\n%s\nRes:\n%s\n\n" "$(cat .g)" "$response" 1>&2
	if [ $code -ne 200 ]; then
		if [ $1 == "create" ]; then
			google_auth
			google_rpc "create" "--data-binary @$APP_FILE"
		else
			exit 1
		fi
	else
		echo -n "$msg"
	fi
	return $code
}

hash () {
	sha1sum $1 | sed 's,.\{8\},&_,g;s,_ .*,,'
}

mime () {
	ex=${1##*.}
	case $ex in
		css|xml)        m="text/$ex" ;;
		htm|html|shtml) m="text/html" ;;
		txt)            m="text/plain" ;;
		htc)            m="text/x-component" ;;
		manifest)       m="text/cache-manifest" ;;
		gif|png)        m="image/$ex" ;;
		jpeg|jpg)       m="image/jpeg" ;;
		tif|tiff)       m="image/tiff" ;;
		ico)            m="image/x-icon" ;;
		svg)            m="image/svg+xml" ;;
		bmp)            m="image/x-ms-bmp" ;;
		pdf|rtf|zip)    m="application/$ex" ;;
		atom|rss)       m="application/$ex+xml" ;;
		js)             m="application/x-javascript" ;;
		*)              m="application/octet-stream" ;;
	esac
	echo $m
}


find_static_files () {
	(	cd $APP_DIR;
		sed -n 's/static_dir: \(.*\)/\1/p' app.yaml | xargs -I{} find {} -type f;
		sed -n 's/upload: \(.*\)/\1/p' app.yaml | xargs -I{} sh -c 'find -type f | cut -c3- | grep -E "^{}$"'
	)
}


api_clone () {
	e=""
	if [ "$1" == "blob" ]; then
		data=$(cat $STATIC_FILES)
		for line in $data; do
			e="$e\n$line|$(hash "${APP_DIR%/}/$line")|$(mime "$line")"
		done
	else
		data=$((cd $APP_DIR && find -type f | cut -c3-) | cat - $STATIC_FILES $STATIC_FILES | sort | uniq -u)
		for line in $data; do
			e="$e\n$line|$(hash "${APP_DIR%/}/$line")"
		done
	fi
	printf "${e:2}" > $TEMP_FILE
		#printf "\nReq:\n%s\nRes:\n%s\n\n" "$1" "$e" 1>&2

	changes=$(google_rpc "clone$1s" "--data-binary @$TEMP_FILE")
	#printf "\nClone $1 \n%s\n" "$1" "$(cat $TEMP_FILE)" 1>&2

	if [ -n "$changes" ]; then
		for file in $changes; do
			echo "Uploading $1: $file"
			google_rpc "add$1" "--data-binary @${APP_DIR%/}/$file" "&path=$file"
		done
	fi
}


[ -f $COOKIE_FILE ] || google_auth



case $1 in
	update)
		google_rpc "create" "--data-binary @$APP_FILE"

		echo "Cloning files."
		find_static_files > $STATIC_FILES

		api_clone "blob"
		api_clone "file"

		echo "Precompile app."
		google_rpc "precompile"

		echo "Deploying new version."
		google_rpc "deploy"

		echo -n "Checking if new version is ready to serve."
		while [ $(google_rpc "isready") -eq 0 ]; do
			echo -n "."
			sleep 4
		done
		echo " ok"
		
		google_rpc "startserving"
	;;

	rollback|setdefault)
		google_rpc "$1" > /dev/null
	;;

	auth)
		google_auth
	;;

	mime)
		mime "$3"
	;;

	static)
		find_static_files
	;;

	*)
		usage
	;;
esac

echo -e "\nOn $(date) $1 completed."


