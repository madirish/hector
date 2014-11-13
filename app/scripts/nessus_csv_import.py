#!/usr/bin/python
#  nessus_csv_import.py
#  Part of the HECTOR project, powered by UPENN SAS
#  @author Colleen Blaho <cblaho@sas.upenn.edu>
#  30.10.2014
#  

###IMPORT####
import csv
import sys
import getopt
import time
import ConfigParser
import socket

#Not a builtin!
try:
	import MySQLdb
except ImportError:
	print "FATAL! MySQL connector not found."
	sys.exit(1)
###############

timestamp = ''
cur = '' 
db = ''

def main(argv):
    """
    
    The backbone of the program. Does the following:
    1. Parses and sanity checks args.
    2. Connects to the database.
    3. Reads lines with the csv reader and passes them to the processor.
    4. Handles errors in the above.
    5. Program cleanup. 
    
    To decide if a vulnerability is a duplicate, we compare the name and 
    the CVE number. If both of these match, then it is assumed to be a duplicate.
    """
    #defined as globals so we don't have to pass them through multiple functions
    global cur
    global db
    global timestamp
    exitCode = 0 # we assume everything will be just fine
    
    inputfile = ''
    print time.strftime("%Y-%m-%d %H:%M:%S") + " NESSUS IMPORTER FOR HECTOR "
    # Parsing arguments
    try:
        opts, args = getopt.getopt(argv,"i:t:h",["inputfile=","timestamp="])
    except getopt.GetoptError:
        print 'Hector Importer -i <inputfile> -t <timestamp>'
        sys.exit(1)
    for opt, arg in opts:
        if opt == '-h':
            print 'Hector Importer -i <inputfile> -t <timestamp>'
            print ' Time Format: "YYYY-MM-DD_24HR:MIN:SEC" '
            sys.exit()
        elif opt in ("-i", "--inputfile"):
            inputfile = arg
        elif opt in ("-t", "--timestamp"):
            timestamp = arg
    if inputfile == '':
        print "FATAL! CSV file mandatory. -i <inputfile>"
        sys.exit(1)
    if timestamp == '':
        print "WARNING! Timestamp will be autofilled by MySQL."
    else:
        try:
            time_tuple = time.strptime(timestamp, "%Y-%m-%d_%H:%M:%S")
            #time will fill in the missing information
            timestamp = time.strftime("%Y-%m-%d %H:%M:%S", time_tuple)
            #time will be okay with an incomplete timestamp but SQL won't.
        except ValueError, e:
            print "Invalid timestamp. \"%Y-%m-%d_%H:%M:%S\""
            print "Mind the underscore between the date and time."
            sys.exit(1)
    #Parse out 
    config = ConfigParser.ConfigParser()
    
    try:
        config.read('/opt/hector/app/conf/config.ini')
        db_database = config.get('hector', 'db')
        db_host = config.get('hector', 'db_host')
        db_user = config.get('hector', 'db_user')
        db_pass = config.get('hector', 'db_pass')
        db = MySQLdb.connect(host=db_host, user=db_user, passwd=db_pass, db=db_database)
    except:
        print "FATAL! Database connection failed."
        sys.exit(1)
    cur = db.cursor()
    f = open(inputfile, 'rb')
    reader = csv.reader(f)
    for row in reader:
        try:
            if len(row) == 13: #sanity check the line in file
                process_row(row, cur)
            else:
                raise csv.Error('Incomplete record')
                exitCode = 2
        except csv.Error, e:
            print 'WARNING: Invalid record at line %d: %s' \
                    % (reader.line_num, str(e))
            exitCode = 2
    f.close()
    cur.close()
    db.close()
    print time.strftime("%Y-%m-%d %H:%M:%S") + " All good records inserted! Check output messages for errors."
    sys.exit(exitCode)
    

