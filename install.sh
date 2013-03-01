#!/bin/bash

HECTOR_PATH=/opt/hector

clear
echo "******************************************"
echo " HECTOR Security Intelligence Platform "
echo " Installer "
echo "******************************************"
echo "by Justin C. Klein Keane <jukeane@sas.upenn.edu>"
echo 
echo Step 1 of X - Checking for prerequisite dependencies...
echo

# Install the prerequisites
if [ -e /etc/redhat-release ]; then
	if rpm -q mysql | grep not ; then
		echo " [+] MySQL is not installed - Installing"
		yum install mysql mysql-server
	fi
	if rpm -q rsyslog | grep not ; then
		echo " [+] Rsyslog is not installed - Installing"
		yum install rsyslog
	fi
	if rpm -q rsyslog-mysql | grep not ; then
		echo " [+] Rsyslog MySQL is not installed - Installing"
		yum install rsyslog-mysql
	fi
	if rpm -q httpd | grep not ; then
		echo " [+] Apache web server is not installed - Installing"
		yum install httpd
	fi
	if rpm -q php | grep not ; then
		echo " [+] PHP is not installed - Installing"
		yum install php
	fi
	if rpm -q php-cli | grep not ; then
		echo " [+] PHP CLI is not installed - Installing"
		yum install php-cli
	fi
	if rpm -q php-mysql | grep not ; then
		echo " [+] PHP MySQL is not installed - Installing"
		yum install php-mysql
	fi
	if rpm -q php-xml | grep not ; then
		echo " [+] PHP XML is not installed - Installing"
		yum install php-xml
	fi
	if rpm -q python | grep not ; then
		echo " [+] Python is not installed - Installing"
		yum install MySQL-python
	fi
	# Install the Python libraries
	if rpm -q MySQL-python | grep not ; then
		echo " [+] Python MySQL library not installed - Installing"
		yum install MySQL-python
	fi
	if rpm -q nmap | grep not ; then
		echo " [+] NMAP is not installed - Installing"
		yum install nmap
	fi
fi

echo 
echo "Step 2 of X - Configuring rsyslog"
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
echo "Step 3 of X - Creating databases for HECTOR"
echo 
echo -e "Please enter a username with permission to CREATE new databases:"
read mysqluser
echo " [+] Running the install script in app/sql/db.sql as ${mysqluser}"
mysql -u ${mysqluser} -p < app/sql/db.sql

echo 
echo "Step 4 of X - Moving HECTOR files to /opt"
echo 
cp -rf app /opt/
cp -rf html /opt/
echo " [+] Files moved"


echo 
echo "Step 5 of X - Configuring Apache"
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
fi 	
	
	







