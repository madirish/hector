import MySQLdb
import time
import re
import sys

HOST = 'localhost'
USERNAME = 'root'
PASSWORD = ''
DB = 'hector'
PORT = 3306

class LogEntry: 
  """ 
  This is just the object that we craft to 
  hold the OSSEC log file entry (since it is 
  a multi-line log)
  """
  # This is the OSSEC alert id (ex: 1297702559.16083181)
  ossec_alert_id = None
  # The date the alert was generated (ex: 2011 Feb 14 11:55:59)
  date = None
  # The host_id from the hector.host table
  host_id = None
  # The log the alert came from (ex: (www.sas.upenn.edu) 128.91.55.19->/var/log/httpd/error_log)
  alert_log = None
  # The rule id from the hector.ossec_rules table
  rule_id = None
  # The actual body of the log entry that caused the alert, into ossec_alerts.rule_log
  message = None
  
  # Other self explanatory messages
  src_ip = user = conn = None
  
  def __init__(self, conn):
    self.conn = conn
    
  def clear(self):
    self.ossec_alert_id = None
    self.date = None
    self.host_id = None
    self.alert_log = None
    self.rule_id = None
    self.src_ip = None
    self.user = None
    self.message = None
  
  def get_ossec_alert_id(self):
    if self.ossec_alert_id is None:
      return ""
    else:
      return self.ossec_alert_id
    
  def get_date(self):
    if self.date is None:
      return "0000-00-0000 00:00:00"
    else:
      return self.date
    
  def get_host_id(self):
    if self.host_id is None:
      return 0
    else:
      return self.host_id
    
  def get_alert_log(self):
    if self.alert_log is None:
      return ""
    else:
      return self.alert_log
  
  def get_rule_id(self):
    if self.rule_id is None:
      return 0
    else:
      return self.rule_id
    
  def get_src_ip(self):
    if self.src_ip is None:
      return "0.0.0.0"
    elif self.src_ip == "(none)":
      return "127.0.0.1"
    else:
      return self.src_ip
    
  def get_user(self):
    if self.user is None:
      return ""
    else:
      return self.user
    
  def get_message(self):
    if self.message is None:
      return ""
    else:
      return self.message
    
  # Process a line from the OSSEC log
  # Example OSSEC Log Entry:
  # ------------------------
  # ** Alert 1297702559.16083181: - apache,
  # 2011 Feb 14 11:55:59 (www.sas.upenn.edu) 128.91.55.19->/var/log/httpd/error_log
  # Rule: 31410 (level 3) -> 'PHP Warning message.'
  # Src IP: 128.91.34.6
  # User: (none)
  # [Mon Feb 14 11:56:00 2011] [error] [client 128.91.34.6] PHP Warning:  Call-time pass-by-reference has been deprecated - argument passed by value;  If you would like to pass it by reference, modify the declaration of task_send_extra_email().  If you would like to enable call-time pass-by-reference, you can set allow_call_time_pass_reference to true in your INI file.  However, future versions may not support this any longer.  in /www/data/drupal-6.19/sites/oni.sas.upenn.edu.taskmgr/modules/task/task.module on line 254, referer: https://oni.sas.upenn.edu/taskmgr/
  # 
  def process(self, line):
    if line[0:8] == '** Alert':
      # Got the alert line
      alert_id = line.split(' ')[2][0:-1]
      self.set_ossec_alert_id(alert_id)
    elif re.match("\d{4} [A-Z][a-z]{2} \d{1,2} ", line):
      if self.alert_log is None:
        linesplit = line.split(' ')
        self.set_date(' '.join([linesplit[0], linesplit[1], linesplit[2], linesplit[3]]))
        self.set_alert_log(' '.join(linesplit[4:]))
    elif line[0:6] == 'Rule: ':
      if self.set_rule_id(line.split(' ')[1]) == False:
        self.set_new_rule(line)
    elif line[0:8] == 'Src IP: ':
        self.set_src_ip(line[8:])
    elif line[0:6] == 'User: ':
        self.set_user(line[6:])
    else:
      # This must be the full message
      if len(line) > 1:
        self.set_message(line)
    
  def set_alert_log(self, log):
    self.alert_log = str(log).strip()
    
  def set_date(self, date):
    self.date = str(date).strip()
    
  def set_host_id(self, id):
    self.host_id = int(id)
    
  def set_message(self, message):
    self.message = str(message).strip()
    
  # Rule: 31410 (level 3) -> 'PHP Warning message.'
  def set_new_rule(self, rulestr):
    rulestr = str(rulestr).strip()
    rulesplit = rulestr.split(' ')
    number = rulesplit[1]
    message = rulestr.split('->')[1][2:-1]
    level = rulesplit[3][0:-1]
    try:
      cursor = self.conn.cursor()
      sql = 'insert into ossec_rules set '
      sql += ' rule_number = "%s", '
      sql += ' rule_message = "%s", '
      sql += ' rule_level = "%s"'
      cursor.execute(sql % (number, message, level)) 
      self.conn.commit() 
      cursor.close()
      if self.set_rule_id(number) == False:
        print "Error setting new rule id in LogEntry object!"
        return False
      return True
    except Exception as err:
      print "Transaction error saving new rule (set_new_rule()) in LogEntry object " , err
      return False
    
  # OSSEC alerts identifiers in the form 1297702559.16083181
  def set_ossec_alert_id(self, id):
    id = re.sub('![\d\.]', '', id)
    self.ossec_alert_id = id.strip()
     
  # Expects the rule number from OSSEC, rather than the db
  # therefore we have to look it up in the db adn set it 
  # accordingly
  #
  # Return False if we can't find it so it can be inserted
  def set_rule_id(self, id):
    id = int(id)
    try:
      cursor = self.conn.cursor()
      sql = 'select rule_id from ossec_rules where rule_number = %d'
      cursor.execute(sql % id) 
      rule_id = int(cursor.fetchone()[0])
    except Exception as err:
      # this error output is useless, always prints out: 'NoneType' object is unsubscriptable
      # print "Transaction error in set_rule_id() in LogEntry object:" , err
      return False
    if rule_id < 1:
      return False
    self.rule_id = rule_id
    return True
    
  def set_src_ip(self, ip):
    ip= str(ip).strip()
    ip = re.sub('![\d\.]', '', ip)
    if ip == '':
      ip = '0.0.0.0'
    self.src_ip = ip
    
  def set_user(self, user):
    self.user = str(user).strip()
    
  def save(self):
    try:
      cursor = self.conn.cursor()
      sql = 'insert into ossec_alerts set '
      sql += ' alert_date = STR_TO_DATE("%s",\'%%Y %%b %%d %%H:%%i:%%s\'), ' # 2011 Feb 14 11:55:59
      sql += ' host_id = "%s", '
      sql += ' alert_log = "%s", '
      sql += ' rule_id = "%s", '
      sql += ' rule_user = "%s", '
      sql += ' rule_log = "%s", '
      sql += ' rule_src_ip = "%s", '
      sql += ' rule_src_ip_numeric = INET_ATON("%s"), '
      sql += ' alert_ossec_id = "%s" '
      cursor.execute(sql % (self.get_date(),
                            self.get_host_id(),
                            self.get_message(),
                            self.get_rule_id(),
                            self.get_user(),
                            self.get_alert_log(),
                            self.get_src_ip(),
                            self.get_src_ip(),
                            self.get_ossec_alert_id()))
      self.conn.commit() 
      cursor.close()
    except Exception as err:
      print "Transaction error saving LogEntry object " , err


