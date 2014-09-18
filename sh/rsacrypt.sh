#!/bin/sh
# openssl rsa -in ~/.ssh/id_rsa -pubout -out ~/.ssh/id_rsa.pub.pem
# ln -s ~/rsacrypt.sh /usr/bin/rsacrypt

ENCRYPTED=""
DECRYPTED=""

usage() {
	cat <<EOF

Usage: $0 options

This script encrypt or decrypt.

OPTIONS:
   -e      Encrypt for public key
   -d      Decrypt with private key
   -f      File for symetric encryption
EOF
	exit 1
}

ESC=$(printf "\e")

while getopts ":d:e:f:" opt; do
	[ "$OPTARG$opt" == "d:" ] && opt="d" && OPTARG=~/.ssh/id_rsa
	[ "$OPTARG$opt" == "e:" ] && opt="e" && OPTARG=~/.ssh/id_rsa.pub.pem
	case $opt in
		e)
			if [ -z "$DECRYPTED" ]; then
				echo -e "\nEnter your decrypted content (end with ESC): "
				read -d $ESC DECRYPTED
			fi
			echo -e "\nEncrypted data for ${OPTARG##*/}:\n"
			echo "$DECRYPTED" | openssl rsautl -inkey $OPTARG -pubin -encrypt | openssl base64
		;;
		d)
			if [ -z "$ENCRYPTED" ]; then
				echo -e "\nEnter your encrypted content (end with ESC): "
				read -d $ESC ENCRYPTED
			fi
			DECRYPTED=$(echo "$ENCRYPTED" | openssl base64 -d | openssl rsautl -inkey $OPTARG -decrypt)
			echo -e "\nDecrypted with ${OPTARG##*/}:\n$DECRYPTED"
		;;
		f)
			[ -z "$DECRYPTED" ] && DECRYPTED=$(openssl rand -base64 48)
			DECRYPT=""
			OUT="$OPTARG.asc"
			[ -n "$ENCRYPTED" ] && DECRYPT="-d" && OUT="${OPTARG%.*}.bin"
			openssl aes-256-cbc $DECRYPT -a -salt -pass pass:"$DECRYPTED" -in $OPTARG -out $OUT
	esac
done

[ -z "$ENCRYPTED$DECRYPTED" ] && usage
















#!/bin/sh
# openssl rsa -in ~/.ssh/id_rsa -pubout -out ~/.ssh/id_rsa.pub.pem
# ln -s ~/rsacrypt.sh /usr/bin/rsacrypt

ENCRYPTED=""
DECRYPTED=""
FILE=""

usage() {
	cat <<EOF

Usage: $0 options

This script encrypt or decrypt.

OPTIONS:
   -e      Encrypt for public key
   -d      Decrypt with private key
   -f      File for symetric encryption
EOF
	exit 1
}

getopts ":f:" opt && FILE=$OPTARG && OPTIND=1 && getopts ":e:" opt && DECRYPTED=$(openssl rand -base64 48) && OPTIND=1

while getopts ":d:e:" opt; do
	[ "$OPTARG$opt" == "d:" -o "$OPTARG" == "-f" ] && opt="d" && OPTARG=~/.ssh/id_rsa
	[ "$OPTARG$opt" == "e:" ] && opt="e" && OPTARG=~/.ssh/id_rsa.pub.pem
	case $opt in
		e)
			if [ -z "$DECRYPTED" ]; then
				echo -e "\nEnter your decrypted content (end with ESC): "
				read -d $(echo -e "\e") DECRYPTED
				echo -e "\n"
			fi
			echo -e "\nEncrypted data for ${OPTARG##*/}:\n"
			echo "$DECRYPTED" | openssl rsautl -inkey $OPTARG -pubin -encrypt | openssl base64
		;;
		d)
			if [ -z "$ENCRYPTED" ]; then
				echo -e "\nEnter your encrypted content (end with ESC): "
				read -d $(echo -e "\e") ENCRYPTED
				echo -e "\n"
			fi
			DECRYPTED=$(echo "$ENCRYPTED" | openssl base64 -d | openssl rsautl -inkey $OPTARG -decrypt)
			echo -e "\nDecrypted with ${OPTARG##*/}:\n$DECRYPTED"
		;;
		f)
			[ -z "$DECRYPTED" ] && DECRYPTED=$(openssl rand -base64 48)
			DECRYPT=""
			OUT="$OPTARG.asc"
			[ -n "$ENCRYPTED" ] && DECRYPT="-d" && OUT="${OPTARG%.*}.bin"
			openssl aes-256-cbc $DECRYPT -a -salt -pass pass:"$DECRYPTED" -in $OPTARG -out $OUT
	esac
