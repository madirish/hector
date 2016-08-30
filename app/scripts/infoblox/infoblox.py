#!/bin/python

import subprocess
import logging
import os,sys

appPath = os.path.abspath(os.path.dirname(os.path.realpath(__file__)) + "/../../")
sys.path.append(appPath + "/lib/pylib")

from pull_config import Configurator
configr = Configurator()

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
infoblox_log_file_name = ""
output_filename = "infoblock-queries.txt"

#pull log file
#@TODO: set up a method of pulling the infoblox log file

outfile = open(infoblox_dir+output_filename, "w")

gzip = subprocess.Popen(["gzip", "-dc", infoblox_dir+"logs/"+infoblox_log_file_name],stdout=subprocess.PIPE)
grep = subprocess.Popen(["grep", "-e", "query"],stdin=gzip.stdout,stdout=subprocess.PIPE)
awk = subprocess.Popen(["awk", "-f", infoblox_dir+"filter.awk"],stdin=grep.stdout,stdout=subprocess.PIPE)
sort = subprocess.Popen(["sort"],stdin=awk.stdout, stdout=subprocess.PIPE)
uniq = subprocess.Popen(["uniq"],stdin=sort.stdout,stdout=outfile)
uniq.communicate() # wait for process to complete