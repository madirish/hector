#!/usr/bin/env python
"""
Copyright (c) 2010 HomeAway, Inc.
All rights reserved.  http://www.homeaway.com

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
"""
import sys,subprocess,shlex,os,smtplib,logging,socket,zipfile
import xml.etree.ElementTree
import ConfigParser

from NessusXMLRPC import Scanner,ParseError
from optparse import OptionParser
from random import randint
from time import sleep
from logging.handlers import WatchedFileHandler
from datetime import datetime

from email.mime.multipart import MIMEMultipart
from email.mime.base import MIMEBase
from email.mime.text import MIMEText
from email import Encoders

from exceptions import KeyError

class Nessus:
	def __init__( self, configfile, scans ):
		"""
		@type   configfile:     string
		@param  configfile:     Full path to a configuration file for loading defaults
		@type   scans:          list
		@param  scans:          A list() of scans assembled with all necessary context
		"""
		self.logformat      = "%s %8s %s"
		self.scans_running  = []        # Scans currently running.
		self.scans_complete = []        # Scans that have completed.
		self.scans          = scans     # Scans that remain to be started.

		self.started        = False     # Flag for telling when scanning has started.

		# Parse the configuration file to set everything up
		self.config = ConfigParser.ConfigParser()
		self.config.readfp(open(configfile))

		loglevels = {   'debug'   : logging.DEBUG,
						'info'    : logging.INFO,
						'warning' : logging.WARNING,
						'error'   : logging.ERROR,
						'critical': logging.CRITICAL }
		# Core settings
		self.logfile     = self.config.get( 'core', 'logfile' )
		self.loglevel    = loglevels[self.config.get( 'core', 'loglevel' )]

		# Setup some basic logging.
		self.logger = logging.getLogger('Nessus')
		self.logger.setLevel(self.loglevel)
		self.loghandler = WatchedFileHandler( self.logfile )
		self.logger.addHandler(self.loghandler)

		self.debug( "CONF configfile = %s" % configfile )
		self.debug( "Logger initiated; Logfile: %s, Loglevel: %s" % (self.logfile,self.loglevel))
		
		self.server      = self.config.get( 'core', 'server' )
		self.debug( "CONF core.server = %s" % self.server)
		self.port        = self.config.getint( 'core', 'port' )
		self.debug( "CONF core.port = %s" % self.port)
		self.user        = self.config.get( 'core', 'user' )
		self.debug( "CONF core.user = %s" % self.user )
		self.password    = self.config.get( 'core', 'password' )
		self.debug( "CONF core.password set" )
		self.limit       = self.config.getint( 'core', 'limit' )
		self.debug( "CONF core.limit = %d" % self.limit )
		self.sleepmax    = self.config.getint( 'core', 'sleepmax')
		self.debug( "CONF core.sleepmax = %d" % self.sleepmax )
		self.sleepmin    = self.config.getint( 'core', 'sleepmin')
		self.debug( "CONF core.sleepmin = %d" % self.sleepmin )

		# SMTP settings
		self.emailto     = self.config.get( 'smtp', 'to' )
		self.debug( "CONF smtp.emailto = %s" % self.emailto )
		self.emailfrom   = self.config.get( 'smtp', 'from' )
		self.debug( "CONF smtp.emailfrom = %s" % self.emailfrom )
		self.smtpserver  = self.config.get( 'smtp', 'server' )
		self.debug( "CONF smtp.smtpserver = %s" % self.smtpserver )
		self.smtpport    = self.config.getint( 'smtp', 'port' )
		self.debug( "CONF smtp.smtpport = %d" % self.smtpport )

		# Reporting settings
		self.reports     = self.config.get( 'report', 'outputdir' )
		self.debug( "CONF report.reports = %s" % self.reports )
		self.xsltproc    = self.config.get( 'report', 'xsltproc' )
		self.debug( "CONF report.xsltproc = %s" % self.xsltproc )
		self.xsltlog     = self.config.get( 'report', 'xsltlog' )
		self.debug( "CONF report.xsltlog = %s" % self.xsltlog )
		self.xsl         = self.config.get( 'report', 'xsl' )
		self.debug( "CONF report.xsl = %s" % self.xsl )
		
		self.debug( "PARSED scans: %s" % self.scans )
		
		try:
			self.info("Nessus scanner started.")
			self.scanner = Scanner( self.server, self.port, self.user, self.password )
			self.info("Connected to Nessus server; authenticated to server '%s' as user '%s'" % (self.server,self.user))
		except socket.error as (errno,strerror):
			self.error("Socket error encountered while connecting to Nessus server: %s. User: '%s', Server: '%s', Port: %s" % (strerror,self.user,self.server,self.port))
			return None

	def start( self ):
		"""
		Proxy for resume() really. Basically begins scanning with the current scanning list.
		"""
		self.started = True
		
		if len(self.scans) > 1:
			self.info("Starting with multiple scans")
		else:
			self.info("Starting with a single scan")

		if self.scans_running is None:
			self.scans_running = []

		return self.resume()

	def stop( self ):
		"""
		We have a start() so we most certainly should have a stop(). This should prevent scans from being continued.
		"""
		self.started = False

	def resume( self ):
		"""
		Basically gets scans going, observing the limit.
		"""
		if self.started and len(self.scans) > 0 and len(self.scans_running) < self.limit:
			count = len(self.scans_running)
			for scan in self.scans:
				if self._startscan(scan):
					count += 1
				if count == self.limit:
					self.warning("Concurrent scan limit reached (currently set at %d)" % self.limit)
					self.warning("Will monitor scans and continue as possible")
					break
		return self.scans_running

	def _startscan( self, scan ):
		"""
		Start a specific scan in the scans list.
		"""
		currentscan = self.scanner.quickScan( scan['name'], scan['target'], scan['policy'] )
		if currentscan is not None:
			self.info("Scan successfully started; Owner: '%s', Name: '%s'" % (currentscan['owner'],currentscan['scan_name']))
		else:
			self.error("Unable to start scan. Name: '%s', Target: '%s', Policy: '%s'" % (scan['name'],scan['target'],scan['policy']))
			return False

		# Add the newly started scan to the running least, remove it from the remaining
		self.scans_running.append(currentscan)
		self.scans.remove(scan)
		return True

	def iscomplete( self ):
		"""
		Check for the completion of of running scans. Also, if there are scans left to be run, resume and run them.
		"""
		try:
			reports = self.scanner.reportList()
		except socket.error as (errno,strerror):
			self.error("Socket error; %s" % strerror)
			self.error("Invalidating connection and sleeping before we continue")
			self.scanner.connection.close()
			self.scanner.connection = None
			sleep(self.sleepmax)
		except ParseError as e:
                        self.error("%s; %s" % (e.info,e.contents))
                        self.error("Continuing...")
                        return False
		for scan in self.scans_running:
			try:
                                if type(reports) is dict:
                                        # We have only one report
                                        report = reports['report']
                                        if report['status'] == 'completed' and scan['uuid'] == report['name']:
                                                self.scans_complete.append(scan)
						self.scans_running.remove(scan)
				elif type(reports) is list:
                                        # We have multiple reports to look through
                                        for report in reports:
                                                if report['status'] == 'completed' and scan['uuid'] == report['name']:
                                                        self.scans_complete.append(scan)
                                                        self.scans_running.remove(scan)
			except KeyError:
				self.error("KeyError when parsing XML from reportList(); continuing")
				return False

		# Check to see if we're running under the limit and we have scans remaining.
		# If so, run more scans up to the limit and continue.
		
		self.info("Number of scans running is %s, total is %s, limit is %s" % (len(self.scans_running),len(self.scans),self.limit))
		if len(self.scans_running)<self.limit and len(self.scans)>0 and self.started:
			self.info("We can run more scans, resuming")
			self.resume()

		elif len(self.scans_running)>0:
			return False                
		else:
			return True

	def report( self ):
		"""
		Report on currently completed scans.
		"""
		consolidate_body=""
		for scan in self.scans_complete:
			pname   = scan['scan_name'].replace(' ','')

			data    = self.scanner.reportDownload( scan['uuid'] )
			xmlf    = os.path.join( self.reports, pname+'.xml' )
			htmlf   = os.path.join( self.reports, pname+'.html')
			zipf    = os.path.join( self.reports, pname+'.zip' )

			self.genreport( data, xmlf, htmlf, zipf )
			self.info("XML report saved as '%s'" % xmlf)
			self.info("HTML was not generated during this pass")
			#self.info("HTML report saved as '%s'" % htmlf)

			# Put together the text of the email with the report attached 
			# Commented out email reporting

			#self.send_report( "Report: %s" % scan['scan_name'], self.gensummary(data), zipf)
			#self.info("Email report sent to '%s' from '%s' including '%s'" % ( self.emailto,self.emailfrom,zipf))
			consolidate_body += self.gensummary(data)
			
		# try consolidating emails. This is new logic to try to reduce emails from each target in subnet sweeps
		message             = MIMEMultipart()
		message['From']     = self.emailfrom
		message['To']       = self.emailto
		#message['Subject']  = scan['scan_name']+" has completed"
		message['Subject']  = "Scan has completed"
		message.attach( MIMEText( consolidate_body ))
			#body="Your scan is done."
			#body = self.gensummary(data)
	                #message.attach( MIMEText( body ))
		conn = smtplib.SMTP(self.smtpserver, self.smtpport)
		conn.sendmail( message['From'], self.emailto, message.as_string())
		conn.close()
		self.info("Limited email notification sent to '%s' from '%s' excluding '%s'. This is disabled for now." % ( self.emailto,self.emailfrom,zipf))

	def genreport( self, data, xmlf, htmlf, zipf ):
		"""
		Simple method for transforming the XML spit out by the server into report-style HTML using
		what's available.

		@type   data:       string
		@param  data:       XML output from the report of a scan.
		@type   xmlf:       string
		@param  xmlf:       The file where the XML is to be output.
		@type   htmlf:          string
		@param  htmlf:          The file where the HTML is to be output.
		@type   zipf:       string
		@param  zipf:       The output ZipFile containing the compressed report.
		"""
		output = open( xmlf, "w")
		output.write(data)
		output.close()

		# Transform the XML using the XSL provided by Nessus for HTML reports (quietly)

		# Commented out, because I dont want no stinkin' HTML reports -- msheiny
		#xsltlog = open( self.xsltlog, 'w' )
		#subprocess.call(shlex.split("%s %s %s -o %s" % (self.xsltproc,self.xsl,xmlf,htmlf)), stdout=xsltlog, stderr=xsltlog)
		#zip = zipfile.ZipFile( zipf, 'w' )
		#zip.write(htmlf,arcname=os.path.basename(htmlf))
		#zip.close()
		#xsltlog.close()

	def gensummary( self, data ):
		"""
		Generate a simple summary as the contents of the email report to be sent.

		@type   data:   string
		@param  data:   XML data from the current report.
		"""
		severity = {    '0' : 0,
						'1' : 0,
						'2' : 0,
						'3' : 0 }
		prefs = {}
		pref = None
		count = 0

		parsed = xml.etree.ElementTree.fromstring(data)

		# Pull out the report name
		report = parsed.find("Report").attrib['name']

		# Pull out the name of the policy used
		policy = parsed.find("Policy/policyName").text

		# Parse preferences and construct a dict from all settings
		for preference in parsed.find("Policy/Preferences").getiterator("preference"):
			for child in preference.getchildren():
				if child.tag == 'name':
					prefs[child.text] = None
					pref = child.text
				elif child.tag == 'value':
					prefs[pref] = child.text
		# Parse severity for totals
		for host in parsed.find("Report").getiterator("ReportHost"):
			for item in host.getiterator("ReportItem"):
				severity[item.attrib['severity']] += 1

		return "Scan Name: %25s\nTarget(s): %25s\nPolicy: %28s\n\nRisk Summary\n%s\n%15s %3s\n%15s %3s\n%15s %3s\n\n%15s %3s" % ( report, prefs['TARGET'], policy,'-'*36,'High', severity['3'], 'Medium', severity['2'], 'Low', severity['1'], 'Open Ports', severity['0'])

	def send_report( self, subject, body, attachment, apptype='x/zip'):
		"""
		Send the email report to its destination.

		@type   to:     string
		@param  to:     Destination email address for the report.
		@type   subject:    string
		@param  subject:    The subject of the email message.
		@type   body:       string
		@param  body:       The body of the email message (includes report summary).
		@type   attachment: string
		@param  attachment: Path to report file for attaching to message.
		@type   apptype:    string
		@param  apptype:    Application MIME type for attachment.
		"""
		message             = MIMEMultipart()
		message['From']     = self.emailfrom
		message['To']       = self.emailto
		message['Subject']  = subject

		message.attach( MIMEText( body ))
		part = MIMEBase('application',apptype)
		part.set_payload( open( attachment, 'r').read())
		Encoders.encode_base64(part)
		part.add_header('Content-Disposition','attachment; filename="%s"' % os.path.basename(attachment))
		message.attach(part)

		conn = smtplib.SMTP(self.smtpserver, self.smtpport)
		conn.sendmail( message['From'], self.emailto, message.as_string())
		conn.close()

	def close( self ):
		"""
		End it.
		"""
		return self.scanner.logout()

	def debug( self, msg ):
		"""
		@type   msg:    string
		@param  msg:    Debug message to be written to the log.
		"""
		self.logger.debug( self.logformat % (datetime.now(),'DEBUG',msg))

	def info( self, msg ):
		"""
		@type   msg:    string
		@param  msg:    Info message to be written to the log.
		"""
		self.logger.info( self.logformat % (datetime.now(),'INFO',msg))

	def warning( self, msg ):
		"""
		@type   msg:    string
		@param  msg:    Warning message to be written to the log.
		"""
		self.logger.warning( self.logformat % (datetime.now(),'WARNING',msg))

	def error( self, msg ):
		"""
		@type   msg:    string
		@param  msg:    Error message to be written to the log.
		"""
		self.logger.info( self.logformat % (datetime.now(),'ERROR',msg))

	def critical( self, msg ):
		"""
		@type   msg:    string
		@param  msg:    Critical message to be written to the log.
		"""
		self.logger.critical( self.logformat % (datetime.now(),'CRITICAL',msg))


