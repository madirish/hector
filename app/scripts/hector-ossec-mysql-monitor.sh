#!/bin/bash
# Author: James Davis <jpd838@gmail.com> 
# Modified by: Justin C. Klein Keane <jukeane@sas.upenn.edu>
#
if [ ! -f /var/run/hector-ossec-mysql.pid ] ; then
  logger -p local0.notice -t hector-ossec-mysql-monitor 'No PID file found, restarting hector-ossec-mysql.'
  /sbin/service hector-ossec-mysql start
fi

# PID file could be orphaned
if !  ps -o pid h -p $(cat /var/run/hector-ossec-mysql.pid) ; then
  logger -p local0.err -t hector-ossec-mysql-monitor 'Pid file exists but process is gone.'
  rm -f /var/run/hector-ossec-mysql.pid
  /sbin/service hector-ossec-mysql start
fi

if [ /sbin/service hector-ossec-mysql status -eq 'hector-ossec-mysql.py is stopped ] ; then
  logger -p local0.notice -t hector-ossec-mysql-monitor 'hector-ossec-mysql is stopped, starting the service.'
  /sbin/service hector-ossec-mysql start
fi