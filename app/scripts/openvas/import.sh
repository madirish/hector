#!/bin/bash
count=`ls -1 /opt/hector/app/scripts/openvas/*.toimport 2>/dev/ull | wc -l`
if [ $count != 0 ]
then
  for importfile in /opt/hector/app/scripts/openvas/*.toimport; do 
    php /opt/hector/app/scripts/openvas/import.php "$importfile"
    mv "$importfile" "$importfile.done"
  done
fi
