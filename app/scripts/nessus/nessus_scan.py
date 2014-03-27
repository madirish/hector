#!/usr/bin/python

from NessusXMLRPC import Scanner

x = Scanner( "probe.security.isc.upenn.edu", 8834, login="jamed", password="")

reports = x.reportList()

down = x.reportDownload(report="50932216-f1da-d52f-8232-de3631335c05d8b6a008443c72e1", version="v2")

f = open('vuln.xml', 'w')
f.write(down)
f.close()