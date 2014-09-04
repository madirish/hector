#!/bin/python
#
# bingfqdn.py
#
# Author: Justin C. Klein Keane <jukeane@sas.upenn.edu>
# Last modified: 31 July , 2014
#
# Integrate HECTOR scans for web ports with a reverse IP
# based lookup via Bing.  This will show, for example, 
# what hostnames or URL's are associated with a specific
# host's IP address.  Useful for identifying web sites
# that aren't available via the default URL.  This 
# script populates the URL table that is then used for
# facilities like screen shots.
#

import MySQLdb
import urllib2
import base64
import sys, os, socket
sys.path.append(os.path.abspath(os.path.dirname(os.path.realpath(__file__)) + "/../../lib/pylib"))
from pull_config import Configurator
from urlparse import urlparse

configr = Configurator()
DB = configr.get_var('db')
HOST = configr.get_var('db_host')
USERNAME = configr.get_var('db_user')
PASSWORD = configr.get_var('db_pass')
DEBUG = False

APIKEY = configr.get_var('bing_api_key')
BINGURL = 'https://api.datamarket.azure.com/Bing/Search/Web?Query=%27ip%3A'
BASE64STRING = base64.encodestring('%s:%s' % (APIKEY,APIKEY)).replace('\n', '')
BASE64STRING = (':%s' % APIKEY).encode('base64')[:-1]

try:
  conn = MySQLdb.connect(host=HOST,
                      user=USERNAME,
                      passwd=PASSWORD,
                      db=DB)
except Exception as err:
  print "Error connecting to the database" , err
  
if DEBUG : print "[!] Starting BingFQDN\n"

#look up IP's for web servers
cursor = conn.cursor()
sql = """SELECT DISTINCT(host_id) 
    FROM nmap_result
    WHERE nmap_result_port_number IN (80,443,8000,8080)
      AND nmap_result_protocol = 'tcp' 
      AND state_id = 1 """
cursor.execute(sql)
host_ids = cursor.fetchall()
cursor.close()

hostmapip = {}

lastip = ''
# Pull the IP addresses of the hosts
for host_id in host_ids:
  cursor = conn.cursor()
  sql = 'SELECT host_ip FROM host WHERE host_id = %s'
  cursor.execute(sql, (host_id[0]))
  ip = cursor.fetchone()[0]
  hostmapip[host_id[0]] = ip
  if DEBUG : print "Adding host_id " + str(host_id[0]) + " IP " + ip + " to hostmapip"

# Poll Bing
for host_id, host_ip in hostmapip.iteritems():
  if DEBUG : print "[?] Polling Bing for IP " + host_ip
  url = BINGURL + str(host_ip) + '%27'
  request = urllib2.Request(url)
  request.add_header("Authorization", "Basic %s" % BASE64STRING)   
  try:
    result = urllib2.urlopen(request)
    retval = result.read()
    if DEBUG : print "  Got Bing response: " + retval
  except Exception as err:
    print "Error polling Bing ", err
  
  x = 0
  for displayurl in retval.split('<d:Url m:type="Edm.String">'):
    if x == 0:
      x = 1 # The first result is never applicable
      pass
    else:
      x = x + 1
      urlandtag = displayurl.split('</d:Url')[0]
      cursor = conn.cursor()
      sql = 'INSERT INTO url (host_id, url_url) VALUES (%s,%s) ON DUPLICATE KEY UPDATE host_id = %s'
      parsedURL = urlparse(urlandtag)
      if DEBUG : print "    parsedURL is " + parsedURL.netloc
      lookupIP = socket.gethostbyname(parsedURL.netloc)
      if DEBUG : print "    lookupIP is " + lookupIP
      if (lookupIP == hostmapip[host_id]):
      	# Reverse lookup OK
      	if DEBUG : print "[+] Inserting %s : %s" % (host_id, urlandtag)
      	cursor.execute(sql, (host_id, urlandtag, host_id))
      else:
      	if DEBUG : print "[-] Not inserting because " + lookupIP + " doesn't match " + hostmapip[host_id] + " for " + urlandtag
      conn.commit()
      cursor.close()

