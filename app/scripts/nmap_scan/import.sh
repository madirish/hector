#!/bin/bash
# Import the NMAP scan XML file
# This file is meant to be run via cron
count=`ls -1 /opt/hector/app/scripts/nmap_scan/*.toimport 2>/dev/ull | wc -l`
if [ $count != 0 ]
then
  for importfile in /opt/hector/app/scripts/nmap_scan/*.toimport; do 
	/usr/bin/php /opt/hector/app/scripts/nmap_scan/nmap_scan_loadfile.php "$importfile"
    mv "$importfile" "$importfile.done"
  done
fi
