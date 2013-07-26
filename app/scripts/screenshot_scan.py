#!/usr/bin/python
import Queue
import threading
import MySQLdb
import time
import ConfigParser
import urllib2
import subprocess

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

class ScreenShotThread(threading.Thread):
    """Threaded Url Grab"""
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
            print self.full_url, ' gave response: ', response
            if response != 'failed': self.take_snapshot()
                
            #signals to queue job is done
            self.urls.task_done()
            
    def take_snapshot(self):
        filter=['/','.',':',';']
        filename = self.full_url
        for c in filter : filename=filename.replace(c, '_')
        filename += str(int(time.time())) + '.png'
        command = 'phantomjs /opt/hector/app/scripts/snapshot.js \'' + self.full_url +'\' \'' + filename +'\''
        print command
        proc = subprocess.Popen(command, stdout=subprocess.PIPE, shell=True)
        (out, err) = proc.communicate()
        print out
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
for i in range(10):
    t = ScreenShotThread(urls)
    t.setDaemon(True)
    t.start()
for result in results: urls.put(result[0])
urls.join()
