#!/usr/bin/python
#
#
#
#
# This program serves as a parser for xml files produced by the Nessus Vulnerability Scanner. 
# If you want to actually do a scan, you are looking in the wrong place. (There's another script for that)
# 2011 - msheiny
# ---- Feb 2012 - made tweaks for new Nessus 5.0 xml parsing, cleaned up some logic to implement more DRY

import pynessus, dotnessus_v2, os, sys, argparse, textwrap, csv
from collections import defaultdict
from operator import itemgetter


#fix for some ascii encoding issues on plugin outputs
reload(sys)
sys.setdefaultencoding('utf-8')

# some default variables ####
xml_scan_dir = False
crit_vulns=0
vuln_systems=0
port_freq=defaultdict(int)
vuln_port_freq=defaultdict(int)
vuln_freq=defaultdict(int)
os_stats=defaultdict(int)
debug=False
length=70 #banner length

# plugin ids to ignore
# 10180 equals host not pingable
standard_plugin_ignore_list=['10180','19506']

# performing argument building and parsing routine
def xml_tester(file_or_dir):
	global xml_scan_dir
	if os.path.isdir(file_or_dir):
		xml_scan_dir=True
	elif not os.path.isfile(file_or_dir):
		raise argparse.ArgumentTypeError('%s is not a valid directory or file!' % file_or_dir)
	return file_or_dir

def string_size(string):
	if len(string) >= 20:
		raise argparse.ArgumentTypeError('%s is too long. You can only search with 20 characters or less.' % string)
	return string

parser = argparse.ArgumentParser(description='This program will parse through already conducted scan result files and give you a nicer report')
parser.add_argument('-xml',required=True,help='Indicate the XML file you would like to parse',type=xml_tester,dest='xml_location')
parser.add_argument('-sevplus',default='1',choices='01234',help='Minimum severity level of severity to report [choose from 0,1,2,3, or 4] . Defaults to 1',dest='severity')
parser.add_argument('-seveq',default='not_defined',choices='01234',help='Only report severity level signified [choose from 0,1,2,3, or 4].',dest='severityeq')
parser.add_argument('-showports',action='store_true',help='Show the ports of the hosts that are open. Typically this is not shown.',default=False,dest='showports')
parser.add_argument('-host',help='Indicate a particular host from the xml file that you would like to filter to',dest='target')
parser.add_argument('-winonly',help='Only show Windows machines.',dest='Just_Windows',action='store_true',default=False)
parser.add_argument('-linuxonly',help='Only show Linux-like machines (including macsand unix).',dest='Just_Linux',action='store_true',default=False)
parser.add_argument('-printersonly',help='Only Show Printers.',dest='Just_Print',action='store_true',default=False)
parser.add_argument('-osmatch',help='String match on a particular word in the operating system type',type=string_size,dest='os_string_match',)
parser.add_argument('-includeplugin',help='Explicitly include the following plugins (no matter the severity).',dest='inc_plugin',action='append')
parser.add_argument('-hide',help='Leave out some of the extra banner formatting for script parsing',dest='hidebanner',action='store_true')
parser.add_argument('-stats',help='Add some nice statistics at the end',dest='stats',action='store_true')
parser.add_argument('-allstats',help='Add some nice statistics at the end',dest='allstats',action='store_true')
parser.add_argument('-onlystats',help='Only Show Statistics',dest='onlystats',action='store_true',default=False)
parser.add_argument('-debug',help='DEBUG output for editing and troubleshooting script ONLY!',dest='debug',action='store_true',default=False)
parser.add_argument('-targetport',help='Only show vulnerabilities for your designated port',dest='onlythisport',type=int,default=None)
parser.add_argument('-syshasport',help='Only show systems that include this open port',dest='syshasport',type=int,default=None)
parser.add_argument('-notport',help='Do not show any alerts for the port specified',dest='notthisport',type=int,default=None)
parser.add_argument('-notplugin',help='Do not show any alerts for the plugin ID specified',dest='notthisid',action='append',default=standard_plugin_ignore_list)
parser.add_argument('-notfamily',help='Ignore plugins that belong to a certain family',dest='notthisfamily',action='append',default=[])


results = parser.parse_args()
# end parse ####
if results.debug:
	debug=True

## start of program definitions ###################################
def reformat(input):
	dedent = textwrap.dedent(input).strip()
	out = textwrap.fill(dedent,initial_indent="\t   ", subsequent_indent="\t   ")
	return out

