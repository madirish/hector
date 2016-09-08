#!/bin/awk

{split($7,ip,"#");print $1 " " $2 " " $3 "|" ip[1] "|" $10}