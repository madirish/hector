#!/bin/bash

from_dt=$(date --date "yesterday" +%Y-%m-%d)" 00:00:00"
to_dt=$(date --date "yesterday" +%Y-%m-%d)" 23:59:59"
conf_file_path=$(dirname "$BASH_SOURCE")"/../../conf/config.ini"

read -d "\n" db_host db db_user db_pass email <<< $(awk -F'=' '\
{key=$1;value=$2;gsub(/^[ \t]+/,"",key);gsub(/[ \t]+$/,"",key);gsub(/^[ \t]+/,"",value);gsub(/[ \t]+$/,"",value);\
if(key=="db_host"){db_host=value};if(key=="db_user"){db_user=value};if(key=="db_pass"){db_pass=value};if(key=="db"){db=value};if(key=="site_email"){email=value}}\
END {OFS="\n"; print db_host, db, db_user, db_pass, email}' "$conf_file_path")


domain_sql="select * from (select d.domain_name, count(nr.named_resolution_datetime) as records from named_resolution nr, domain d \
where d.domain_is_malicious>0 and d.domain_id = nr.domain_id and nr.named_resolution_datetime>='$from_dt' and nr.named_resolution_datetime<='$to_dt' \
group by d.domain_name order by d.domain_name) as malware_domains order by records desc, domain_name asc"

ip_sql="select * from ( \
select nr.named_resolution_src_ip, nr.named_resolution_src_ip_numeric, count(nr.named_resolution_datetime) as records from named_resolution nr, domain d \
where d.domain_is_malicious>0 and d.domain_id = nr.domain_id and nr.named_resolution_datetime>='$from_dt' and nr.named_resolution_datetime<='$to_dt' \
group by nr.named_resolution_src_ip, nr.named_resolution_src_ip_numeric order by nr.named_resolution_src_ip, nr.named_resolution_src_ip) \
as malware_ips order by records desc, named_resolution_src_ip_numeric asc"

records_sql="select nr.named_resolution_src_ip, named_resolution_src_ip_numeric, named_resolution_datetime, d.domain_name, d.domain_is_malicious \
from named_resolution nr, domain d where d.domain_is_malicious>0 and d.domain_id = nr.domain_id and nr.named_resolution_datetime>='$from_dt' and nr.named_resolution_datetime<='$to_dt' \
order by nr.named_resolution_src_ip_numeric, nr.named_resolution_datetime"

output="\n---------------Domains------------------\n"
output+=$(mysql -u "$db_user" -h "$db_host" -D "$db" -p"$db_pass" -t -e  "$domain_sql")
output+="\n--------------End Domains---------------\n"
output+="\n-----------------IPs--------------------\n"
output+=$(mysql -u "$db_user" -h "$db_host" -D "$db" -p"$db_pass" -t -e  "$ip_sql")
output+="\n---------------End IPs------------------\n"
output+="\n---------------Records------------------\n"
output+=$(mysql -u "$db_user" -h "$db_host" -D "$db" -p"$db_pass" -t -e  "$records_sql")
output+="\n-------------End Records----------------\n"

echo -e "$output" | mail -s "OpenDNS Report $from_dt - $to_dt" "$email"