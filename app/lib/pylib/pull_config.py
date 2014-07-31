#!/bin/python
"""
Parse the PHP config file to get database credentials.
Author: Justin C. Klein Keane
Last Updated: 15 March, 2013
"""
import inspect, os

class Configurator:
  
  configs = {}
  
  def __init__(self):
    configpath = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))
    configpathstr = str(configpath) + "/../../conf/config.ini"
    configfile = open(configpathstr, 'r')
    
    for line in configfile:
      if line[0] is ';':
        pass
      elif line.find('=') < 0:
        pass
      else:
        configvars = line.split('=')
        self.configs[configvars[0].strip()] = configvars[1].strip()
        
  def get_var(self, varkey):
    if varkey in self.configs:
      return self.configs[varkey]
    else:
      return False
        
