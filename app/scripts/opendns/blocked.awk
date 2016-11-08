#!/bin/awk
#
#
BEGIN {FS="\""; OFS="|";} 
{if($12=="Blocked") print substr($18,1,length($18)-1)"|"$20}
{}
