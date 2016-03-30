#!/bin/bash
for importfile in *.toimport; do 
  php /opt/hector/app/scripts/openvas/import.php "$importfile"
  mv "$importfile" "$importfile.done"
done