#############################################################################################################

if __name__ == "__main__":
	"""
	The goal with this tool is to essentially replace the command-line versions of the Nessus scanner. I
	found with the latest version that they've deprecated version 1 of the Nessus XML output preventing
	policies exported directly through the web interface from being used with the command-line versions to
	automate scans. This tool is an example using the NessusXMLRPC module I've also written to completely
	automate scans using the Nessus server. For more info, review the help/usage information available.

	nessus.py and NessusXMLRPC were written under Python v2.6.5 with xsltproc available in the PATH. Feel
	free to tweak the default concurrent scanning limit in configuration file; what's currently set is
	what worked the best on my test box. The Nessus daemon appears to be touchy when it comes to resources.
	"""
	parser = OptionParser()
	parser.add_option("-t", dest='target', help="target string for Nessus scan")
	parser.add_option("-n", dest='name', default="No-name Auto Scan", help="name for the scan")
	parser.add_option("-p", dest='policy', help="policy (on server-side) to use in the scan")
	parser.add_option("-f", dest='infile', help="input file with multiple scans to run")
	parser.add_option("-c", dest='configfile', default='nessus.conf', help="configuration file to use")

	(options,args) = parser.parse_args()

	if  options.configfile is not None and \
		(options.infile is not None or options.target is not None):

		if options.infile is not None and options.target is None:
			# Start with multiple scans.
			scans = []
			f = open(options.infile, "r")
			for line in f:
				scan = line.strip().split(',')
				scans.append({'name':scan[0],'target':scan[1],'policy':scan[2]})
			x = Nessus( options.configfile, scans )
			scans = x.start()
		elif options.target is not None and options.infile is None:
			# Start with a single scan.
			if options.name is not None and \
			   options.target is not None and \
			   options.policy is not None:
				scan = [{ 'name' : options.name, 'target' : options.target, 'policy' : options.policy }]
				x = Nessus( options.configfile, scan )
				scans = x.start()

			else:
				print "HARD ERROR: Incorrect usage.\n"
				parser.print_help()
				sys.exit(1)
		while True:
			if scans is None:
				break
			sleeptime = randint(x.sleepmin,x.sleepmax)
			x.info("Sleeping for %d seconds, polling for scan completion" % sleeptime)
			sleep(sleeptime)
			# MSHEINY - added additional login because I kept getting kicked out
			x.scanner.login(x.user,x.password)
			if x.iscomplete():
				x.report()
				break
		x.info("All done; closing")
		x.close()
		sys.exit(0)
	else:
		parser.print_help()
		sys.exit(0)
