#!/bin/bash
# ToDo: Allow change to default install path

HECTOR_PATH=${HECTOR_PATH}

clear
echo "******************************************"
echo " HECTOR Security Intelligence Platform "
echo " Installer "
echo "******************************************"
echo "by Justin C. Klein Keane <jukeane@sas.upenn.edu>"
echo 
echo Step 1 of 7 - Checking for prerequisite dependencies...
echo


# Install the prerequisites
if [ -e /etc/redhat-release ]; then
    yum install mysql mysql-server rsyslog rsyslog-mysql httpd php php-cli php-mysql php-xml MySQL-python MySQL-python nmap
    /sbin/chkconfig --level 345 mysqld on 
    /sbin/chkconfig --level 345 httpd on 
    /sbin/chkconfig --level 345 rsyslog on 
    if ! /sbin/service mysqld status | grep running ; then
      /sbin/service mysqld start
    fi
    if ! /sbin/service httpd status | grep running ; then
      /sbin/service httpd start
    fi
    if ! /sbin/service rsyslog status | grep running ; then
      /sbin/service rsyslog start
    fi
fi

echo 
echo "Step 2 of 7 - Configuring rsyslog"
echo 

# Create a new temporary file to perform all our SQL functions
touch /tmp/hector.sql
chmod 0700 /tmp/hector.sql

if [ -d /var/lib/mysql/Syslog ]; then
  IN=`rpm -q rsyslog-mysql`
  IFS='-'
  arr=($IN)
  rsyslogsqldir="/usr/share/doc/${arr[0]}=${arr[1]}=${arr[2]}/createDB.sql"
  IFS=';'
  rsyslogsqldir=${rsyslogsqldir//=/-}
  cp ${rsyslogdir} > /tmp/hector.sql
fi

echo " [+] Setting up the MySQL databases for rsyslog and HECTOR."
echo "     Please choose a password for the hector-rsyslog MySQL user:"
read RSYSLOGPASS
echo "     Please choose a password for the hector MySQL user:"
read HECTORPASS
echo "use mysql; CREATE DATABASE IF NOT EXISTS Syslog; GRANT INSERT ON Syslog.* to 'hector-rsyslog'@localhost identified by '${RSYSLOGPASS}';" >> /tmp/hector.sql
echo "CREATE DATABASE IF NOT EXISTS hector; GRANT ALL PRIVILEGES ON hector to 'hector'@localhost identified by '${HECTORPASS}';" >> /tmp/hector.sql
cat app/sql/db.sql >> /tmp/hector.sql
echo "Please enter your MySQL root user password:"
mysql -u root -p < /tmp/hector.sql



if  ! cat /etc/rsyslog.conf | grep -q "ModLoad ommysql" ; then
  echo " [+] Configuring rsyslog to load MySQL module"
  sed -i "s/MODULES ####/MODULES ####\\n\\n\$ModLoad ommysql/" /etc/rsyslog.conf
fi
if  ! cat /etc/rsyslog.conf | grep -q "iptables" ; then
  echo " [+] Configuring rsyslog to log to MySQL"
  sed -i "s/\/var\/log\/messages/\/var\/log\/messages\\nif \$msg contains 'iptables ' then :ommysql:localhost,Syslog,hector-rsyslog,${RSYSLOGPASS}/" /etc/rsyslog.conf
fi

echo -e "Do you wish to allow rsyslog (UDP 514)? (y/n):"
read configiptables
if [ $configiptables == 'y' ] ; then
  if ! cat /etc/sysconfig/iptables | grep -q "udp \-\-dport 514 \-j ACCEPT" ; then
    sed -i "s/COMMIT/-A INPUT -m state --state NEW -m udp -p udp --dport 514 -j ACCEPT\\nCOMMIT/" /etc/sysconfig/iptables
    echo " [+] Committing firewall updates"
    service iptables restart
  fi  
fi

echo " [+] Committing rsyslog updates"
service rsyslog restart

echo 
echo "Step 4 of 7 - Moving HECTOR files to /opt"
echo 
cp -rf app /opt/
cp -rf html /opt/
echo " [+] Files moved"
echo " [+] Customizing config at ${HECTOR_PATH}/app/conf/config.ini"
cp ${HECTOR_PATH}/app/conf/config.ini.blank ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/\/path\/to\/hector/\/opt\/hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_name\hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_user\hector/g" ${HECTOR_PATH}/app/conf/config.ini
sed -i "s/database_password\${HECTORPASS}/g" ${HECTOR_PATH}/app/conf/config.ini
echo "    Please enter your HECTOR server name or IP:"
read SERVERNAME
sed -i "s/yoursite\/hector_html\${SERVERNAME}\/hector/g" ${HECTOR_PATH}/app/conf/config.ini
echo "    Please enter an e-mail address for contact e-mails:"
read EMAILADDY
sed -i "s/your_email@localhost\${EMAILADDY}/g" ${HECTOR_PATH}/app/conf/config.ini
echo " [+] Config at ${HECTOR_PATH}/app/conf/config.ini complete."

echo 
echo "Step 5 of 7 - Configuring Apache"
echo 
if ! cat /etc/httpd/conf/httpd.conf | grep -q "HECTOR" ; then
  echo " [+] Creating virtual directory /hector at the web root"
  echo >> /etc/httpd/conf/httpd.conf
  echo '#HECTOR configuration' >> /etc/httpd/conf/httpd.conf
  echo 'Alias /hector "${HECTOR_PATH}/html/"' >>  /etc/httpd/conf/httpd.conf
  echo '<Directory "/hector">' >>  /etc/httpd/conf/httpd.conf
  echo '  Options Indexes MultiViews FollowSymLinks' >>  /etc/httpd/conf/httpd.conf
  echo '  AllowOverride None' >>  /etc/httpd/conf/httpd.conf
  echo '  Order allow,deny' >>  /etc/httpd/conf/httpd.conf
  echo '  Allow from all' >>  /etc/httpd/conf/httpd.conf
  echo '</Directory>' >>  /etc/httpd/conf/httpd.conf
  service httpd restart
else
  echo " [+] HECTOR Apache config seems to already exist"
fi  
  
chown -R apache ${HECTOR_PATH}/app/logs

echo 
echo "Step 6 of 7 - Scheduling cron jobs"
echo 
if ! cat /etc/cronttab | grep -q "HECTOR" ; then
  echo "#HECTOR scans" >> /etc/crontab
  echo "01 0 * * * /usr/bin/php ${HECTOR_PATH}/app/scripts/scan_cron.php" >> /etc/crontab
  echo " [+] cron scheduled in /etc/crontab"
else
  echo " [+] HECTOR crontab seems to already exist"
fi

echo 
echo "Step 7 of 7 - Finishing"
echo 
if [ ! -d /var/ossec ] ; then
  echo " [+] OSSEC still needs to be installed"
  tar xvzf ${HECTOR_PATH}/app/software/ossec-hids-2.3.tar.gz
  ${HECTOR_PATH}/app/software/ossec-hids-2.3/install.sh
fi
echo " [+] Scheduling OSSEC monitoring."
cp ${HECTOR_PATH}/app/scripts/hector-ossec-mysql /etc/init.d/
/sbin/chkconfig --add hector-ossec-mysql

