#!/usr/bin/python
#
#
#
# Dump all reports in xml that exist on the server that occured within so many hours 
#	(defined by hoursdifference var)
#

import sys,subprocess,shlex,os,smtplib,logging,socket,zipfile,ConfigParser

from NessusXMLRPC import Scanner,ParseError
from datetime import timedelta,datetime
import NessusXMLRPC, argparse

#lets prepare argument parsing...

parser = argparse.ArgumentParser(description='This program will provide Nessus server status and allow downloading XML')
parser.add_argument('-hr',help='Indicate how many hours difference you would like to go back. (default 1hr)',type=int,default=1)
parser.add_argument('-min',help='Indicate how many minutes difference you would like to go back. (default 30min)',type=int,default=30)
parser.add_argument('-pol',help='Show policies present on Server',action='store_true',default=False)
parser.add_argument('-active',help='Show active scans occuring on server',action='store_true',default=False)
parser.add_argument('-reports',help='Show reports saved on server. Affected by -hr, -min arguments.',action='store_true',default=False)
parser.add_argument('-dl',help='Download reports saved on server. Affected by -hr, -min arguments.',action='store_true',default=False)
parser.add_argument('-savedir',help='Save Directory',default='Null')

results = parser.parse_args()

if results.dl and (not os.path.isdir(results.savedir) or results.savedir == 'Null'):
		print "**ERROR - You need to specify a valid folder when using the Download option\n"
		parser.print_help()
		sys.exit(1)

if not results.active and (not results.reports and not results.dl) and not results.pol:
#        raise argparse.ArgumentTypeError("**ERROR - You need to select a view or indicate you want to download xmls**")
        print "**ERROR - You need to select a view or indicate you want to download xmls**"
	parser.print_help()
	sys.exit(1)


todaysdate=datetime.today()

NessusInstance = Scanner( "server.upenn.edu", 8834, login="username", password="password")

def xml_save(scan_name,xml_contents):
	xmlfile=open(os.path.join(results.savedir,scan_name),'w')
	xml_output=xml_contents
	xmlfile.write(xml_output)
	xmlfile.close()

# make sure we log out no matter what happens, this kills the token so no one else can hijack xmlrpc session
try:
	if results.active:
		print "\n=======Active Scans:"
		try:
			scans = 0 
			for report in NessusInstance.reportList():
				if report['status'] == 'running':
					print str(report)
					continue
			if scans == 0:
				print "There are currently no running scans"
		except Exception as error:
			print error

	if results.reports or results.dl:
		print "\n=======Finished Reports ============ [Going back: %s hr(s), %s min(s)] " % (results.hr,results.min)
		try:
			reports = 0
			for report in NessusInstance.reportList():
				if report['status'] == 'completed':
					extracted_date=datetime.fromtimestamp(int(report['timestamp']))
					if todaysdate - extracted_date < timedelta(hours=results.hr,minutes=results.min):
						#print str(report)+" is potential to to be downloaded" 
						print str("Name: %s, TimeStamp: %s" % (report['readableName'],report['timestamp']))
						if results.dl:
							print "\t==saved===>  %s" % (str(os.path.join(results.savedir,report['readableName'])))
							xml_save(report['readableName'],NessusInstance.reportDownload(report['name']))
		except Exception as error:
			print error

	if results.pol:
		print "\n=======Policies Present on Server:"
	#	print NessusInstance.policyList()
		for policy in NessusInstance.policyList():
			print policy['policyName']

finally:
	NessusInstance.logout()

