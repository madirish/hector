#!/usr/bin/python

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

import sys
import xml.etree.ElementTree

from httplib import HTTPSConnection,CannotSendRequest,ImproperConnectionState
from urllib import urlencode
from random import randint
from time import sleep

from exceptions import Exception

# Arbitary minimum and maximum values for random sequence num
SEQMIN = 10000
SEQMAX = 99999

# Simple exceptions for error handling
class NessusError(Exception):
    """
    Base exception.
    """
    def __init__( self, info, contents ):
        self.info = info
        self.contents = contents

class RequestError(NessusError):
    """
    General requests.
    """
    pass

class LoginError(NessusError):
    """
    Login.
    """
    pass

class PolicyError(NessusError):
    """
    Policies.
    """
    pass

class ScanError(NessusError):
    """
    Scans.
    """
    pass

class ReportError(NessusError):
    """
    Reports.
    """
    pass

class ParseError(NessusError):
    """
    Parsing XML.
    """
    pass

class Scanner:
    def __init__( self, host, port, login=None, password=None):
        """
        Initialize the scanner instance by setting up a connection and authenticating
        if credentials are provided. 

        @type   host:       string
        @param  host:       The hostname of the running Nessus server.
        @type   port:       number
        @param  port:       The port number for the XMLRPC interface on the Nessus server.
        @type   login:      string
        @param  login:      The username for logging in to Nessus.
        @type   password:   string
        @param  password:   The password for logging in to Nessus.
        """
        self.host = host
        self.port = port
        self.connection = self._connect( host, port )
        self.headers = {"Content-type":"application/x-www-form-urlencoded","Accept":"text/plain"}

        if login != None and password != None:
            self.login( login, password )
    
    def _connect( self, host, port ):
        """
        Internal method for connecting to the target Nessus server.

        @type   host:       string
        @param  host:       The hostname of the running Nessus server.
        @type   port:       number
        @param  port:       The port number for the XMLRPC interface on the Nessus server.
        """
        self.connection = HTTPSConnection( host, port )

    def _request( self, method, target, params ):
        """
        Internal method for submitting requests to the target Nessus server, rebuilding
        the connection if needed.

        @type   method:     string
        @param  method:     The HTTP verb/method used in the request (almost always POST).
        @type   target:     string
        @param  target:     The target path (or function) of the request.
        @type   params:     string
        @param  params:     The URL encoded parameters used in the request.
        """
        try:    
            if self.connection is None:
                self._connect( self.host, self.port )
            self.connection.request( method, target, params, self.headers )
        except CannotSendRequest,ImproperConnectionState:
            self._connect( self.host, self.port)
            self.login( self.login, self.password )
            self._request( method, target, params, self.headers )
        return self.connection.getresponse().read()

    def _rparse( self, parsed ):
        """
        Recursively parse XML and generate an interable hybrid dictionary/list with all data.

        @type   parsed:     xml.etree.ElementTree.Element
        @param  parsed:     An ElementTree Element object of the parsed XML.
        """
        result = dict()
        # Iterate over each element
        for element in parsed.getchildren():
            # If the element has children, use a dictionary
            children = element.getchildren()
            if len(children) > 0:
                # We have children for this element
                if type(result) is list:
                    # Append the next parse, we're apparently in a list()
                    result.append(self._rparse( element ))
                elif type(result) is dict and result.has_key(element.tag):
                    # Change the dict() to a list() if we have multiple hits
                    tmp = result
                    result = list()
                    # Iterate through the values in the dictionary, adding values only
                    # - This reduces redundancy in parsed output (no outer tags)
                    for val in tmp.itervalues():
                        result.append(val)
                else:
                    result[element.tag] = dict()
                    result[element.tag] = self._rparse( element )
            else:
                result[element.tag] = element.text
