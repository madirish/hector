#!/bin/bash
# ToDo: Allow change to default install path

HECTOR_PATH=/opt/hector

clear
echo "******************************************"
echo " HECTOR Security Intelligence Platform "
echo " Installer "
echo "******************************************"
echo "by Justin C. Klein Keane <jukeane@sas.upenn.edu>"
echo 

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root, please use sudo"
   exit 1
fi

echo Step 1 of 7 - Checking for prerequisite dependencies...
echo


# Install the prerequisites
if [ -e /etc/redhat-release ]; then
    yum install mysql mysql-server git httpd php php-cli php-mysql php-xml MySQL-python MySQL-python nmap gcc make
    /sbin/chkconfig --level 345 mysqld on 
    /sbin/chkconfig --level 345 httpd on 
    if ! /sbin/service mysqld status | grep running ; then
      /sbin/service mysqld start
    fi
    if ! /sbin/service httpd status | grep running ; then
      /sbin/service httpd start
    fi
fi

echo " [+] Pulling down Kojoney2 sources."
# Pull in Kojoney2 for the database components
git clone git://github.com/madirish/kojoney2 app/software/kojoney2

echo 
echo "Step 2 of 7 - Configuring MySQL"
echo 

# Create a new temporary file to perform all our SQL functions
touch /tmp/hector.sql
chmod 0700 /tmp/hector.sql
cat app/software/kojoney2/create_tables.sql >> /tmp/hector.sql

echo " [+] Setting up the MySQL databases for HECTOR."
echo "     Please choose a password for the hector MySQL user:"
read HECTORPASS

echo "use mysql; " >> /tmp/hector.sql
echo "CREATE DATABASE IF NOT EXISTS hector; GRANT ALL PRIVILEGES ON hector.* to 'hector'@localhost identified by '${HECTORPASS}';" >> /tmp/hector.sql
cat app/sql/db.sql >> /tmp/hector.sql

echo "Please enter your MySQL root user password:"
mysql -u root -p < /tmp/hector.sql

echo 
echo "Step 4 of 7 - Moving HECTOR files to /opt"
echo 
if [ ! -d $HECTOR_PATH ] ; then
  mkdir $HECTOR_PATH
fi
cp -rf app $HECTOR_PATH
cp -rf html $HECTOR_PATH
echo " [+] Files moved"
echo " [+] Customizing config at $HECTOR_PATH/app/conf/config.ini"
cp ${HECTOR_PATH}/app/conf/config.ini.blank ${HECTOR_PATH}/app/conf/config.ini

sed -i "s|/path/to/hector|${HECTOR_PATH}/app|g" $HECTOR_PATH/app/conf/config.ini

echo " [+] Setting database parameters in $HECTOR_PATH/app/conf/config.ini"
sed -i "s/database_name/hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_user/hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_password/${HECTORPASS}/g" ${HECTOR_PATH}/app/conf/config.ini


sed -i "s|approot = /opt/hector/app|approot = /opt/hector|g" ${HECTOR_PATH}/app/conf/config.ini

echo "    Please enter your HECTOR server name or IP:"
read SERVERNAME
sed -i "s/yoursite\/hector_html/${SERVERNAME}\/hector/g" ${HECTOR_PATH}/app/conf/config.ini
echo "    Please enter an e-mail address for contact e-mails:"
read EMAILADDY
sed -i "s/your_email@localhost/${EMAILADDY}/g" ${HECTOR_PATH}/app/conf/config.ini
echo " [+] Config at ${HECTOR_PATH}/app/conf/config.ini complete."

echo 
echo "Step 5 of 7 - Configuring Apache"
echo 
if ! cat /etc/httpd/conf/httpd.conf | grep -q "HECTOR" ; then
  echo " [+] Creating virtual directory /hector at the web root"
  echo >> /etc/httpd/conf/httpd.conf
  echo '#HECTOR configuration' >> /etc/httpd/conf/httpd.conf
  echo "Alias /hector \"${HECTOR_PATH}/html/\"" >>  /etc/httpd/conf/httpd.conf
  echo '<Directory "/hector">' >>  /etc/httpd/conf/httpd.conf
  echo '  Options Indexes MultiViews FollowSymLinks' >>  /etc/httpd/conf/httpd.conf
  echo '  AllowOverride None' >>  /etc/httpd/conf/httpd.conf
  echo '  Order allow,deny' >>  /etc/httpd/conf/httpd.conf
  echo '  Allow from all' >>  /etc/httpd/conf/httpd.conf
  echo '</Directory>' >>  /etc/httpd/conf/httpd.conf
  if ! cat /etc/sysconfig/iptables | grep -q "tcp \-\-dport 80 \-j ACCEPT" ; then
    echo " [+] Modifyign iptables to allow port 80"
    sed -i "s/--dport 22 -j ACCEPT/--dport 22 -j ACCEPT\\n-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT/" /etc/sysconfig/iptables
    echo " [+] Committing firewall updates"
    service iptables restart
  fi
  service httpd restart
