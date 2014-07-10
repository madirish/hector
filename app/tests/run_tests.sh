#!/bin/bash
cd /opt/hector/app/tests
# We have to run as apache because the account doing
# the test takes ownership of logs during roll-over
sudo -u apache /usr/bin/php all_tests.php
