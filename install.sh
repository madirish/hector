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
umask 077
touch /tmp/hector.sql
cat app/software/kojoney2/create_tables.sql > /tmp/hector.sql

echo " [+] Setting up the MySQL databases for HECTOR."
echo "     Please choose a password for the hector MySQL user:"
read HECTORPASS

echo "use mysql; " >> /tmp/hector.sql
echo "CREATE DATABASE IF NOT EXISTS hector; GRANT ALL PRIVILEGES ON hector.* to 'hector'@localhost identified by '${HECTORPASS}';" >> /tmp/hector.sql
cat app/sql/db.sql >> /tmp/hector.sql


echo 
echo "Step 3 of 7 - Moving HECTOR files to /opt"
echo 

umask 022

if [ ! -d $HECTOR_PATH ] ; then
  mkdir $HECTOR_PATH
fi
cp -rf app $HECTOR_PATH
cp -rf html $HECTOR_PATH
if [ ! -d $HECTOR_PATH/app/screenshots ] ; then
  mkdir ${HECTOR_PATH}/app/screenshots
fi
chmod -R 0755 $HECTOR_PATH

echo " [+] Files moved"
echo " [+] Customizing config at $HECTOR_PATH/app/conf/config.ini"
cp ${HECTOR_PATH}/app/conf/config.ini.blank ${HECTOR_PATH}/app/conf/config.ini
chgrp apache ${HECTOR_PATH}/app/conf/config.ini
chmod g+r ${HECTOR_PATH}/app/conf/config.ini
sed -i "s|/path/to/hector|${HECTOR_PATH}/app|g" $HECTOR_PATH/app/conf/config.ini

echo " [+] Setting database parameters in $HECTOR_PATH/app/conf/config.ini"
sed -i "s/database_name/hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_user/hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_password/${HECTORPASS}/g" ${HECTOR_PATH}/app/conf/config.ini


sed -i "s|approot = /opt/hector/app|approot = /opt/hector|g" ${HECTOR_PATH}/app/conf/config.ini

echo " [+] Customizing config at /etc/ossec2mysql.conf"
cp ${HECTOR_PATH}/app/scripts/ossec2mysql.conf.blank /etc/ossec2mysql.conf
sed -i "s/hectorpass/${HECTORPASS}/g" /etc/ossec2mysql.conf

echo 
echo "Step 4 of 7 - Operational configuration info"
echo 

echo "Please enter your MySQL root user password:"
mysql -u root -p < /tmp/hector.sql

echo "    Please enter your HECTOR server name or IP:"
read SERVERNAME
sed -i "s/yoursite\/hector_html/${SERVERNAME}\/hector/g" ${HECTOR_PATH}/app/conf/config.ini
echo "    Please enter an e-mail address for contact e-mails:"
read EMAILADDY
sed -i "s/your_email@localhost/${EMAILADDY}/g" ${HECTOR_PATH}/app/conf/config.ini
echo " [+] Config at ${HECTOR_PATH}/app/conf/config.ini complete."

touch $HECTOR_PATH/app/logs/error_log
touch $HECTOR_PATH/app/logs/message_log
chmod 0700 $HECTOR_PATH/app/logs/*_log
chown -R apache $HECTOR_PATH/app/logs

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
  # Check if iptables exists
  if [ ! -e /etc/sysconfig/iptables ] ; then
    echo '# Firewall configuration written by system-config-firewall' > /etc/sysconfig/iptables
    echo '# Copied from CentOS 5.5 by HECTOR' >> /etc/sysconfig/iptables
    echo '# Manual customization of this file is not recommended.' >> /etc/sysconfig/iptables
    echo '*filter' >> /etc/sysconfig/iptables
    echo ':INPUT ACCEPT [0:0]' >> /etc/sysconfig/iptables
    echo ':FORWARD ACCEPT [0:0]' >> /etc/sysconfig/iptables
    echo ':OUTPUT ACCEPT [0:0]' >> /etc/sysconfig/iptables
    echo '-A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT' >> /etc/sysconfig/iptables
    echo '-A INPUT -p icmp -j ACCEPT' >> /etc/sysconfig/iptables
    echo '-A INPUT -i lo -j ACCEPT' >> /etc/sysconfig/iptables
    echo '-A INPUT -m state --state NEW -m tcp -p tcp --dport 22 -j ACCEPT' >> /etc/sysconfig/iptables
    echo '-A INPUT -m state --state NEW -m udp -p udp --dport 1514 -j ACCEPT' >> /etc/sysconfig/iptables
    echo '-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT' >> /etc/sysconfig/iptables
    echo '-A INPUT -j REJECT --reject-with icmp-host-prohibited' >> /etc/sysconfig/iptables
    echo '-A FORWARD -j REJECT --reject-with icmp-host-prohibited' >> /etc/sysconfig/iptables
    echo 'COMMIT' >> /etc/sysconfig/iptables
  fi
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
  echo "01 0 * * * root /usr/bin/php $HECTOR_PATH/app/scripts/scan_cron.php" >> /etc/crontab
  # echo "* * * * * root /opt/hector/app/scripts/hector-ossec-mysql-monitor.sh" >> /etc/crontab
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
  tar xvzf $HECTOR_PATH/app/software/ossec-hids-2.7.tar.gz --directory=$HECTOR_PATH/app/software/
  ${HECTOR_PATH}/app/software/ossec-hids-2.7/install.sh
fi

echo " [+] Adding OSSEC decoder so it can monitor darknet sensors."
if grep -Fxq "HECTOR" /var/ossec/etc/decoder.xml ; then
  echo "     Looks like the HECTOR decoders are already there..."
else
  cat app/software/ossec-hector-decoder.xml >> /var/ossec/etc/decoder.xml
fi
echo " [+] Adding OSSEC local_rules for kojoney2 and darknet sensors."
if grep -Fxq "HECTOR" /var/ossec/rules/local_rules.xml ; then
  echo "     Looks like the rules are already there..."
else
  cat app/software/ossec-hector-rules.xml >> /var/ossec/rules/local_rules.xml
fi


echo " [+] Scheduling OSSEC monitoring services."
mv ${HECTOR_PATH}/app/scripts/ossec2hector /etc/init.d/
chmod +x /etc/init.d/ossec2hector
/sbin/chkconfig --add ossec2hector
/sbin/chkconfig --level 345 ossec2hector
/sbin/service ossec restart
/sbin/service ossec2hector start

echo -e "Do you wish to allow remote OSSEC (UDP 1514)? (y/n):"
read configiptables
if [ $configiptables == "y" ] ; then
  if ! cat /etc/sysconfig/iptables | grep -q "udp \-\-dport 1514 \-j ACCEPT" ; then
    sed -i "s/--dport 22 -j ACCEPT/--dport 22 -j ACCEPT\\n-A INPUT -m state --state NEW -m udp -p udp --dport 1514 -j ACCEPT/" /etc/sysconfig/iptables
    echo " [+] Committing firewall updates"
    service iptables restart
  fi  
fi

echo " [+] SELinux "
if [ -e /selinux/enforce ]; then
  echo "      It looks like SELinux is enabled.  I'm going to disable it temporarily."
  echo "      You should make this change permanent by editing /etc/selinux/conf "
  echo 0 > /selinux/enforce
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
