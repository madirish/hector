#!/bin/python

import subprocess
import logging
import os,sys

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
logger.setLevel(logging.INFO)

logger.info('infoblox.py starting')
logger.debug('args: [\''+('\', \''.join(sys.argv))+'\']')

#config vars
infoblox_dir = configr.get_var('approot')+"app/scripts/infoblox/"
infoblox_log_file_name = "infoblox.log.1.gz"
output_filename = "infoblock-queries.txt"

#pull log file
#@TODO: set up a method of pulling the infoblox log file

#outfile = open(infoblox_dir+output_filename, "w")

gzip = subprocess.Popen(["gzip", "-dc", infoblox_dir+"logs/"+infoblox_log_file_name],stdout=subprocess.PIPE)
grep = subprocess.Popen(["grep", "-e", "query"],stdin=gzip.stdout,stdout=subprocess.PIPE)
awk = subprocess.Popen(["awk", "-f", infoblox_dir+"filter.awk"],stdin=grep.stdout,stdout=subprocess.PIPE)
sort = subprocess.Popen(["sort"],stdin=awk.stdout, stdout=subprocess.PIPE)
uniq = subprocess.Popen(["uniq"],stdin=sort.stdout,stdout=subprocess.PIPE)            # outfile)
records = uniq.communicate()[0] # wait for process to complete

logger.info("Starting to import records into database")
count = 0;
conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB)
cursor = conn.cursor()

for r in records:
    count+=1
    
    if count%1000==0:
        logger.info(str(count)+' records added')
        
        
logger.info("infoblox.py finished. "+str(count)+" records added.")