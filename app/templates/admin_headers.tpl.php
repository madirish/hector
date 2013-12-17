<!DOCTYPE html>
<html lang="en">
<head>
	<!-- This is the header template for logged in users -->
	<meta charset="utf-8">
	<title>HECTOR</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="images/favicon.ico" />
	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/penn.css" rel="stylesheet">
	
	<script type="text/javascript" src="js/ajaxFunctions.js"></script> 
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	
	<?php if (isset($javascripts)) echo $javascripts;?>
</head>
<body>
<!-- 

	HECTOR
	
	an open source security intelligence platform from the 
	University of Pennsylvania's School of Arts & Sciences.

-->
<div id="headerbar" class="headergrey">
<div id="sas_header" class="sasgrey"> 
	<a href="http://www.sas.upenn.edu"><img src="http://www.sas.upenn.edu/home/assets/img/global/sas_header_logo_grey.jpg"></a> 
	<a class="links" href="http://www.sas.upenn.edu">School of Arts and Sciences</a> 
	<a class="links" href="http://www.upenn.edu">University of Pennsylvania</a>
</div>
</div>

<div class="container">
<h1>HECTOR</h1>
<div class="navbar"><div class="navbar-inner">
  <ul class="nav">
    <li><a href="?action=summary" title="Summary screen with overview statistics">Home</a></li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-globe"></i> Assets <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=browse_ip">Browse</a></li>
        <li><a href="?action=ossec">OSSEC Clients</a></li>
        <li class="divider"></li>
        <li class="nav-header">Search</li>
        <li><a href="?action=assets&object=search">Search</a></li>
        <li><a href="?action=assets&object=ports">Advanced Search</a></li>
        <li class="divider"></li>
        <li class="nav-header">State changes</li>
        <li><a href="?action=assets&object=alerts">View alerts</a></li>
        <li class="divider"></li>
        <li class="nav-header">Add</li>
				<li><a href="?action=add_hosts">Add hosts</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Security Incidents."><i class="icon-warning-sign"></i> Incidents <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=incident_reports">Incident Reports</a></li>
        <li><a href="?action=new_ir">New Incident Report</a></li>
        <li class="nav-header">Configuration</li>
        <li><a href="?action=details&object=IRAction">Threat actions</a></li>
        <li><a href="?action=details&object=IRAgent">Incident agents</a></li>
        <li><a href="?action=details&object=IRAsset">Assets</a></li>
        <li><a href="?action=details&object=IRDiscovery">Discovery methods</a></li>
        <li><a href="?action=details&object=IRMagnitude">Magnitudes</a></li>
        <li><a href="?action=details&object=IRTimeframe">Timeframes</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-list-alt"></i> <?php echo $reports ?> <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=reports&report=by_port">Ports detected</a></li>
        <li><a href="?action=reports&report=danger_host">Dangerous hosts</a></li>
        <li><a href="?action=reports&report=nonisuswebservers">Non ISUS Server Report</a></li>
        <li><a href="?action=vuln">Vulnerabilities <?php echo $vuln_badge ;?></a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="View intrustion detection summaries."><i class="icon-eye-open"></i> Detection <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=detection">Detection summary</a></li>
        <li><a href="?action=honeypot">Honeypot data</a></li>
        <li><a href="?action=attackerip">Malicious IP database</a></li>
        <li><a href="?action=ossecalerts">OSSEC Alerts</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-wrench"></i> Configuration <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <!-- <li><a href="?action=config">Overview</a></li> -->
        <li class="nav-header">Scans</li>
        <li><a href="?action=config&object=scan">Scan Schedule</a></li>
        <li><a href="?action=config&object=scan_type">Script Configuration</a></li>
        <li class="divider"></li>
        <li class="nav-header">Designations</li>
        <li><a href="?action=config&object=host_group">Host groups</a></li>
        <li><a href="?action=config&object=location">Locations</a></li>
        <li><a href="?action=config&object=supportgroup">Support groups</a></li>
        <li><a href="?action=config&object=tags">Free tags</a></li>
        <li><a href="?action=config&object=vuln">Vulnerabilities</a></li>
        <li class="divider"></li>
        <li class="nav-header">Data sources</li>
        <li><a href="?action=config&object=feeds">RSS feeds</a></li>
        <li class="divider"></li>
        <li class="nav-header">Authorizations</li>
        <li><a href="?action=config&object=users">User accounts</a></li>
        <li><a href="?action=config&object=api_key">API key</a></li>
      </ul>
    </li>
    <li><a href="?action=logout">Log Out</a></li>
  </ul>
    
    <form class="navbar-search pull-right" method="post" name="<?php echo $ip_search_name;?>" id="<?php echo $ip_search_name;?>" action="?action=assets&object=search">
    	<input type="text" class="search-query" placeholder="Search for IP" name="ip">
    	<input type="hidden" name="token" value="<?php echo $ip_search_token;?>"/>
			<input type="hidden" name="form_name" value="<?php echo $ip_search_name;?>"/>
    </form>
    
</div></div>  <!-- End navbar -->

<div id="content">
