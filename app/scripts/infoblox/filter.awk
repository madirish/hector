#!/bin/awk

{split($7,ip,"#");print ip[1]}