#	print result
        return result
            
    def parse( self, response ):
        """
        Parse the XML response from the server.

        @type   response:   string
        @param  response:   Response XML from the server following a request.
        """
        # Okay, for some reason there's a bug with how expat handles newlines
        try:
            return self._rparse( xml.etree.ElementTree.fromstring(response.replace("\n","")) )
        except Exception:
            raise ParseError( "Error parsing XML", response )

    def login( self, login, password, seq=randint(SEQMIN,SEQMAX) ):
        """
        Log in to the Nessus server and preserve the token value for subsequent requests.

        @type   login:      string
        @param  login:      The username for logging in to Nessus.
        @type   password:   string
        @param  password:   The password for logging in to Nessus.
        @type   seq:        number
        @param  seq:        A sequence number that will be echoed back for unique identification (optional).
        """
        self.username = login
        self.password = password

        params      = urlencode({ 'login':self.username, 'password':self.password, 'seq':seq})
        response    = self._request( "POST", "/login", params )
        parsed      = self.parse( response )

        if parsed['status'] == "OK":
            contents        = parsed['contents']
            self.token      = contents['token']     # Actual token value
            user            = contents['user']      # User dict (admin status, user name)
            self.isadmin    = user['admin']         # Is the logged in user an admin?
        
            self.headers["Cookie"] = "token=%s" % self.token    # Persist token value for subsequent requests
        else:
            raise LoginError( "Unable to login", contents )

    def logout( self, seq=randint(SEQMIN,SEQMAX) ):
        """
        Log out of the Nessus server, invalidating the current token value. Returns True if successful, False if not.

        @type   seq:        number
        @param  seq:        A sequence number that will be echoed back for unique identification (optional).
        """
        params      = urlencode( {'seq':seq} )
        response    = self._request( "POST", "/logout", params)
        parsed      = self.parse( response )

        if parsed['status'] == "OK" and parsed['contents'] == "OK":
            return True
        else:
            return False
        
    def policyList( self, seq=randint(SEQMIN,SEQMAX) ):
        """
        List the current policies configured on the server and return a dict with the info.

        @type   seq:        number
        @param  seq:        A sequence number that will be echoed back for unique identification (optional).
        """
        params      = urlencode( {'seq':seq} )
        response    = self._request( "POST", "/policy/list", params)
        parsed      = self.parse( response )

        if parsed['status'] == "OK":
            contents = parsed['contents']
            policies = contents['policies']         # Should be an iterable list of policies
        else:
            raise PolicyError( "Unable to get policy list", contents )
	#print parsed
        return policies

    def scanNew( self, scan_name, target, policy_id, seq=randint(SEQMIN,SEQMAX)):
        """
        Start up a new scan on the Nessus server immediately.

        @type   scan_name:  string
        @param  scan_name:  The desired name of the scan.
        @type   target:     string
        @param  target:     A Nessus-compatible target string (comma separation, CIDR notation, etc.)
        @type   policy_id:  number
        @param  policy_id:  The unique ID of the policy to be used in the scan.
        @type   seq:        number
        @param  seq:        A sequence number that will be echoed back for unique identification (optional).
        """
        params      = urlencode( {'target':target,'policy_id':policy_id,'scan_name':scan_name,'seq':seq} )
        response    = self._request( "POST", "/scan/new", params)
        parsed      = self.parse( response )

        if parsed['status'] == "OK":
            contents = parsed['contents']
            return contents['scan']                 # Return what you can about the scan
        else:
            raise ScanError("Unable to start scan", contents )
            
    def quickScan( self, scan_name, target, policy_name, seq=randint(SEQMIN,SEQMAX)):
        """
        Configure a new scan using a canonical name for the policy. Perform a lookup for the policy ID and configure the scan,
        starting it immediately.

        @type   scan_name:   string
        @param  scan_name:   The desired name of the scan.
        @type   target:      string
        @param  target:      A Nessus-compatible target string (comma separation, CIDR notation, etc.)
        @type   policy_name: string
        @param  policy_name: The name of the policy to be used in the scan.
        @type   seq:         number
        @param  seq:         A sequence number that will be echoed back for unique identification (optional).
        """
        policies = self.policyList()
	try:
	        if type(policies['policy']) is dict:
	            # There appears to be only one configured policy
	            policy = policies['policy']
	            if policy['policyName'] == policy_name:
	                policy_id = policy['policyID']
	            else:
	                raise PolicyError( "Unable to parse policies from policyList()", (scan_name,target,policy_name))
        except:
            # We have multiple policies configured
		for policy in policies:
#			print ("Policy Name is : ",policy['policyName'], "ID is : ",policy['policyID'])
	                if policy['policyName'] == policy_name:
				policy_id = policy['policyID']
#			else:
#		                raise PolicyError( "Unable to parse policies from policyList()", (scan_name,target,policy_name))
				return self.scanNew( scan_name, target, policy_id )

    def reportList( self, seq=randint(SEQMIN,SEQMAX)):
        """
        Generate a list of reports available on the Nessus server.

        @type   seq:        number
        @param  seq:        A sequence number that will be echoed back for unique identification (optional).
        """
        params      = urlencode({'seq':seq})
        response    = self._request( "POST", "/report/list", params)
        parsed      = self.parse( response )

        if parsed['status'] == "OK":
            contents = parsed['contents']
            return contents['reports']              # Return an iterable list of reports
        else:
            raise ReportError( "Unable to get reports.", contents )

    def reportDownload( self, report, version="v2" ):
        """
        Download a report (XML) for a completed scan.

        @type   report:     string
        @param  report:     The UUID of the report or completed scan.
        @type   version:    string
        @param  version:    The version of the .nessus XML file you wish to download.
        """
        if version == "v1":
            params = urlencode({'report':report, 'v1':version })
        else:
            params = urlencode({'report':report})
        return self._request( "POST", "/file/report/download", params )
