#!/usr/bin/env python

# report-darknet.py
#
# Clay Wells
#
# Search for a specified IP address and output to csv file.
#
# Resources
# =========
# PyYAML - http://pyyaml.org/wiki/PyYAMLDocumentation
# mysqlclient - https://mysqlclient.readthedocs.org/en/latest/
#
# 


import sys
import yaml
import MySQLdb as mdb
import csv

# convert int value to ip address string
def int2ip(ipnum):
    o1 = int(ipnum / 16777216) % 256
    o2 = int(ipnum / 65536) % 256
    o3 = int(ipnum / 256) % 256
    o4 = int(ipnum) % 256
    return '%(o1)s.%(o2)s.%(o3)s.%(o4)s' % locals()

# convert string ip address to int value
def ip2int(ip):
    o = map(int, ip.split('.'))
    res = (16777216 * o[0]) + (65536 * o[1]) + (256 * o[2]) + o[3]
    return res


# get the ip address string we're looking for from sys.argv[1]
try:
    if sys.argv[1]:
        ipstring=sys.argv[1]
    if ipstring:
	print "[+] running search for %s" % ipstring
        ipint = int(ip2int(ipstring))
        print "[+] %s converted to %d" % (ipstring, ipint)  
	
except:
    print "[-] no IP address provided.. exiting"
    sys.exit(0)


# TODO: fix the way the path is set below. The path below will only work when the
#       script is executed from within the app/scripts/ directory.
try:
    config = yaml.load(file('../conf/config.yaml', 'r'))
except yaml.YAMLError, exc:
    print ("Error in configuration file:", exc)

dbconf = yaml.dump(config)

debug =  config['debug']
dbuser = config['db_user']
dbpass = config['db_pass']
dbname = config['db_name']
dbhost = config['db_host']

# search across a range of dates.
dnet_start = config['darknet_start_date']
dnet_stop = config['darknet_stop_date']

if debug:
    print ("[+] dbhost: %s") % dbhost
    print ("[+] dbname: %s") % dbname
    print ("[+] dbuser: %s") % dbuser

# Basic query
query = "SELECT CONCAT(dst_port, '/', proto) AS port, count(id) AS cnt " \
       "FROM darknet WHERE src_ip=%d AND received_at BETWEEN '%s' AND '%s' " \
       "ORDER BY received_at;" % (ipint, dnet_start, dnet_stop)

# We can add other queries for creating the specific datasets we'd like to analyse.

# Connect to the DB
try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()

    cur.execute(query)
    rows = cur.fetchall()
    with open('/opt/hector/app/reports/darknet-search-by-ip.csv', 'w') as f:
        repwriter = csv.writer(f, delimiter=',')

        for row in rows:
            #print row
            thisport = row[0]
            thiscnt = row[1]
            print "%s, %s, %s" % (ipstring, thisport, thiscnt)
            repwriter.writerow([thisport, thiscnt])


except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit("DB error")
 

if con:    
    con.close()

