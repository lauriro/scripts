
SSHD_CONFIG=/etc/ssh/sshd_config

test ! -e $SSHD_CONFIG && {
	echo "File $SSHD_CONFIG not found"
	exit 1
}

cat <<EOF
Wanted:

PermitRootLogin no
PasswordAuthentication no

Existing:

EOF

grep 'PasswordAuthentication\|PermitRootLogin' $SSHD_CONFIG


