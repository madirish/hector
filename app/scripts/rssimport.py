#!/usr/bin/python
"""
This script is part of HECTOR.
by Justin C. Klein Keane <jukeane@sas.upenn.edu>
Last modified: 19 Dec, 2013

This script pulls in RSS news items based on configuration in the HECTOR
database (from the rss table) and populates the article table for display
and analysis. 

ToDo: Auto-tag articles based on tag keywords showing up in the article
title or teaser
"""

import sys
sys.path.append('/opt/hector/app/software/feedparser')
import datetime
import feedparser
import MySQLdb
import ConfigParser
import logging


DEBUG = False

# Credentials used for the database connection
configr = ConfigParser.ConfigParser()
configr.read('/opt/hector/app/conf/config.ini')
global HOST, USERNAME, PASSWORD, DB, PORT
HOST = configr.get('hector', 'db_host')
USERNAME =  configr.get('hector', 'db_user')
PASSWORD = configr.get('hector', 'db_pass')
DB = configr.get('hector', 'db')
PORT = 3306
conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB,
      port=PORT)
cursor = conn.cursor()

#logging set up
logger = logging.getLogger('hectorss')
hdlr = logging.FileHandler('/opt/hector/app/logs/message_log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
hdlr.setFormatter(formatter)
logger.addHandler(hdlr) 
logger.setLevel(logging.INFO)
if DEBUG : logger.setLevel(logging.DEBUG)
logger.info('RSS feed import from rssimport.py is starting')

cursor.execute('select rss_url from rss')
results = cursor.fetchall()


for feedurl in results: 
  feed = feedparser.parse(feedurl[0])
  for feeditem in feed["items"]:
    if feeditem.has_key("published_parsed"):
      mysqldate  = str(feeditem["published_parsed"][0]) + "-"
      mysqldate += str(feeditem["published_parsed"][1]) + "-"
      mysqldate += str(feeditem["published_parsed"][2]) + " "
      mysqldate += str(feeditem["published_parsed"][3]) + ":"
      mysqldate += str(feeditem["published_parsed"][4]) + ":"
      mysqldate += str(feeditem["published_parsed"][5])
    elif feeditem.has_key("date_parsed"):
      mysqldate  = str(feeditem["date_parsed"][0]) + "-"
      mysqldate += str(feeditem["date_parsed"][1]) + "-"
      mysqldate += str(feeditem["date_parsed"][2]) + " "
      mysqldate += str(feeditem["date_parsed"][3]) + ":"
      mysqldate += str(feeditem["date_parsed"][4]) + ":"
      mysqldate += str(feeditem["date_parsed"][5])
    else:
      mysqldate = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")
    sql = "INSERT INTO article (article_date, article_title, article_url, article_teaser)"
    sql += " SELECT %s, %s, %s, %s FROM DUAL WHERE NOT EXISTS "
    sql += "(SELECT article_id FROM article WHERE article_url = %s)"
    
    try:
      cursor.execute(sql,(mysqldate, feeditem["title"], feeditem["link"], feeditem["summary"], feeditem["link"]))
    except MySQLdb.OperationalError, e:
      raise e

conn.commit()
conn.close()
