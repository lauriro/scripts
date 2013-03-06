
# Run shell script on a remote machine
ssh user@host 'sh -s' < local_script.sh

ssh user@host 'echo "rootpass" | sudo -Sv && bash -s' < local_script.sh