import unittest

class TestLogEntry(unittest.TestCase):
  def setUp(self):
    try:
      self.conn = MySQLdb.connect(host=HOST,
                                  user=USERNAME,
                                  passwd=PASSWORD,
                                  db=DB,
                                  port=PORT)
    except Exception as err:
      print "Error connecting to the database" , err
      
    self.log = LogEntry(self.conn)
    self.log.process("** Alert 1297702559.16083181: - apache,")
    self.log.process("2011 Feb 14 11:55:59 (www.sas.upenn.edu) 128.91.55.19->/var/log/httpd/error_log")
    self.log.process("Rule: 31410 (level 3) -> 'PHP Warning message.'")
    self.log.process("Src IP: 128.91.34.6")
    self.log.process("User: (none)")
    self.log.process("[Mon Feb 14 11:56:00 2011] [error] [client 128.91.34.6] PHP Warning:  Call-time pass-by-reference has been deprecated - argument passed by value;  If you would like to pass it by reference, modify the declaration of task_send_extra_email().  If you would like to enable call-time pass-by-reference, you can set allow_call_time_pass_reference to true in your INI file.  However, future versions may not support this any longer.  in /www/data/drupal-6.19/sites/oni.sas.upenn.edu.taskmgr/modules/task/task.module on line 254, referer: https://oni.sas.upenn.edu/taskmgr/")
    
  def test_ossec_alert_id(self):
    self.assertEqual(self.log.get_ossec_alert_id(), "1297702559.16083181")
  def test_get_date(self):
    self.assertEqual(self.log.get_date(), "2011 Feb 14 11:55:59")
  def test_get_host_id(self):
    self.log.set_host_id(1)
    self.assertEqual(self.log.get_host_id(), 1)
  def test_get_alert_log(self):
    self.assertEqual(self.log.get_alert_log(), "(www.sas.upenn.edu) 128.91.55.19->/var/log/httpd/error_log")
  def test_get_rule_id(self):
    cursor = self.conn.cursor()
    sql = 'select rule_id from ossec_rules where rule_level = "3" '
    sql += 'AND rule_message = "PHP Warning message." AND rule_number = "31410"'
    cursor.execute(sql) 
    rule_id = cursor.fetchone()[0]
    cursor.close()
    self.assertEqual(self.log.get_rule_id(), rule_id)
  def test_get_src_ip(self):
    self.assertEqual(self.log.get_src_ip(), "128.91.34.6")
  def test_get_user(self):
    self.assertEqual(self.log.get_user(), "(none)")
  def test_get_message(self):
    self.assertEqual(self.log.get_message(), "[Mon Feb 14 11:56:00 2011] [error] [client 128.91.34.6] PHP Warning:  Call-time pass-by-reference has been deprecated - argument passed by value;  If you would like to pass it by reference, modify the declaration of task_send_extra_email().  If you would like to enable call-time pass-by-reference, you can set allow_call_time_pass_reference to true in your INI file.  However, future versions may not support this any longer.  in /www/data/drupal-6.19/sites/oni.sas.upenn.edu.taskmgr/modules/task/task.module on line 254, referer: https://oni.sas.upenn.edu/taskmgr/")


