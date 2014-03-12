#!/usr/bin/python
# Author: James Davis <jpd838@gmail.com> 
# Modified by: Justin C. Klein Keane <jukeane@sas.upenn.edu>
#

import os, time, datetime, smtplib, sys, commands

def sendMail(message, status):
    sender = 'root'
    receiver = 'root'
    s = smtplib.SMTP('localhost')
    s.sendmail(sender, receiver, 'Subject: %s\r\n%s' % (message, status))
    s.quit()            
    
pidfile = '/tmp/hectorcheck.pid'
hectorrunning = 'HECTOR OSSEC to MySQL import daemon server is up and running.'
starthector = '/sbin/service ossec2hector start'
killhector = '/sbin/service ossec2hector kill'
hectorstatus = '/sbin/service ossec2hector status'

open(pidfile, 'a').close()

if os.path.isfile(pidfile):
	if commands.getoutput("cat " + pidfile) == os.getpid():
		sys.exit()
	else:
	    os.system(starthector)
	    if commands.getoutput(hectorstatus) == hectorrunning:
	    	if os.path.isfile("/tmp/hector_dead"):
        		os.remove('/tmp/hector_dead')
	    fpid = file("/tmp/hectorcheck.pid", "w")
	    fpid.write(str(os.getpid()))
	    if os.path.isfile("/tmp/hector_dead"):
	        os.system('service ossec2hector kill')
	        sys.exit()
	    else:    
	        if commands.getoutput(hectorstatus) != hectorrunning:	             
	             for i in range(3):
	                 if i == 0:
	                     os.system(starthector)
	                     if commands.getoutput(hectorstatus) == hectorrunning:
	                         break
	                     else:
	                         sendMail('OSSEC2HECTOR Started By Monitor', 'OSSEC2HECTOR could not be started, will retry in 5 minutes.')
	                         time.sleep(300)
	                 if i == 1:
	                     os.system(starthector)
	                     if commands.getoutput(hectorstatus) == hectorrunning:
	                         break
	                     else:
	                         sendMail('OSSEC2HECTOR Started By Monitor', 'OSSEC2HECTOR could not be started, will retry in 10 minutes.')
	                         time.sleep(600)
	                 if i == 2:
	                     os.system('service ossec2hector start')
	                     if commands.getoutput(hectorstatus) == hectorrunning:
	                         break
	                     else:
	                         sendMail('OSSEC2HECTOR Started By Monitor', 'OSSEC2HECTOR could not be started, will retry in 15 minutes.')
	                         time.sleep(900)
	                         os.system('service ossec2hector start')
	                         if commands.getoutput(hectorstatus) != hectorrunning:
	                             sendMail('OSSEC2HECTOR Stopped By Monitor', 'OSSEC2HECTOR could not be started, requires manual restart. Once restarted, if the script still does not work, delete the file /tmp/hector_dead')
	                             fdead = file("/tmp/hector_dead", "w")
	                             os.system(killhector)

