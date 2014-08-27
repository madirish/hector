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

import datetime
import MySQLdb
import logging
import sys, os
# appPath - for example /opt/hector/app
appPath = os.path.abspath(os.path.dirname(os.path.realpath(__file__)) + "/../../app")
sys.path.append(appPath + "/lib/pylib")
sys.path.append(appPath + "/software/feedparser")

from pull_config import Configurator
import feedparser


DEBUG = False

# Credentials used for the database connection
configr = Configurator()
DB = configr.get_var('db')
HOST = configr.get_var('db_host')
USERNAME = configr.get_var('db_user')
PASSWORD = configr.get_var('db_pass')
PORT = 3306
conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB,
      port=PORT)
conn.autocommit()
cursor = conn.cursor()
conn.set_character_set('utf8')

#logging set up
logger = logging.getLogger('hectorss')
hdlr = logging.FileHandler(appPath + '/logs/message_log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
hdlr.setFormatter(formatter)
logger.addHandler(hdlr) 
logger.setLevel(logging.INFO)
if DEBUG : logger.setLevel(logging.DEBUG)
logger.info('RSS feed import from rssimport.py is starting')

# get tags
sql = "SELECT * FROM tag"
cursor.execute(sql)
tags = cursor.fetchall()

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
    
    sql = "INSERT INTO article (article_date, article_title, article_url, article_teaser, article_body)"
    sql += " SELECT %s, %s, %s, %s, %s FROM DUAL WHERE NOT EXISTS "
    sql += "(SELECT article_id FROM article WHERE article_url = %s)"

    try:
      if DEBUG: print "[+] Attempting to insert item "
      if DEBUG: print feeditem
      cursor.execute(sql,(mysqldate, feeditem["title"], feeditem["link"], feeditem["summary"], feeditem["description"], feeditem["link"]))
      if DEBUG: print "[+] Inserted " + str(conn.insert_id())
    except MySQLdb.OperationalError, e:
      print "Error importing feeditem " + feeditem["title"]
      raise e
    
    # Insure we got an article id
    article_id = conn.insert_id()
    if (article_id < 1):
      sql = "SELECT article_id FROM article WHERE article_title = %s AND article_url = %s"
      cursor.execute(sql,(feeditem["title"], feeditem["link"]))
      row = cursor.fetchone()
      while row is not None:
        article_id = row[0]
    
    # Autotag articles
    for (tag_id, tag_name) in tags:
      tagged = 0
      if feeditem["title"].find(tag_name) > -1 :
          sql = "insert into article_x_tag set article_id = %s, tag_id = %s"
          cursor.execute(sql, (conn.insert_id(), tag_id))
          tagged = 1
          if DEBUG: print "[+] Found tag_id " + str(tag_id) + " named " + tag_name + " in: " + feeditem["title"]
      if feeditem["summary"].find(tag_name) > -1 and tagged == 0 :
          sql = "insert into article_x_tag set article_id = %s, tag_id = %s"
          cursor.execute(sql, (conn.insert_id(), tag_id))
          if DEBUG: print "[+] Found tag_id " + str(tag_id) + " named " + tag_name + " in: " + feeditem["title"]
conn.close()
