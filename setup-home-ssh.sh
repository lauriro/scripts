# Prepare .ssh folder
mkdir ~/.ssh
chmod 700 ~/.ssh

touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
touch ~/.ssh/config
chmod 600 ~/.ssh/config
touch ~/.ssh/known_hosts
chmod 600 ~/.ssh/known_hosts

# Generating SSH keys
ssh-keygen -t rsa -b 2048 -f ~/.ssh/id_rsa
cat ~/.ssh/id_rsa.pub

# for ssh connections multiplexing
mkdir ~/.ssh/connections
chmod 700 ~/.ssh/connections