def vulnoutputer(port,sev,service_name,protocol,plugin_name,description,plugin_out,solution,plugID):
	starting_output = "\n\n Port [%s] Service: %s | Protocol: %s | Plugin ID#: %s | Severity : %s\n\t-Plugin Name: %s\n\t-Description:\n%s\n\t-Plugin_Output\n%s" % (port,service_name,protocol,plugID,sev,plugin_name,reformat(description),reformat(plugin_out))	
	if "n/a" in solution.lower() or "none" in solution.lower():
		return	starting_output
	else:
		return (starting_output+"\n\t-Solution:\n%s" % (reformat(solution)))

def this_characteristic_nothere(true_or_false,dict_defined,system,os_string,filter_name):
	counter = 0
	if true_or_false:
		for os in dict_defined:
			if os.lower() in os_string:
				counter += 1
		if counter == 0:
			if debug: print "%s has been skipped b/c of %s filtering" % (system,filter_name)
			return True
	return False
		


def reporter(full_xml_path):
	global vuln_systems
	severity_filter=results.severity

	# Start a parsing instance with the dotnessus_v2 class
	rpt = dotnessus_v2.Report()

        try:
		# Perform the parse and start looping through systems and vulnerabilities
		if debug: print "About to Parse XML %s " % (full_xml_path)
                rpt.parse(full_xml_path)
		if debug: print "Print before system parse of targets ...." 

		# Iterate through each system listed in a report, first round excludes skips systems that dont match our filter
		#
                for system in rpt.targets:

			if debug: print "Now Filtering System %s " % (system.name)
			# if a particular target is being filtered for and not the right system, continue to next iteration
			if results.target and str(results.target) not in str(system):
				if debug: print "%s has been skipped b/c of target filtering" % system.name
				continue
			
			if system.get('operating-system'):
				oper_sys=system.get('operating-system').lower()
			else:
				oper_sys='Unknown'

			if debug: print "Now Filtering System %s - loop1" % (system.name)

			# check system os matching set in arguments 
			if this_characteristic_nothere(results.os_string_match,[results.os_string_match],system.name,oper_sys,'Custom User'):
				continue
			if this_characteristic_nothere(results.Just_Windows,['windows'],system.name,oper_sys,'Windows'):
				continue
			if this_characteristic_nothere(results.Just_Linux,['linux','mac','unix'],system.name,oper_sys,'Linux'):
				continue
			if this_characteristic_nothere(results.Just_Print,['print','jetdirect'],system.name,oper_sys,'Printer'):
                                continue

			# filter out systems that have include a particular port open
			if results.syshasport:
				sys_has_port_check=0
				for v in system.vulns:
					plug_family= v.get('pluginFamily')
					if v.get('severity') == '0' and str(v.get('port')) == str(results.syshasport) and ( plug_family == '' or plug_family == 'Port scanners'):
						sys_has_port_check += 1
						if debug: print "%s added because it has port %s open," \
						"and your arguments are looking for it" % (system.name,str(results.syshasport))
				if sys_has_port_check == 0:
					if debug: print "%s skipped because it does not have port %s open," \
					"and your arguments are looking for it" % (system.name,str(results.syshasport))
					continue
						

			if debug: print "Now Filtering System %s - loop2" % (system.name)

                        vuln_output=''
			port_output=''
			global crit_vulns, vuln_systems,length
                        crit_vulns=0
			port_target=0

			if debug: print "%s passed first round filters, now going thru vulns " % (system.name)
			
			# Iterate through each vulnerability listed in a system, first section deals with skipping stuff not in filter
			#
                        for v in system.vulns:
				port = v.get('port')
				plugin_id=str(v.plugin_id)
				
				# skip plugin IDs that are listed in beginning and added in arguments
				if v.plugin_id in results.notthisid:
					if debug: print "Plugin ID# %s has been skipped..." % (plugin_id)
					continue

				service_name = v.get('svc_name')
				proto= v.get('protocol')
				plugin_name=v.get('plugin_name')
				descript=str(v.get('description'))
				plugin_output=str(v.get('plugin_output'))
