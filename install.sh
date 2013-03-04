#!/bin/bash

HECTOR_PATH=/opt/hector

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

if [ ! -d /var/lib/mysql/Syslog ]; then
  IN=`rpm -q rsyslog-mysql`
  IFS='-'
  arr=($IN)
  rsyslogsqldir="/usr/share/doc/${arr[0]}=${arr[1]}=${arr[2]}/createDB.sql"
  IFS=';'
  rsyslogsqldir=${rsyslogsqldir//=/-}
  echo "Enter your MySQL root password:"
  mysql -u root -p < ${rsyslogsqldir}
fi

if  ! cat /etc/rsyslog.conf | grep -q "ModLoad ommysql" ; then
  echo " [+] Configuring rsyslog to load MySQL module"
  sed -i "s/MODULES ####/MODULES ####\\n\\n\$ModLoad ommysql/" /etc/rsyslog.conf
fi
if  ! cat /etc/rsyslog.conf | grep -q "iptables" ; then
  echo " [+] Configuring rsyslog to log to MySQL"
  echo -e "Please enter a username with permission to INSERT on the Syslog tables:"
  read mysqluser
  echo -e "Please enter the user's password:"
  read mysqlpass
  sed -i "s/\/var\/log\/messages/\/var\/log\/messages\\nif \$msg contains 'iptables ' then :ommysql:localhost,Syslog,${mysqluser},${mysqlpass}/" /etc/rsyslog.conf
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
echo "Step 3 of 7 - Creating databases for HECTOR"
echo 
echo -e "Please enter a username with permission to CREATE new databases:"
read mysqluser
echo " [+] Running the install script in app/sql/db.sql as ${mysqluser}"
mysql -u ${mysqluser} -p < app/sql/db.sql

echo 
echo "Step 4 of 7 - Moving HECTOR files to /opt"
echo 
cp -rf app /opt/
cp -rf html /opt/
echo " [+] Files moved"


echo 
echo "Step 5 of 7 - Configuring Apache"
echo 
if ! cat /etc/httpd/conf/httpd.conf | grep -q "HECTOR" ; then
  echo " [+] Creating virtual directory /hector at the web root"
  echo >> /etc/httpd/conf/httpd.conf
  echo '#HECTOR configuration' >> /etc/httpd/conf/httpd.conf
  echo 'Alias /hector "/opt/hector/html/"' >>  /etc/httpd/conf/httpd.conf
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
  
chown -R apache /opt/hector/app/logs

echo 
echo "Step 6 of 7 - Scheduling cron jobs"
echo 
if ! cat /etc/cronttab | grep -q "HECTOR" ; then
  echo "#HECTOR scans" >> /etc/crontab
  echo "01 0 * * * /usr/bin/php /opt/hector/app/scripts/scan_cron.php" >> /etc/crontab
  echo " [+] cron scheduled in /etc/crontab"
else
  echo " [+] HECTOR crontab seems to already exist"
fi

echo 
echo "Step 7 of 7 - Finishing"
echo 
if [ ! -d /var/ossec ] ; then
  echo " [+] OSSEC still needs to be installed"
  tar xvzf /opt/hector/app/software/ossec-hids-2.3.tar.gz
  /opt/hector/app/software/ossec-hids-2.3/install.sh
fi
echo " [+] Scheduling OSSEC monitoring."
cp /opt/hector/app/scripts/hector-ossec-mysql /etc/init.d/
/sbin/chkconfig --add hector-ossec-mysql

