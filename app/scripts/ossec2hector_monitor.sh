#!/usr/bin/python
# Author: James Davis <jpd838@gmail.com> 
#

import os, time, datetime, smtplib, sys, commands

def sendMail(message, status):
    sender = 'root'
    receiver = 'root'
    s = smtplib.SMTP('localhost')
    s.sendmail(sender, receiver, 'Subject: %s\r\n%s' % (message, status))
    s.quit()            

if os.path.isfile("/tmp/hectorcheck.pid"):
	if commands.getoutput("cat /tmp/hectorcheck.pid") == os.getpid():
		sys.exit()
	else:
	    os.system('service ossec2hector start')
	    if commands.getoutput('service ossec2hector status') == 'HECTOR OSSEC to MySQL import daemon server is up and running.':
	    	if os.path.isfile("/tmp/hector_dead"):
        		os.remove('/tmp/hector_dead')
	    fpid = file("/tmp/hectorcheck.pid", "w")
	    fpid.write(str(os.getpid()))
	    if os.path.isfile("/tmp/hector_dead"):
	        os.system('service ossec2hector kill')
	        sys.exit()
	    else:    
	        if commands.getoutput('service ossec2hector status') != 'HECTOR OSSEC to MySQL import daemon server is up and running.':	             
	             for i in range(3):
	                 if i == 0:
	                     os.system('service ossec2hector start')
	                     if commands.getoutput('service ossec2hector status') == 'HECTOR OSSEC to MySQL import daemon server is up and running.':
	                         break
	                     else:
	                         sendMail('OSSEC2HECTOR Started By Monitor', 'OSSEC2HECTOR could not be started, will retry in 5 minutes.')
	                         time.sleep(300)
	                 if i == 1:
	                     os.system('service ossec2hector start')
	                     if commands.getoutput('service ossec2hector status') == 'HECTOR OSSEC to MySQL import daemon server is up and running.':
	                         break
	                     else:
	                         sendMail('OSSEC2HECTOR Started By Monitor', 'OSSEC2HECTOR could not be started, will retry in 10 minutes.')
	                         time.sleep(600)
	                 if i == 2:
	                     os.system('service ossec2hector start')
	                     if commands.getoutput('service ossec2hector status') == 'HECTOR OSSEC to MySQL import daemon server is up and running.':
	                         break
	                     else:
	                         sendMail('OSSEC2HECTOR Started By Monitor', 'OSSEC2HECTOR could not be started, will retry in 15 minutes.')
	                         time.sleep(900)
	                         os.system('service ossec2hector start')
	                         if commands.getoutput('service ossec2hector status') != 'HECTOR OSSEC to MySQL import daemon server is up and running.':
	                             sendMail('OSSEC2HECTOR Stopped By Monitor', 'OSSEC2HECTOR could not be started, requires manual restart. Once restarted, if the script still does not work, delete the file /tmp/hector_dead')
	                             fdead = file("/tmp/hector_dead", "w")
	                             os.system('service ossec2hector kill')

