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
        opts, args = getopt.getopt(argv,"i:t:",["ifile=","timestamp="])
    except getopt.GetoptError:
        print 'Hector Importer -i <inputfile> -t <timestamp>'
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print 'Hector Importer -i <inputfile> -t <timestamp>'
            print ' Time Format: %a %b %d %H:%M:%S %Y '
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
            timestamp = time.strptime(timestamp)
            timestamp = time.strftime( "%Y-%b-%d %H:%M:%s", timestamp)
        except ValueError:
            print "Invalid timestamp."
            exit(2)
            
    #!!!CHANGE ME IN PRODUCTION!!!#
    db = MySQLdb.connect(host='localhost', user='root', db='hector')
    ###############################
    cur = db.cursor()
    with open(inputfile, 'rb') as f:
        reader = csv.reader(f)
        for row in reader:
            try:
                process_row(row, cur)
            except csv.Error as e:
                print 'WARNING: Invalid record at line %d: %s' \
                        % (reader.line_num, str(e))
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
    #print row
    cve = row[1]
    hostName = row[4]
    vulnName = row[7]
    vulnDescription = row[8]
    url = row[12]
    if not all([hostName, vulnName, vulnDescription]):
        raise csv.Error
    try:
        hostID = cur.execute("SELECT host_id FROM host WHERE host_name = %s", hostName)
        if hostID == 0:
            raise MySQLdb.Error
    except MySQLdb.Error, e:
        try:
            print "Host '%s' Lookup Error [%d]: %s" % (hostName, e.args[0], e.args[1])
            return
        except IndexError:
            print "Host '%s' Lookup Error: %s" % (hostName, str(e))
            return
    # TODO: SQLi protection? Although I hope the people dealing with HECTOR are smart and Nessus is nice.
    try:
        vulnID = cur.execute("SELECT vuln_id FROM vuln WHERE vuln_name = %s", vulnName)
        if vulnID == 0:
            vulnID = insertVuln(vulnName, cve, vulnDescription)
            db.commit()
        try:
            insertInstance(hostID, vulnID, url)
            db.commit()
        except MySQLdb.Error, e:
            try:
                print "Vuln '%s' Detail Error [%d]: %s" % (str(vuln_id), e.args[0], e.args[1])
                return
            except IndexError:
                print "Vuln '%s' Detail Error: %s" % (str(vuln_id), str(e))
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
def insertVuln(vulnName, cve, vulnDescription):
    if cve == '':
        cur.execute("INSERT INTO vuln ( \
            vuln_name, vuln_description) \
            VALUES (%s, %s)", (vulnName, vulnDescription))
    else:
        cur.execute("INSERT INTO vuln ( \
            vuln_name, vuln_description, vuln_cve) \
            VALUES (%s, %s, %s)", (vulnName, vulnDescription, cve))
    vulnID = cur.execute("SELECT vuln_id FROM vuln WHERE vuln_name = %s", vulnName)
    return vulnID

"""
Inserts specific instances of the vulns. 
No return value.

"""
def insertInstance(hostID, vulnID, url):
    if timestamp == '':
        cur.execute("INSERT INTO vuln_detail ( \
            host_id, vuln_id, vuln_detail_text) VALUES (%s, %s, %s)", (hostID, vulnID, "Derp derp derp"))
    else:
        cur.execute("INSERT INTO vuln_detail ( \
                host_id, vuln_id, vuln_detail_datetime) VALUES (%s, %s, %s)", 
                hostID, vulnID, timestamp)
    if url == '':
        cur.execute("INSERT INTO vuln_url (\
            vuln_id, vuln_url) VALUES (%s, %s)", int(vuln_id), url)
    
if __name__ == "__main__":
    main(sys.argv[1:])
