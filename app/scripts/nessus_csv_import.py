#!/usr/bin/python
#  nessus_csv_import.py
#  Part of the HECTOR project, powered by UPENN SAS
#  Copyleft 2014 Colleen Blaho <cblaho@sas.upenn.edu>
#  30.10.2014
#  

###IMPORT####
import csv
import sys
import getopt
import time

#Not a builtin!
try:
	import MySQLdb
except ImportError:
	print "FATAL! MySQL connector not found."
	exit(1)
###############

timestamp = ''
cur = '' 
db = ''
def main(argv):
    global cur
    global db
    global timestamp
    
    inputfile = ''
    
    # Parsing arguments
    try:
        opts, args = getopt.getopt(argv,"i:t:h",["ifile=","timestamp="])
    except getopt.GetoptError:
        print 'Hector Importer -i <inputfile> -t <timestamp>'
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print 'Hector Importer -i <inputfile> -t <timestamp>'
            print ' Time Format: "YYYY-MM-DD_HR:MIN:SEC" '
            sys.exit()
        elif opt in ("-i", "--ifile"):
            inputfile = arg
        elif opt in ("-t", "--time"):
            timestamp = arg
    if inputfile == '':
        print "FATAL! CSV file mandatory. -i <inputfile>"
        exit(1)
    if timestamp == '':
        print "WARNING! Timestamp will be autofilled by MySQL."
    else:
        try:
            time.strptime(timestamp, "%Y-%m-%d_%I:%M:%S")
        except ValueError as e:
            print "Invalid timestamp. \"%Y-%m-%d_%I:%M:%S\""
            print "Mind the underscore between the date and time."
            exit(2)
            
    #!!!CHANGE ME IN PRODUCTION!!!#
    db = MySQLdb.connect(host='localhost', user='root', db='hector')
    ###############################
    cur = db.cursor()
    with open(inputfile, 'rb') as f:
        reader = csv.reader(f)
        for row in reader:
            try:
                if len(row) == 13: #sanity check the line in file
                    process_row(row, cur)
                else:
                    raise csv.Error('Incomplete record')
            except csv.Error as e:
                print 'WARNING: Invalid record at line %d: %s' \
                        % (reader.line_num, str(e))
    cur.close()
    db.close()
    print "All good records inserted! Check output messages for errors."
    
"""

process_row handles the tuples produced by the csv reader, and 
handles all errors from the actual database functions. 
Error handling is print messages and return to the for loop, so that we can 
recover from broken records. The goal is to insert as many good vulns
and/or records as possible, and alert the user to the malformed records.

db.commit() happens after every successful insertVuln(), and every 
successful insertInstance().

"""
def process_row(row, cur):
    if row[3] == "None" or row[3] == "Risk": #not a vuln
        return
    pluginID = row[0]
    cve = row[1]
    cvss = row[2]
    risk = row[3]
    hostName = row[4]
    protocol = row[5]
    port = row[6]
    vulnName = row[7]
    vulnDescription = row[8]
    longDescription = row[9]
    solution = row[10]
    url = row[11]
    pluginOutput = row[12]
    
    #textString = "<div id=\"cvss-score\">CVSS: " + cvss + "</div> \
                #<div id=\"risk\">Risk: " + risk + "</div> \
                #<div id=\"protocol\">Protocol: " + protocol + "</div> \
                #<div id=\"port\">Port: " + port + "</div> \
                #<div id=\"solution\">Solution: " + solution + "</div> \
                #<div id=\"detailed-explanation\">More Details: " + longDescription + "</div> \
                #<div id=\"plugin-output\">Plugin Output: " + pluginOutput + "</div>"
                
                
    textString = "PROTOCOL: " + protocol + " \
                 PORT: " + port + " \
                 MORE DETAILS: " + longDescription + " \
                 PLUGIN OUTPUT: " + pluginOutput
    descString = "DESCRIPTION: " + vulnDescription + "\
                 CVSS: " + cvss + " \
                 RISK: " + risk + " \
                 SOLUTION: " + solution 
    
    if not all([hostName, vulnName, vulnDescription]):
        raise csv.Error('Incomplete Record.')
    try:
        cur.execute("SELECT host_id FROM host WHERE host_name = %s", hostName)
        hostID = cur.fetchone()
        if hostID == None:
            raise MySQLdb.Error
        else:
            hostID = hostID[0]
    except MySQLdb.Error, e:
        try:
            print "Host '%s' Lookup Error [%d]: %s" % (hostName, e.args[0], e.args[1])
            return
        except IndexError:
            print "Host '%s' Lookup Error: %s" % (hostName, str(e))
            return
    # TODO: SQLi protection? Although I hope the people dealing with HECTOR are smart and Nessus is nice.
    try:
        cur.execute("SELECT vuln_id FROM vuln WHERE vuln_name = %s", vulnName)
        vulnID = cur.fetchone()
        if vulnID == None:
            vulnID = insertVuln(vulnName, cve, descString)
            db.commit()
        else:
            vulnID = vulnID[0]
        try:
            insertInstance(hostID, vulnID, url, textString)
            db.commit()
        except MySQLdb.Error, e:
            try:
                print "Vuln '%s' Detail Error [%d]: %s" % (str(vulnID), e.args[0], e.args[1])
                return
            except IndexError:
                print "Vuln '%s' Detail Error: %s" % (str(vulnID), str(e))
                return 
    except MySQLdb.Error, e:
        try:
            print "Vuln '%s' Error [%d]: %s" % (vulnName, e.args[0], e.args[1])
            return
        except IndexError:
            print "Vuln '%s' Error: %s" % (vulnName, str(e))
            return
    
    
            
"""
Inserts vuln entries into the database. 
Returns the ID for the vuln inserted.

"""
def insertVuln(vulnName, cve, descString):
    if cve == '':
        cur.execute("INSERT INTO vuln ( \
            vuln_name, vuln_description) \
            VALUES (%s, %s)", (vulnName, descString))
    else:
        cur.execute("INSERT INTO vuln ( \
            vuln_name, vuln_description, vuln_cve) \
            VALUES (%s, %s, %s)", (vulnName, descString, cve))
    cur.execute("SELECT vuln_id FROM vuln WHERE vuln_name = %s", vulnName)
    vulnID = cur.fetchone()
    return vulnID[0]

"""
Inserts specific instances of the vulns. 
No return value.

"""
def insertInstance(hostID, vulnID, url,textString):
    if timestamp == '':
        cur.execute("INSERT INTO vuln_detail ( \
            host_id, vuln_id, vuln_detail_text) VALUES (%s, %s, %s)", (hostID, vulnID, textString))
    else:
        cur.execute("INSERT INTO vuln_detail ( \
                host_id, vuln_id, vuln_detail_datetime, vuln_detail_text) VALUES (%s, %s, %s, %s)", 
                (hostID, vulnID, timestamp, textString))
    if url != '':
        cur.execute("INSERT INTO vuln_url (\
            vuln_id, url) VALUES (%s, %s)", (vulnID, url))
    
if __name__ == "__main__":
    main(sys.argv[1:])
