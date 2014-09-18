# Prepare .ssh folder
mkdir ~/.ssh
chmod 700 ~/.ssh

touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

cat > ~/.ssh/config <<EOF
Host example example.wall
  IdentityFile ~/.ssh/alternative_id_rsa
  HostName 192.168.4.157
  User root
	UserKnownHostsFile /dev/null
	StrictHostKeyChecking no
  ForwardX11 yes
  # MasterControl no
  # LocalForward 9906 127.0.0.1:3306
  #LocalForward 192.168.4.110:9418 127.0.0.1:9418

Host *.wall
  ProxyCommand ssh lauri@firewall.ee nc %h %p
	#ProxyCommand ssh lauri@firewall.ee /bin/netcat -w 1 $(echo %h | cut -d%% -f1) 22
	Port 220
  Compression yes
  CompressionLevel 8

Host *
  User lauri
  ForwardAgent yes
  # Multiplexing
  ControlMaster auto
  ControlPath ~/.ssh/pid/%r_%h_%p
  TCPKeepAlive yes
  #   Compression yes
  #   CompressionLevel 7
  #   Cipher blowfish
  #   ServerAliveInterval 600
  #   ControlMaster auto
  #   ControlPath /tmp/ssh-%r@%h:%p
EOF

chmod 600 ~/.ssh/config
touch ~/.ssh/known_hosts
chmod 600 ~/.ssh/known_hosts

# for ssh connections multiplexing
mkdir ~/.ssh/connections
chmod 700 ~/.ssh/connections

# Generating SSH keys
test -f ~/.ssh/id_rsa || {
	ssh-keygen -t rsa -b 2048 -P "" -f ~/.ssh/id_rsa
	cat ~/.ssh/id_rsa.pub
}

