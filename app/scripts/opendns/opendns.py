#!/bin/python

import subprocess
import logging
import os,sys
from datetime import date, timedelta
import os

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
logger.setLevel(logging.DEBUG)

logger.info('opendns.py starting')
logger.debug('args: [\''+('\', \''.join(sys.argv))+'\']')

#config vars
opendns_dir = configr.get_var('approot')+"app/scripts/opendns/"
bucket = configr.get_var('s3_bucket')
if len(sys.argv)>=2:
	target_date = sys.argv[1]
	logger.info('User supplied target date \''+target_date+'\'')
else:
	target_date = str(date.today() - timedelta(days=1))
	logger.info('There was no user supplied target date. Using target date \''+target_date+'\'')


output_filename = "Blocked-Domains-"+target_date+".txt"
logger.debug('opendns_dir: '+opendns_dir)
logger.debug('target_date: '+target_date)
logger.debug('output_filename: '+output_filename)

aws_env = os.environ.copy()
aws_env['AWS_ACCESS_KEY_ID'] = configr.get_var('aws_access_key_id')
aws_env['AWS_SECRET_ACCESS_KEY'] = configr.get_var('aws_secret_access_key')
logger.info('pulling logs from s3 bucket.')
aws = subprocess.Popen(["aws", "s3", "cp", "--recursive", "s3://"+bucket+"/dnslogs/"+target_date+"/", opendns_dir+"logs/"+target_date+"/"], 
					stdout=subprocess.PIPE,stderr=subprocess.PIPE, env=aws_env)
aws_out,aws_err = aws.communicate() # wait for process to complete
if aws.returncode>0:
	logger.error('AWS process error. Exited with status code: \''+str(aws.returncode)+'\'')
	logger.error('AWS process error. Error output: \''+str(aws_err).strip()+'\'')
	exit(1);
else:
	logger.debug('AWS completed with return code: \''+str(aws.returncode)+'\'')

outfile = open(opendns_dir+output_filename, "w")

logger.info('filtering out domain names')

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
