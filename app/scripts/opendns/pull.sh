#!/bin/bash
#
# @author Josh Bauer (bauerj@mlhs.org)
# 
# This script is used to pull opendns logs from the Amazon s3 bucket
#

dest="/opt/hector/app/scripts/opendns/logs"
date="" #yyyy-mm-dd

while getopts "d:" opt; do
	case "$opt" in
	d) date=$OPTARG
		;;
	esac
done

if [ date == "" ] 
	then 
		echo "usage opendns-blocked -d [date]";
		echo "  [date] format is \"YYYY-MM-DD\"";
		exit 0;
fi

if [ ! -d "$dest" ] 
	then
        	mkdir "$dest";
fi

aws s3 cp --recursive s3://opendns-1939393/dnslogs/$date/ $dest/$date
