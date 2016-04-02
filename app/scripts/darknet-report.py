#!/usr/bin/env python

# report-darknet.py
#
# Clay Wells
# Hoang Bui
#
# Generate a darknet report in csv file 
#
# Resources
# =========
# PyYAML - http://pyyaml.org/wiki/PyYAMLDocumentation
# mysqlclient - https://mysqlclient.readthedocs.org/en/latest/
#

#import argparse

import sys
import yaml
import MySQLdb as mdb
import csv


def int2ip(ipnum):
    o1 = int(ipnum / 16777216) % 256
    o2 = int(ipnum / 65536) % 256
    o3 = int(ipnum / 256) % 256
    o4 = int(ipnum) % 256
    return '%(o1)s.%(o2)s.%(o3)s.%(o4)s' % locals()


try:
    if sys.argv[1]:
        days=sys.argv[1]
	#print "[+] days set to: %s" % days
    if days:
	#print "[+] converting days from string to int"
	getdays = int(days)
except:
	getdays = 1


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

# get the darknet config options
dnet_start = config['darknet_start_date']
dnet_stop = config['darknet_stop_date']
dayfile = config['darknet_dayfile']
allfile = config['darknet_allfile']

if debug:
    print ("[+] dbhost: %s") % dbhost
    print ("[+] dbname: %s") % dbname
    print ("[+] dbuser: %s") % dbuser

# Basic query
# TODO: make this configurable?
# Uncomment which query you want to run and uncomment the appropriate section in following
#  try block below. 

query = "SELECT CONCAT(dst_port, '/', proto) AS port, count(id) AS cnt " \
        "FROM darknet WHERE received_at > DATE_SUB(NOW(), INTERVAL %d DAY) " \
        "GROUP BY port ORDER BY cnt DESC LIMIT 40;" % getdays

qrange = "SELECT CONCAT(dst_port, '/', proto) AS port, src_ip, received_at " \
       "FROM darknet WHERE received_at BETWEEN '%s' AND '%s' " \
       "ORDER BY received_at, src_ip, port;" % (dnet_start, dnet_stop)


# We can add other queries for creating the specific datasets we'd like to analyse.

# Grab it all
#qall = "SELECT CONCAT(dst_port, '/', proto) AS port, src_ip, " \
#       "country_code, received_at, dst_port, proto " \
#       "FROM darknet WHERE dst_port=22 or dst_port=2222 ORDER BY received_at, src_ip, port;"

try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()

    cur.execute(query)
    rows = cur.fetchall()
    # TODO: it might be nice to name the file dynamically using a variable.
    with open('/opt/hector/app/reports/report-darknet.csv', 'w') as f:
        repwriter = csv.writer(f, delimiter=',')

        for row in rows:
            #print row
            thisport = row[0]
            thiscnt = row[1] 
            print "%s, %s" % (thisport, thiscnt)
            repwriter.writerow([thisport, thiscnt])

    # src_ip is stored as an int. convert int to char
#    cur.execute(qall)
#    rows = cur.fetchall()
#    with open(dayfile, 'w') as f2:
#        rep2writer = csv.writer(f2, delimiter=',')
#        for row in rows:
#            qport = row[0]
#            qsrcip = row[1]
#            qwhen = row[2]
#            # convert src_ip int to ip
#            ipaddr = int2ip(qsrcip)
#            print "%s, %s, %s" % (qwhen, qport, ipaddr)
#            rep2writer.writerow([qwhen, qport, ipaddr])
#
#    cur.execute(qall)
#    rows = cur.fetchall()
#    with open(allfile, 'w') as f3:
#        rep2writer = csv.writer(f3, delimiter=',')
#        rep2writer.writerow(['Timestamp', 'Port', 'Protocol', 'Port/Proto', 'Source IP', 'Country Code'])
#        for row in rows:
#            port = row[0]
#            src_ip = row[1]
#            country_code = row[2]
#            received_at = row[3]
#            dst_port = str(row[4])
#            proto = row[5]
#            # convert src_ip int to ip
#            srcip = int2ip(src_ip)
#            if srcip == '128.91.234.47':
#                # skip this row
#                print "[-] skipping row: %s " % srcip
#            else:
#                print "%s, %s, %s, %s, %s, %s" % (received_at, dst_port, proto, port, srcip, country_code)
#                rep2writer.writerow([received_at, dst_port, proto, port, srcip, country_code])
 

except mdb.Error, e:
  
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit("DB error")
 
#finally:    
        
if con:    
    con.close()

