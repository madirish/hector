#!/bin/python

import subprocess
import logging
import os,sys

appPath = os.path.abspath(os.path.dirname(os.path.realpath(__file__)) + "/../../")
sys.path.append(appPath + "/lib/pylib")

from pull_config import Configurator
configr = Configurator()

#logging set up
logger = logging.getLogger('OpenDNS python script')
hdlr = logging.FileHandler(appPath + '/logs/message_log')
error_hdlr = logging.FileHandler(appPath + '/logs/error_log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(name)s: %(message)s')
hdlr.setFormatter(formatter)
error_hdlr.setFormatter(formatter)
error_hdlr.setLevel(logging.ERROR)
logger.addHandler(hdlr) 
logger.addHandler(error_hdlr)
logger.setLevel(logging.INFO)

logger.info('opendns.py starting')
logger.debug('args: [\''+('\', \''.join(sys.argv))+'\']')

#config vars
opendns_dir = configr.get_var('approot')+"app/scripts/opendns/"
bucket = configr.get_var('s3_bucket')
if len(sys.argv)<2:
	logger.error('requires date as param YYYY-MM-DD. script terminated.')
	exit(1)
target_date = sys.argv[1]
output_filename = "Blocked-Domains-"+target_date+".txt"
logger.debug('opendns_dir: '+opendns_dir)
logger.debug('target_date: '+target_date)
logger.debug('output_filename: '+output_filename)

logger.info('pulling logs from s3 bucket.')
aws = subprocess.Popen(["aws", "s3", "cp", "--recursive", "s3://"+bucket+"/dnslogs/"+target_date+"/", opendns_dir+"logs/"+target_date+"/"], stdout=subprocess.PIPE)
aws.communicate() # wait for process to complete

logger.info('filtering out domain names')
outfile = open(opendns_dir+output_filename, "w")
gzip = subprocess.Popen(["gzip", "-rdc", opendns_dir+"logs/"+target_date+"/"],stdout=subprocess.PIPE)
grep = subprocess.Popen(["grep", "-e", "Malware"],stdin=gzip.stdout,stdout=subprocess.PIPE)
awk = subprocess.Popen(["awk", "-f", opendns_dir+"blocked.awk"],stdin=grep.stdout,stdout=subprocess.PIPE)
sort = subprocess.Popen(["sort"],stdin=awk.stdout, stdout=subprocess.PIPE)
uniq = subprocess.Popen(["uniq"],stdin=sort.stdout,stdout=outfile)
uniq.communicate() # wait for process to complete

outfile.close()

logger.info('calling import.php')
php = subprocess.Popen(["php", opendns_dir+"import.php", opendns_dir+output_filename], stdout=subprocess.PIPE)
php.communicate() # wait for process to complete

logger.info('cleaning up')
rm = subprocess.Popen(["rm", "-rvf", opendns_dir+output_filename, opendns_dir+"logs/"+target_date+"/"], stdout=subprocess.PIPE)
rm.communicate() # wait for process to complete

logger.info('opendns.py completed')