# Tail (follow) the log file and parse it into the database
def follow(thefile):
    thefile.seek(0,2)      # Go to the end of the file
    sleep = 0.00001
    while True:
        line = thefile.readline()
        if not line:
            time.sleep(sleep)    # Sleep briefly
            if sleep < 1.0:
                sleep += 0.00001
            continue
        sleep = 0.00001
        yield line
        
if __name__ == '__main__':
  if len(sys.argv) > 1 and sys.argv[1] == 'test':
    suite = unittest.TestLoader().loadTestsFromTestCase(TestLogEntry)
    unittest.TextTestRunner(verbosity=2).run(suite)
  else:
    print "Processing log file"
    try:
      conn = MySQLdb.connect(host=HOST,
                                  user=USERNAME,
                                  passwd=PASSWORD,
                                  db=DB,
                                  port=PORT)
    except Exception as err:
      print "Error connecting to the database" , err
    logfile = open("/var/ossec/logs/alerts/alerts.log")
    loglines = follow(logfile)
    log = LogEntry(conn)
    for line in loglines:
      # start a new log if necessary
      if line[0:8] == '** Alert':
        if log.get_ossec_alert_id() is not "":
          print "Saving log"
          print log.get_alert_log()
          print log.get_date()
          print log.get_host_id()
          print log.get_message()
          print log.get_ossec_alert_id()
          print log.get_rule_id()
          print log.get_src_ip()
          print log.get_user()
          log.save()
        log.clear()
      log.process(line)