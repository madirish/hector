#!/bin/python

import subprocess
import logging
import os,sys
import MySQLdb

appPath = os.path.abspath(os.path.dirname(os.path.realpath(__file__)) + "/../../")
sys.path.append(appPath + "/lib/pylib")

from pull_config import Configurator
configr = Configurator()

# Credentials used for the database connection
configr = Configurator()
DB = configr.get_var('db')
HOST = configr.get_var('db_host')
USERNAME = configr.get_var('db_user')
PASSWORD = configr.get_var('db_pass')


#logging set up
logger = logging.getLogger('Infoblox python script')
hdlr = logging.FileHandler(appPath + '/logs/message_log')
error_hdlr = logging.FileHandler(appPath + '/logs/error_log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(name)s: %(message)s')
hdlr.setFormatter(formatter)
error_hdlr.setFormatter(formatter)
error_hdlr.setLevel(logging.ERROR)
logger.addHandler(hdlr) 
logger.addHandler(error_hdlr)
logger.setLevel(logging.DEBUG)

logger.info('infoblox.py starting')
logger.debug('args: [\''+('\', \''.join(sys.argv))+'\']')

#config vars
infoblox_dir = configr.get_var('approot')+"app/scripts/infoblox/"
infoblox_log_file_name = "infoblox.log.1.gz.short"
output_filename = "infoblock-queries.txt"

#pull log file
#@TODO: set up a method of pulling the infoblox log file

#outfile = open(infoblox_dir+output_filename, "w")

gzip = subprocess.Popen(["gzip", "-dc", infoblox_dir+"logs/"+infoblox_log_file_name],stdout=subprocess.PIPE)
grep = subprocess.Popen(["grep", "-e", "query"],stdin=gzip.stdout,stdout=subprocess.PIPE)
awk = subprocess.Popen(["awk", "-f", infoblox_dir+"filter.awk"],stdin=grep.stdout,stdout=subprocess.PIPE)
sort = subprocess.Popen(["sort"],stdin=awk.stdout, stdout=subprocess.PIPE)
uniq = subprocess.Popen(["uniq"],stdin=sort.stdout,stdout=subprocess.PIPE)            # outfile)
#records = uniq.communicate()[0] # wait for process to complete

logger.info("Opening database connection")
domains = {}
count = 0
domain_count = 0
conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB)
cursor = conn.cursor()
#print records
for record in iter(uniq.stdout.readline,''):
    count+=1
    r=record.split("|")
    dt=r[0]
    ip=r[1]
    dm=r[2].rstrip()
    if not dm in domains.keys():
        logger.debug("looking up domain \""+dm+"\"")
        cursor.execute("""SELECT domain_id from domain 
        where domain_name=%s""",(dm,))
        res = cursor.fetchone()
        if res == None:
            domain_count+=1
            cursor.execute("""Insert into domain set domain_name=%s""",(dm,))
            conn.commit()
            domains[dm] = int(cursor.lastrowid)
            logger.debug("inserted domain \""+dm+"\" with id \""+str(domains[dm])+"\"")
        else:
            domains[dm] = int(res[0])
    dm_id = domains[dm]   
    if count%1000000==0:
        logger.info(str(count)+' records added')
        
print domains
print len(domains)
conn.close()        
logger.info("infoblox.py finished. "+str(count)+" records added. "+str(domain_count)+" domains added.")

