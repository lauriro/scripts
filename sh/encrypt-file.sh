#!/bin/sh

if [ "x$1" = x-keys ] ; then

    # Generated keys should not be world-readable
    umask 077

    PUBLICKEY="$2"
    PRIVATEKEY="$3"
    if [ x"$PRIVATEKEY" = x ] ; then
        echo "Usage: $0 [public-key] [private-key]"
        exit 1
    fi

    # Check if files exist already
    for f in "$PUBLICKEY" "$PRIVATEKEY" ; do
        if [ -e "$f" ] ; then
            echo -n "File $f exists! Overwrite? (y/n) "
            [ `head -n 1` = 'y' ] || exit 1
        fi
    done

    # Generate the keys.
    echo "Generating 2048-bit keys..."
    openssl genrsa -out "$PRIVATEKEY" -des3 2048
    echo "Creating public key..."
    openssl rsa -in "$PRIVATEKEY" -pubout -out "$PUBLICKEY"
    echo "Done."

    exit
fi

#
# Main script, for archiving and encrypting files.
#
# Read the command-line arguments.
#
ARCHIVEDIR="$1"
PUBLICKEY="$2"

if ! [ -e "$ARCHIVEDIR" -a -f "$PUBLICKEY" ] ; then
    echo "Usage: $0 [directory] [public-key] > [decryption-script]"
    echo "   or: $0 -keys [public-key] [private-key]"
    exit 1
fi

# Generate the random symmetric-key password.
PASSIZE=30
if [ -c /dev/urandom ] ; then
    KEY=`head -c 30 /dev/urandom | openssl enc -base64`
else
    KEY=`openssl rand -base64 30`
fi
export KEY

# Echo the script that will decrypt the data contained at the end of the script.
cat <<EOF
#!/bin/sh
PRIVATEKEY="\$1"

if ! [ -f "\$PRIVATEKEY" ] ; then
    echo "Usage: \$0 [private-key] > [decrypted-archive]"
    exit 1
fi

# Decrypt the symmetric key using the private key.
AESKEY=\` \\
        awk '/^#BKEY/{prn=1;next} /^#EKEY/{exit} prn==1{print}' "\$0" | \\
        openssl enc -base64 -d | \\
	openssl rsautl -decrypt -inkey "\$PRIVATEKEY" \`
export AESKEY

# Using the symmetric key, decrypt the data.
awk '/^#BARC/{prn=1;next} /^#EARC/{exit} prn==1{print}' "\$0" | \\
        openssl enc -aes-256-cbc -d -a -pass env:AESKEY

exit

#BKEY
EOF

# Encode the symmetric key using the public key.
openssl rsautl -encrypt -inkey "$PUBLICKEY" -pubin <<EOF | openssl enc -base64
$KEY
EOF

echo '#EKEY'
echo '#BARC'

# Create and encode the archive file.
cd `dirname "$ARCHIVEDIR"`
tar cz `basename "$ARCHIVEDIR"` | openssl enc -aes-256-cbc -pass env:KEY -a

echo '#EARC'

