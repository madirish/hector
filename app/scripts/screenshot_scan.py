#!/usr/bin/python
"""
This script is part of HECTOR.
by Josh Bauer <joshbauer3@gmail.com>
Last modified: 29 July, 2013

This script requires python 2.5 or higher.

This script is a threaded screenshot scan
using phantomjs to render screenshots for urls
in Hector's url table. Files are stored in the 
"app/screenshots" directory. This script is 
called by "screenshot_scan.php".
"""

import Queue
import threading
import MySQLdb
import time
import ConfigParser
import urllib2
import subprocess
import logging

# Credentials used for the database connection
configr = ConfigParser.ConfigParser()
configr.read('/opt/hector/app/conf/config.ini')
global HOST, USERNAME, PASSWORD, DB, PORT
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
cursor = conn.cursor()

#logging set up
logger = logging.getLogger('screenshot scan')
hdlr = logging.FileHandler('/opt/hector/app/logs/message_log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
hdlr.setFormatter(formatter)
logger.addHandler(hdlr) 
logger.setLevel(logging.WARNING)
if DEBUG : logger.setLevel(logging.DEBUG)

class ScreenShotThread(threading.Thread):
    """Threaded Screenshot Grab"""
    def __init__(self, urls):
        threading.Thread.__init__(self)
        self.urls = urls

    def run(self):
        while True:
            #grabs url from queue
            self.url = self.urls.get() #url for database purposes
            self.full_url=''              #url for phantomjs/urllib2
            if not self.url.startswith('http'):
                self.full_url = 'http://'
            self.full_url += self.url
            try :
                response = urllib2.urlopen(self.full_url,timeout=10)
                response = response.getcode()
            except :
                response = 'failed'
            logger.debug(self.name + " " + self.full_url + ' gave response: ' + str(response))
            if response != 'failed': self.take_snapshot()
                
            #signals to queue job is done
            self.urls.task_done()
            
    def take_snapshot(self):
        """calls phantomjs to capture screenshot and updates the database"""
        filter=['/','.',':',';']
        filename = self.full_url
        for c in filter : filename=filename.replace(c, '_')
        filename += '_' + str(int(time.time())) + '.png'
        command = 'phantomjs /opt/hector/app/scripts/snapshot.js \'' + self.full_url +'\' \'' + filename +'\''
        logger.debug(self.name + " command: " + command + " start")
        proc = subprocess.Popen(command, stdout=subprocess.PIPE, shell=True)
        (out, err) = proc.communicate()
        logger.debug(self.name + " command: "+ command + "\n\toutput: " + out)
        if out.count('Status: success')>0 :
                conn = MySQLdb.connect(host=HOST,
                      user=USERNAME,
                      passwd=PASSWORD,
                      db=DB,
                      port=PORT)
                cursor = conn.cursor()
                cursor.execute('update url set url_screenshot=%s where url_url=%s',(filename,self.url))
                conn.commit()
                
conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB,
      port=PORT)
cursor = conn.cursor()
cursor.execute('select url_url from url')
results = cursor.fetchall()
conn.close()
urls=Queue.Queue()
#initialize threads
for i in range(10):
    t = ScreenShotThread(urls)
    t.setDaemon(True)
    t.start()
#populate the queue
for result in results: urls.put(result[0])
#wait for the queue to be emptied
urls.join()