done

if [ -n "$FILE" ]; then
	echo "File: $FILE"
fi

[ -z "$ENCRYPTED$DECRYPTED" ] && usage

exit



#!/bin/sh

usage() {
	echo "Usage: $0 {encrypt PUBLIK_KEY|decrypt PRIVATE KEY} > OUT_FILE"
	exit 1
}

KEY=$2
encrypt() {
	echo "$1"
#	| openssl rsautl -inkey ${KEY:-"~/.ssh/id_rsa.pub.pem"} -pubin -encrypt | openssl base64

}

case $1 in
    setup)
		# extract the public key in standard form
		openssl rsa -in ${2:-"~/.ssh/id_rsa"} -pubout -out ${3:-"~/.ssh/id_rsa.pub.pem"}
    ;;
    encrypt)
		echo -e "Type your secret content (end with ESC): "
		read -d `echo -e "\e"` DATA
		echo -e "\n"
		encrypt "$DATA"
    ;;
    decrypt)
		echo -e "Paste your encrypted secret (end with ESC): "
		read -d `echo -e "\e"` DATA
		echo -e "\n"
		echo "$DATA" | openssl base64 -d | openssl rsautl -inkey ${2:-"~/.ssh/id_rsa"} -decrypt
    ;;
    *)
        usage
    ;;
esac

exit 1


(openssl rsa -noout -modulus -in .ssh/id_rsa | openssl md5 ; openssl rsa -noout -modulus -in lauri.pub.pem -pubin | openssl md5) | uniq | wc -l


#!/bin/sh

ID=$1

if ! [ -f ${ID:=$(echo ~/.ssh/id_rsa)} ]; then
	echo "key $ID not found"
	exit 1
fi

read -s -p "Enter passphrase for $ID: " PHRASE

#MOD=`openssl rsa -noout -modulus -in .ssh/id_rsa -passin pass:$PHRASE | openssl md5`

echo -e "\nPaste your secret (end with ESC): "
read -d `echo -e "\e"` DATA

echo -e "\n"
echo "$DATA" | openssl base64 -d | openssl rsautl -inkey .ssh/id_rsa -passin pass:$PHRASE -decrypt


#Lauri@Lauri-PC ~
#$ openssl rsautl -inkey public.pem -pubin -encrypt | openssl base64 > data.b64
#tere
#Lauri@Lauri-PC ~
#$ openssl base64 -d < data.b64 | openssl rsautl -inkey .ssh/id_rsa -decrypt
#Enter pass phrase for .ssh/id_rsa:
#tere


# Encryption of the file.
# $ openssl aes-256-cbc -a -salt -in message.txt -out message.txt.aes
# $ openssl enc -aes-256-cbc -a -salt -in message.txt -out message.txt.aes
# Decryption of the file.
# $ openssl aes-256-cbc -d -a -in message.txt.aes -out message.txt
# $ openssl enc -aes-256-cbc -d -a -in message.txt.aes -out message.txt



		echo "#!/bin/sh"
		KEY=`openssl rand -base64 48`
		echo "KEY_`openssl rsa -noout -modulus -in .ssh/id_rsa.pub.pem -pubin | openssl md5`=<<EOF`echo $KEY | openssl rsautl -inkey .ssh/id_rsa.pub.pem -pubin -encrypt | openssl base64`"
		cat <<EOF
ID=\$1

if ! [ -f \${ID:=\$(echo ~/.ssh/id_rsa)} ]; then
	echo "key \$ID not found"
	exit 1
fi

read -s -p "Enter passphrase for $ID: " PHRASE

MOD=`openssl rsa -noout -modulus -in .ssh/id_rsa -passin pass:\$PHRASE | openssl md5`

eval "HASH=\\\$KEY_\$MOD"

if [ -z "\$HASH" ]; then
	echo -e "\nno access"
	exit 1
fi
EOF




#!/bin/sh


IN=""

read_piped() {
	local OUT=""
	while read LINE; do 
		OUT="$OUT$LINE"; 
		 echo -e "Input contains ${#LINE[@]} elements $LINE"
	done
	echo "$OUT"
}



$(readlink /proc/$$/fd/0 | grep -q "^pipe:") && IN=$(read_piped)

