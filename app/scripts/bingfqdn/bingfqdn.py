#!/bin/python

import MySQLdb
import urllib2
import base64
from pull_config import Configurator

configr = Configurator()
DB = configr.get_var('db')
HOST = configr.get_var('db_host')
USERNAME = configr.get_var('db_user')
PASSWORD = configr.get_var('db_pass')
DEBUG = True

APIKEY = configr.get_var('bing_api_key')
BINGURL = 'https://api.datamarket.azure.com/Bing/Search/Web?Query=%27ip%3A'
BASE64STRING = base64.encodestring('%s:%s' % (APIKEY,APIKEY)).replace('\n', '')

try:
  conn = MySQLdb.connect(host=HOST,
                      user=USERNAME,
                      passwd=PASSWORD,
                      db=DB,
                      port=3306,
                      unix_socket='TCP')
except Exception as err:
  print "Error connecting to the database" , err

#look up IP's for web servers
cursor = conn.cursor()
sql = """select distinct(host_id) 
    from nmap_scan_result 
    where nmap_scan_result_port_number IN (80,443,8000,8080) 
      and state_id = 1 """
cursor.execute(sql)
host_ids = cursor.fetchall()
cursor.close()

hostmapip = {}

lastip = ''
# Pull the IP addresses of the hosts
for host_id in host_ids:
  cursor = conn.cursor()
  sql = 'select host_ip from host where host_id = %s'
  cursor.execute(sql, (host_id[0]))
  ip = cursor.fetchone()[0]
  hostmapip[host_id[0]] = ip

# Poll Bing
for host_id, host_ip in hostmapip.iteritems():
  url = BINGURL + str(host_ip) + '%27'
  request = urllib2.Request(url)
  request.add_header("Authorization", "Basic %s" % BASE64STRING)   
  try:
    result = urllib2.urlopen(request)
    retval = result.read()
  except Exception as err:
    print "Error polling Bing ", err
  
  x = 0
  for displayurl in retval.split('<d:DisplayUrl m:type="Edm.String">'):
    if x == 0:
      x = 1 # The first result is never applicable
      pass
    else:
      x = x + 1
      urlandtag = displayurl.split('</d:DisplayUrl')[0]
      cursor = conn.cursor()
      sql = 'insert into url (host_id, host_ip, url_url) values (%s,%s,%s) ON DUPLICATE KEY UPDATE host_id = %s'
      if DEBUG : print "Inserting %s : %s %s" % (host_id, host_ip, urlandtag)
      cursor.execute(sql, (host_id, host_ip, urlandtag, host_id))
      conn.commit()
      cursor.close()