def process_row(row, cur):
    """

    process_row handles the tuples produced by the csv reader, and 
    handles all errors from the actual database functions. 
    Error handling is print messages and return to the for loop, so that we can 
    recover from broken records. The goal is to insert as many good vulns
    and/or records as possible, and alert the user to the malformed records.
    
    db.commit() happens after every successful insertVuln()/insertHost(), and every 
    successful insertInstance().
    Row Header:Plugin ID,CVE,CVSS,Risk,Host,Protocol,Port,Name,Synopsis,Description,Solution,See Also,Plugin Output
    Sample Row: 
    
        10881,,,None,192.168.1.5,tcp,22,SSH Protocol Versions Supported,A SSH server is running on the remote host.,
        "This plugin determines the versions of the SSH protocol supported by 
        the remote SSH daemon.",n/a,,"The remote SSH daemon supports the following versions of the
        SSH protocol :-X.XX- X.X SSHv2 host key fingerprint : XX:02:XX:07:54:05:b0:XX:4b:dd:88:XX:43:ae:XX:0a"

    """
    if row[3] == "None" or row[3] == "Risk": #not a vuln
        return
    pluginID,cve,cvss,risk,hostName,protocol,port,vulnName,vulnDescription,longDescription,solution,url,pluginOutput = row
    textString = "<div id=\"protocol\">Protocol: " + protocol + "</div> \
                 <div id=\"port\">Port: " + port + "</div> \
                 <div id=\"detailed-explanation\">More Details: " + longDescription + "</div> \
                 <div id=\"plugin-output\">Plugin Output: " + pluginOutput + "</div>"
    descString = "<div id=\"description\">DESCRIPTION: " + vulnDescription + "</div>\
                 <div id=\"cvss-score\">CVSS: " + cvss + "</div> \
                 <div id=\"solution\">Solution: " + solution + "</div>" 
    
    if (hostName == '' or vulnName== '' or  vulnDescription == ''):
        raise csv.Error('Incomplete Record.')
        exitCode = 2
    try:
        cur.execute("SELECT host_id FROM host WHERE host_name = %s", hostName)
        hostID = cur.fetchone()
        if hostID == None:
            hostID = insertHost(hostName)
        else:
            hostID = hostID[0]
    except MySQLdb.Error, e:
        try:
            print "Host '%s' Lookup Error [%d]: %s" % (hostName, e.args[0], e.args[1])
            exitCode = 2
            return
        except IndexError:
            print "Host '%s' Lookup Error: %s" % (hostName, str(e))
            exitCode = 2
            return
    # Already SQLi hardened courtesy of .execute()
    try:
        cur.execute("SELECT vuln_id, vuln_cve FROM vuln WHERE vuln_name = %s AND vuln_cve = %s", (vulnName, cve))
        vulnID = cur.fetchone()
        if vulnID == None :
            vulnID = insertVuln(vulnName, cve, descString, url)
            db.commit()
        else:
            vulnID = vulnID[0]
        try:
            insertInstance(hostID, vulnID, textString, risk)
            db.commit()
        except MySQLdb.Error, e:
            try:
                print "Vuln '%s' Detail Error [%d]: %s" % (str(vulnID), e.args[0], e.args[1])
                exitCode = 2
                return
            except IndexError:
                print "Vuln '%s' Detail Error: %s" % (str(vulnID), str(e))
                exitCode = 2
                return 
    except MySQLdb.Error, e:
        try:
            print "Vuln '%s' Error [%d]: %s" % (vulnName, e.args[0], e.args[1])
            exitCode = 2
            return
        except IndexError:
            print "Vuln '%s' Error: %s" % (vulnName, str(e))
            exitCode = 2
            return

def insertVuln(vulnName, cve, descString, url):
    """
    Inserts vuln entries into the database. 
    Returns the ID for the vuln inserted.

    """
        
    if cve == '':
        cur.execute("INSERT INTO vuln ( \
            vuln_name, vuln_description) \
            VALUES (%s, %s)", (vulnName, descString))
    else:
        cur.execute("INSERT INTO vuln ( \
            vuln_name, vuln_description, vuln_cve) \
            VALUES (%s, %s, %s)", (vulnName, descString, cve))
    cur.execute("SELECT vuln_id FROM vuln WHERE vuln_name = %s", vulnName)
    vulnID = cur.fetchone()[0]
    if url != '':
        cur.execute("INSERT INTO vuln_url (\
            vuln_id, url) VALUES (%s, %s)", (vulnID, url))
    return vulnID

def insertInstance(hostID, vulnID, textString, risk):
    """
    Inserts specific instances of the vulns. 
    No return value.

    """
    cur.execute("SELECT risk_id FROM risk WHERE risk_name = %s", risk.lower())
    riskID = cur.fetchone()
    if riskID == None:
        cur.execute("INSERT INTO risk (risk_name) VALUES (%s)", risk.lower())
        cur.execute("SELECT risk_id FROM risk WHERE risk_name = %s", risk.lower())
        riskID = cur.fetchone()[0]
    else:
        riskID = riskID[0]
    if timestamp == '':
        cur.execute("INSERT INTO vuln_detail ( \
            host_id, vuln_id, vuln_detail_text, risk_id) VALUES (%s, %s, %s, %s)", (hostID, vulnID, textString, riskID))
    else:
        cur.execute("INSERT INTO vuln_detail ( \
                host_id, vuln_id, vuln_detail_datetime, vuln_detail_text, risk_id) VALUES (%s, %s, %s, %s, %s)", 
                (hostID, vulnID, timestamp, textString, riskID))

def insertHost(hostName):
    """ 
    
    Inserts a skeleton record for a new host.
    Returns the ID for the host inserted.
    
    """
    try:
        hostIP = socket.gethostbyname(hostName)
        cur.execute("INSERT INTO host (host_name, host_ip, host_ip_numeric) \
        VALUES (%s, %s, INET_ATON(%s))", (hostName, hostIP, hostIP))       
    except socket.gaierror:
        #ignore column restraints
        cur.execute("INSERT INTO host (host_name) VALUES (%s)", hostName)
    
    cur.execute("SELECT host_id FROM host WHERE host_name = %s", hostName)
    hostID = cur.fetchone()[0]
    return hostID

if __name__ == "__main__":
    main(sys.argv[1:])

