#!/bin/sh


google_clientlogin() {
	local LOGIN_URL="https://www.google.com/accounts/ClientLogin"

	printf "User: "; local u; read -r u
	printf "Pass: "; local p; read -rs p; echo ""

	local tok=$(curl -ks -d Email=$u -d Passwd=$p -d accountType=GOOGLE -d source=curl -d service=${1-ah} $LOGIN_URL | grep 'Auth=')

	test -z "$tok" && {
		echo "Login FAIL"
		exit 1
	}
	echo ${tok#*=}
}

# A "service name" is a brief string that the ClientLogin authentication system uses to identify a Google service.
# 
# cl - Google Calendar
# ah - 
# analytics - Google Analytics Data APIs	
# apps - Google Apps APIs (Domain Information & Management)	
# jotspot - Google Sites Data API	
# blogger - Blogger Data API	
# print - Book Search Data API	
# cl - Calendar Data API	
# codesearch - Google Code Search Data API	
# cp - Contacts Data API	
# structuredcontent - Content API for Shopping	
# writely - Documents List Data API	
# finance - Finance Data API	
# mail - Gmail Atom feed	
# health - Health Data API	
# weaver (H9 sandbox)
# local - Maps Data APIs	
# lh2 - Picasa Web Albums Data API	
# annotateweb - Sidewiki Data API	
# wise - Spreadsheets Data API	
# sitemaps - Webmaster Tools API	
# youtube - YouTube Data API	

