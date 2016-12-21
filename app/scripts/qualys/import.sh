#!/bin/bash
# Import the Qualys scan
# This file is meant to be run via cron
count=`ls -1 /opt/hector/app/scripts/qualys/*.toimport 2>/dev/ull | wc -l`
if [ $count != 0 ]
then
  for importfile in /opt/hector/app/scripts/qualys/*.toimport; do 
    mv "$importfile" "$importfile.done"
	/usr/bin/php /opt/hector/app/scripts/qualys/import.php "$importfile.done"
  done
fi
