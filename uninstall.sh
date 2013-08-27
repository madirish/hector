#!/bin/bash
# ToDo: Allow change to default install path

HECTOR_PATH=/opt/hector

clear
echo "******************************************"
echo " HECTOR Security Intelligence Platform "
echo " Conan the Destructor! "
echo "******************************************"
echo "by Justin C. Klein Keane <jukeane@sas.upenn.edu>"
echo 

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root, please use sudo"
   exit 1
fi

/var/ossec/bin/ossec-control stop
rm -rf /var/ossec
rm -f /etc/init.d/ossec
rm -f /etc/ossec-init.conf

# Clean up HECTOR

echo "drop database hector;" >> /tmp/hector-drop
mysql -u root < /tmp/hector-drop
rm /tmp/hector-drop

/etc/init.d/ossec2hector stop

rm -rf /opt/hector
rm -f /etc/init.d/ossec2hector
rm -f /etc/ossec2mysql.conf