#!/usr/bin/python
import Queue
import threading
import MySQLdb
import time
import re
import sys
import syslog
from os import path
import ConfigParser
import urllib2

# Credentials used for the database connection
configr = ConfigParser.ConfigParser()
configr.read('/opt/hector/app/conf/config.ini')
HOST = configr.get('hector', 'db_host')
USERNAME =  configr.get('hector', 'db_user')
PASSWORD = configr.get('hector', 'db_pass')
DB = configr.get('hector', 'db')
PORT = 3306
DEBUG = False
conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB,
      port=PORT)

class ScreenShotThread(threading.Thread):
    """Threaded Url Grab"""
    def __init__(self, urls, conn):
        threading.Thread.__init__(self)
        self.urls = urls
        self.conn = conn
        self.cursor = conn.cursor()

    def run(self):
        while True:
            #grabs url from queue
            db_url = self.urls.get() #url for database purposes
            full_url=''              #url for phantomjs/urllib2
            if not db_url.startswith('http'):
                full_url = 'http://'
            full_url += db_url
            try :
                response = urllib2.urlopen(full_url,timeout=10)
                response = response.getcode()
            except :
                response = 'failed'
            print full_url, ' gave response: ', response
            #signals to queue job is done
            self.urls.task_done()
            
cursor = conn.cursor()
cursor.execute('select url_url from url')
results = cursor.fetchall()
urls=Queue.Queue()
for i in range(10):
    t = ScreenShotThread(urls, conn)
    t.setDaemon(True)
    t.start()
for result in results: urls.put(result[0])
urls.join()
conn.close()