while getopts "i:" opt; do IN="$IN\n$(cat $OPTARG)"; done
shift $(($OPTIND - 1))



[ -n "$1" ] && echo "$IN" > $1 || echo "$IN"

#echo "FILE: $1" $IN;

exit 1;

TMP="packer$$.tmp"


usage() {
	echo "Usage: $0 {make|compact|list|latest|restore [date]}"
	exit 1
}


minify() {
	rm -f $TMP;
	while read data; do echo "$data" >> $TMP; done
	
	CONTINUE: /\\\r?\n/g,

}


shrink() {
	rm -f $TMP;
	while read data; do echo "$data" >> $TMP; done

}




case $1 in
    make)
		echo "meik! $TMP"
    ;;
    
    *)
        usage
    ;;
esac





case $1 in
    run )
        if [ ! -z "${GIT_DIR}" ] ; then
            unset GIT_DIR
        fi
        shift
        trap report_error ERR
        run_hook "$@"
        ;;
    --install|--uninstall )
        install_hooks "$1"
        ;;
    -h|--help )
        echo 'Git Hooks'
        echo ''
        echo 'Options:'
        echo '    --install      Replace existing hooks in this repository with a call to'
        echo '                   git hooks run [hook].  Move old hooks directory to hooks.old'
        echo '    --uninstall    Remove existing hooks in this repository and rename hooks.old'
        echo '                   back to hooks'
        echo "    run [cmd]      Run the hooks for cmd (such as pre-commit)"
        echo "    (no arguments) Show currently installed hooks"
        ;;
    * )
        list_hooks
        ;;
esac





exit 0




So, do this instead:

$ openssl passwd -1
Password:
Verifying - Password:
$1$zmUy5lry$aG45DkcaJwM/GNlpBLTDy0
Enter your new password twice; it won't echo. If you have multiple accounts, run the above example multiple times. The output is the cryptographic hash of your password. The hash is randomly salted so that every time it's run, the output will be different, even if the password is the same.

In my example, the password hash is:

$1$zmUy5lry$aG45DkcaJwM/GNlpBLTDy0
Your password hash probably will be completely different, except for the initial $1$.

This password hash can now be e-mailed, faxed, text messaged or even spoken over the phone to a sysadmin to set as your password hash.

After the sysadmin receives your password hash, it can be entered into /etc/shadow manually or with chpasswd. The latter requires a temporary new file, call it newpassword, with your loginid and password hash like this:

LoginidHere:$1$ywrU2ttf$yjm9OXTIBnoKJLQK2Fw5c/
The file can contain multiple lines for other accounts too.

Then, the sysadmin runs this as root:


chpasswd --encrypted < newpassword

Now, the new password is set. It's a good idea to change your password once you log in, unless you use a really strong passphrase. This is because the password hash, once exposed, is subject to off-line brute-force attacks, unless the password is really long.

This method of resetting passwords can be quite secure. For example, using this technique, someone could take the password hash published above, create an account, then tell the person the loginid and hostname and that the password hash above was used. Only the person who originally created the password hash could know the password, even though the password hash was published in a magazine.

By the way, the password for that hash is 28 completely random base64 characters long, so it should be extremely difficult to crack. But please, don't create any accounts with that hash because the password is also published, it's:

HXzNnCTo8k44k8v7iz4ZkR/QWkM2
That password and hash were created like this:

$ openssl rand -base64 37 | cut -c1-37
$ openssl rand 21 -base64
HXzNnCTo8k44k8v7iz4ZkR/QWkM2
$ openssl passwd -1 HXzNnCTo8k44k8v7iz4ZkR/QWkM2
These examples use the MD5 password hashes now common on Linux systems. If you need to use the old UNIX password hash, simply leave off the -1. For example:

$ openssl passwd
Password:
Verifying - Password:
xcx7DofWC0LpQ
The password for this last password hash example is: TheLinux.

Crypto Benchmarking
The many algorithms that OpenSSL supports makes it well suited for cryptographic benchmarking. This is useful for comparing the relative performance of different cryptographic algorithms and different hardware architectures using a consistent code base. And, it has a built-in benchmarking command.

The openssl speed command, by default, runs through every single algorithm in every single supported mode and option, with several different sizes of data. The different sizes are important because of algorithm start-up overhead.

A complete speed run takes about six minutes, regardless of hardware performance, and produces 124 lines of performance data with 29 lines of summary.