#				plugin_orig=v.get('plugin_output')
#				plugin_output=plugin_orig.encode('utf-8')
				solution=str(v.get('solution'))
				severity=v.get('severity')
				plug_family= v.get('pluginFamily')


				# ignore ports indicate on argument
				if str(port) == str(results.notthisport):
					continue

				#ignore vuln that does not include a specific port
				if results.onlythisport:
					if str(port) != str(results.onlythisport) and not \
					(severity == '0' and ( plug_family == '' or plug_family == 'Port scanners')):
						if debug: print "Vulnerability skipped on port %s because it didn't match %s" % (str(port),str(results.onlythisport))
						continue
						
				appendme = vulnoutputer(port,severity,service_name,proto,plugin_name,descript,plugin_output,solution,plugin_id)

				# Get Tally for Statistics
				if results.stats or results.onlystats:
					if v.get('severity') != '0' and v.get('severity') >= severity_filter:
						vuln_port_freq[v.get('port')] += 1
						vuln_freq[v.get('severity')] += 1
					else:
						port_freq[v.get('port')] += 1

				# Display the system ports if the user requested the -showports flag, otherwise 
				# check if vulnerability matches level requested
				if severity == '0' and results.showports and ( plug_family == '' or plug_family == 'Port scanners'): 
						port_output+= "\n"+"%s\t %s\t\t%s\t\t%s".center(length) % (v.get('port'),proto,service_name,plugin_name)
                                elif ((v.get('severity') >= severity_filter and results.severityeq == 'not_defined') \
			  			or (severity == results.severityeq and results.severity != 'not_defined')): 
						os_stats[oper_sys] += 1
						if debug: print "%s system.name has a severity of %s" % (system.name,severity)
					        crit_vulns+= 1
						vuln_output+= appendme

				# if you want some plugin included that doesn't match anything else
				elif results.inc_plugin and v.get('plugin_id') in results.inc_plugin:
				        crit_vulns+= 1
					vuln_output+= appendme

                        if crit_vulns >= 1 or port_target >= 1:
				vuln_systems+=1
				#checking if the only stats flag is enabled
				if not results.onlystats:
					# hide some of the banners to help out with parsing output
					sys_strings=(oper_sys.rstrip()).replace('\n',' | ')
					if not results.hidebanner:

						if system.get('host-ip') and str(system.get('host-fqdn')) and str(system.get('operating-system')):
							banner='\n'+'%s (%s)'.center(length,'=') % (system.get('host-ip'),str(system.get('host-fqdn')))
							banner+='\n'+'OS: %s'.center(length,'-') % (sys_strings)
							if len(sys_strings) > length:
								length = len(sys_strings)+4
						else:
							banner='System : %s'.center(length,'=') % (system.name)
		                                print (banner)
						if results.showports:
							print "\n"+"Port #\tProtocol\tService Name".center(length+10)+port_output
					else:
						print "%s - %s" % (system.get('host-ip'),sys_strings)
	                                #print vuln_output.rsplit('\n',1)[0]
	                                print vuln_output

        except AttributeError as error:
		print "************** Ran into an attribute error ***********\n %s" % (error)
                ohno=1
#		print full_xml_path+" did not have any entries!"

def stat_shower(title,item_header,report_suffix,dictionary):
	print "\n\n"+title+"\n"
	sort_output=[]
	col_num=3
	zebra = 1
	just=20
	total=0
	#for item in dictionary:
	for item in sorted(dictionary, key=dictionary.get):
		# lets make columns!
		total += dictionary[item]
		if zebra == col_num:
			zebra=1
			ender='\n'
		else:
			zebra += 1
			ender=''

		if item != '0':
			print "%s %s: %s %s".ljust(just) % (item_header,item,dictionary[item],report_suffix)+ender,
		else:
			print "Non-Port Specific: %s %s".ljust(just) % (dictionary[item],report_suffix)+ender,
	print "\nTotal = %s" % (total)

def main():
	# Either run a report over all the files 
	# in a directory or at a particular file
	if xml_scan_dir:
		for xml_file in os.listdir(results.xml_location):
			reporter(os.path.join(results.xml_location+xml_file))
	else:
		reporter(results.xml_location)

	# Report that no vulnerable systems were detected, otherwise exit code with
	# the number of vulnerable systems
	if vuln_systems == 0:
		print "No systems matched the vulnerabilities you were filtering for"
		sys.exit(0)
	else:
		if results.stats or results.onlystats:
			cent=100
			print "\n"+("*"*len('statistics')).center(cent)
			print "Statistics".center(cent)
			print ("*"*len('statistics')).center(cent)
			print "\tSystems affected : %s" % (vuln_systems)
			if vuln_systems >= 2 and results.allstats:
				stat_shower("Open Ports (all systems):","Port","system(s)",port_freq)
			stat_shower("Severity Items:","Level","",vuln_freq)
			stat_shower("Vulnerabilities:","Port","Items",vuln_port_freq)
			stat_shower("Operating Systems:","OS","",os_stats)

		sys.exit(vuln_systems)


############### end of definitions ############################################################################
if __name__ == "__main__":
	main()