else
  echo " [+] HECTOR Apache config seems to already exist"
fi  
  
chown -R apache $HECTOR_PATH/app/logs

echo 
echo "Step 6 of 7 - Scheduling cron jobs"
echo 
if ! cat /etc/crontab | grep -q "HECTOR" ; then
  echo "#HECTOR scans" >> /etc/crontab
  echo "01 0 * * * /usr/bin/php $HECTOR_PATH/app/scripts/scan_cron.php" >> /etc/crontab
  echo "#HECTOR OSSEC monitor restart (for log rotates)" >> /etc/crontab
  echo "#01 0 * * * service hector-ossec-mysql restart" >> /etc/crontab
  echo " [+] cron scheduled in /etc/crontab"
else
  echo " [+] HECTOR crontab seems to already exist"
fi

echo 
echo "Step 7 of 7 - Finishing"
echo 
if [ ! -d /var/ossec ] ; then
  echo " [+] OSSEC still needs to be installed"
  echo "     Press [Enter] to begin the OSSEC install process"
  read foo
  tar xvzf $HECTOR_PATH/app/software/ossec-hids-2.3.tar.gz --directory=$HECTOR_PATH/app/software/
  ${HECTOR_PATH}/app/software/ossec-hids-2.3/install.sh
fi

echo " [+] Adding OSSEC local_rule so it can monitor darknet sensors."
echo "<group name=\"local,syslog,\">" >> /var/ossec/rules/local_rules.xml
echo "  <rule id=\"100001\" level=\"3\">" >> /var/ossec/rules/local_rules.xml
echo "    <if_sid>4100</if_sid>" >> /var/ossec/rules/local_rules.xml
echo "    <match>iptables</match>" >> /var/ossec/rules/local_rules.xml
echo "    <description>Darknet sensor detection for HECTOR.</description>\n" >> /var/ossec/rules/local_rules.xml
echo "  </rule>" >> /var/ossec/rules/local_rules.xml
echo "</group>" >> /var/ossec/rules/local_rules.xml

echo " [+] Scheduling OSSEC monitoring services."
sed -i "s/USERNAME = 'root'/USERNAME = 'hector'/g" ${HECTOR_PATH}/app/scripts/hector-ossec-mysql.py
sed -i "s/PASSWORD = ''/PASSWORD = '${HECTORPASS}'/g" ${HECTOR_PATH}/app/scripts/hector-ossec-mysql.py
cp ${HECTOR_PATH}/app/scripts/hector-ossec-mysql /etc/init.d/
chmod +x /etc/init.d/hector-ossec-mysql
/sbin/chkconfig --add hector-ossec-mysql
/sbin/chkconfig --level 345 hector-ossec-mysql on
/sbin/service ossec restart
/sbin/service hector-ossec-mysql start

echo -e "Do you wish to allow remote OSSEC (UDP 1514)? (y/n):"
read configiptables
if [ $configiptables == 'y' ] ; then
  if ! cat /etc/sysconfig/iptables | grep -q "udp \-\-dport 1514 \-j ACCEPT" ; then
    sed -i "s/--dport 22 -j ACCEPT/--dport 22 -j ACCEPT\\n-A INPUT -m state --state NEW -m udp -p udp --dport 1514 -j ACCEPT/" /etc/sysconfig/iptables
    echo " [+] Committing firewall updates"
    service iptables restart
  fi  
fi
echo 
echo "Congratulations, installation complete!"
echo "Note that http access is GLOBALLY available."
echo "Update your iptables rules if you wish to "
echo "restrict access."
echo
echo "You can log in to the admin panel at:"
echo "http://$SERVERNAME/hector"
echo "as administrator:password (which you should change)"
