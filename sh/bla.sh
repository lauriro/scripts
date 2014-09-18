#!/bin/sh
# $ cat bla.txt | sed -n '/ed0e0c4e1bd39570d232fadccf72431e/{:l;n;/./{p;bl}}'
# $ cat bla.txt | sed -n '/^$/,$h;${x;p}'

ID=$1
if ! [ -f ${ID:=$(echo ~/.ssh/id_rsa)} ]; then
	echo "key $ID not found"
	exit 1
fi

KEY_756672750189697216cd38e3139fee5c='NIO9VWPplvximpYN6rkSX+wHTB5IZ+dU00esEAHBzEASQf5phaa4GIiUhrnptkIC
fJX8u3OAKbqWL+0wKCz5jps/1Jq0FlbH+s6Xd/4gO7yk1Ws19CL4+snPBuJgvcVF
VX/D9tyc0n3OO9ia4LfE3GEvCiveIrjmoK4DN0IuF4Q8y/8jB1/sYb8XIzd4i9OU
lXYZ5Ru9k0xorSQ6AABHhxSna1SKU5RBk5ZDGCxdJo/4w5dycsyVo6SdWiICWoyv
n5NUz4QUiSB9vovNmEKFXtTjaAe53BSfNi+fGdHHopPaKi1vL4UHeHuysfQsKPGs
cH+l+x7boYsv7ESyGoBDvw=='
KEY_ed0e0c4e1bd39570d232fadccf72431e='02OZpx6kXAoCpcDNtaeFXvJyWEiFQhn9EpDyXoo6XuMSMHL+H/ngJ7eHsQ6GQRN5
z8Usx2bgQvyztmCNndHr8zvxCflYMwylfES/xNVbXfH2/K7LOr8A0HwuMaViA0xj
QCHK8nD2bwy52TFcG3IDyOvrQa1k7agNX5uHLZ8qgKWo0kHlonQfvZGTdDpe+C4v
1ljrVE6VSEc1YzmNh3hjd+8F8BuI7i/G35/3MXUvgENTkN3SEjWzb2cap9Xbz5kA
TJz+O+fch4+uX9Ou/JKIZc2V2h4L6B33KseNFlrnPrBwgjUJgpl3IMQLqGVd+E7m
eTjSH+BZWl7XHbBUvuLpVg=='
DATA='U2FsdGVkX18m9AdGUzfN8AamA/r1nQzrWE8QP+Sxv8g='

read -s -p "Enter passphrase for $ID: " PHRASE
echo " "
MOD=$(openssl rsa -noout -modulus -in $ID -passin pass:$PHRASE | openssl md5)

eval "HASH=\$KEY_$MOD"

if [ -z "$HASH" ]; then
	echo "No access for mod $MOD"
	exit 1
fi

KEY=$(echo "$HASH" | openssl base64 -d | openssl rsautl -inkey $ID -passin pass:$PHRASE -decrypt)
echo "$DATA" | openssl aes-256-cbc -d -a -pass pass:$KEY
