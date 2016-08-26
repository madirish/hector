#!/bin/bash
#
# @author Josh Bauer (bauerj@mlhs.org)
# 
# This script is used to unzip opendnlogs and extract the Blocked Malware lookups.
#

src="/opt/hector/app/scripts/opendns/logs"
dst="/opt/hector/app/scripts/opendns"
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

gzip -dc $src/$date/*.csv.gz | grep -e Malware | awk 'BEGIN{FS="\"";OFS="|";}{if($12=="Blocked") print substr($18,1,length($18)-1)}' | sort | uniq > $dst/blocked-domains-$date.txt
