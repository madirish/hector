<!DOCTYPE html>
<html lang="en">
<head>
	<!-- This is the header template for logged in users -->
	<meta charset="utf-8">
	<title>HECTOR</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="images/hector.ico" />

	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/ajaxFunctions.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	<script type="text/javascript" src="js/Chart.js"></script>
	<script type="text/javascript" src="js/jquery.dataTables.js"></script>
	<script type="text/javascript" src="js/hector.analytics.js"></script>
	<?php if (isset($javascripts)) echo $javascripts;?>
	<?php if (!empty($testscripts)): ?>
		<?php foreach($testscripts as $script): ?>
			<?php echo $script;?>
		<?php endforeach?>
	<?php endif;?>

	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/chart-legend.css" rel="stylesheet">
	<link href="css/jquery.dataTables.css" rel="stylesheet">
	<?php if (isset($css)) echo $css;?>
	<?php if (!empty($testcss)): ?>
		<?php foreach($testcss as $link):?>
			<?php echo $link;?>
		<?php endforeach;?>
	<?php endif;?>
</head>
<body>
<!--

	HECTOR

	an open source security intelligence platform

-->

<div class="container">
<div class="row">
    <div class="span11"><h1>HECTOR</h1></div>
    <div class="span1"><i class="icon-info-sign"></i><a href="?action=about" title="About HECTOR">About</a></div>
</div>
<div class="navbar"><div class="navbar-inner">
  <ul class="nav">
    <li><a href="?action=summary" title="Summary screen with overview statistics">Home</a></li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Browse, search, and review assets"><i class="icon-hdd"></i> Assets <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=browse_ip"><i class="icon-th"></i> Browse</a></li>
        <li><a href="?action=host_groups"><i class="icon-filter"></i> Host groups</a></li>
        <li><a href="?action=ossec"><i class="icon-flag"></i> OSSEC clients</a></li>
        <li class="divider"></li>
        <li class="nav-header">Search</li>
        <li><a href="?action=assets&object=search"><i class="icon-search"></i> Search</a></li>
        <li><a href="?action=assets&object=ports"><i class="icon-zoom-in"></i> Advanced search</a></li>
        <li class="divider"></li>
        <li class="nav-header">Logs</li>
        <li><a href="?action=assets&object=alerts"><i class="icon-list"></i> System logs</a></li>
        <li class="divider"></li>
        <li class="nav-header">Add</li>
		<li><a href="?action=add_hosts"><i class="icon-plus-sign"></i> Add hosts</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Create, review, and manage security incidents."><i class="icon-warning-sign"></i> Incidents <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=incident_reports" title="Review incident reports"><i class="icon-file"></i> Incident reports</a></li>
        <li><a href="?action=new_ir" title="Create new incident report"><i class="icon-plus-sign"></i> New incident report</a></li>
        <li class="nav-header">Configuration</li>
        <li><a href="?action=details&object=IRAsset"><i class="icon-download-alt"></i> Assets</a></li>
        <li><a href="?action=details&object=IRDiscovery"><i class="icon-search"></i> Discovery methods</a></li>
        <li><a href="?action=details&object=IRAgent"><i class="icon-user"></i> Incident agents</a></li>
        <li><a href="?action=details&object=IRMagnitude"><i class="icon-signal"></i> Magnitudes</a></li>
        <li><a href="?action=details&object=IRAction"><i class="icon-asterisk"></i> Threat actions</a></li>
        <li><a href="?action=details&object=IRTimeframe"><i class="icon-time"></i> Timeframes</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Comprehensive reports from HECTOR"><i class="icon-list-alt"></i> Reports <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=reports&report=danger_host"><i class="icon-indent-right"></i> Dangerous hosts</a></li>
        <li><a href="?action=reports&report=oses"><i class="icon-tasks"></i>  Operating systems</a></li>
        <li><a href="?action=reports&report=by_port"><i class="icon-list-alt"></i> Ports detected</a></li>
        <li><a href="?action=vuln"><i class="icon-fire"></i> Vulnerabilities <?php if (isset($vuln_badge)) echo $vuln_badge ;?></a></li>
        <li><a href="?action=vulnscans"><i class="icon-screenshot"></i> Vulnerability Scans</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="View intrustion detection summaries."><i class="icon-eye-open"></i> Detection <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=detection" title="Overview of detection data"><i class="icon-list-alt"></i> Detection summary</a></li>
        <li><a href="?action=honeypot"><i class="icon-screenshot"></i> Honeypot data</a></li>
        <li><a href="?action=attackerip"><i class="icon-ban-circle"></i> Malicious IP database</a></li>
        <li><a href="?action=ossecalerts"><i class="icon-exclamation-sign"></i> OSSEC alerts</a></li>
        <li><a href="?action=screenshots"><i class="icon-camera"></i> Website Screenshots</a>
        <!--  <li><a href="?action=nessus_scans">Nessus scans</a></li> -->
        <li class="nav-header">Manually Add Data</li>
        <li><a href="?action=add_edit&object=Vuln_detail" title="Manually log a vulnerability"><i class="icon-comment"></i> Report vulnerability</a></li>
        <li class="nav-header">Upload XML Data</li>
        <li><a href="?action=upload_qualys_xml"><i class="icon-arrow-up"></i> Upload Qualys report</a></li>
        <li><a href="?action=upload_openvas_xml"><i class="icon-arrow-up"></i> Upload OpenVAS report</a></li>
        <li><a href="?action=upload_nmap_xml"><i class="icon-arrow-up"></i> Upload Nmap scan</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="View open source intelligence collections."><i class="icon-globe"></i> OSINT <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=articles" title="Articles database"><i class="icon-book"></i> Articles</a></li>
        <li><a href="?action=add_edit&object=Article" title="Manually add an article"><i class="icon-edit"></i> Add Article</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Configure HECTOR components and scans."><i class="icon-wrench"></i> Config <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <!-- <li><a href="?action=config">Overview</a></li> -->
        <li class="nav-header">Scans</li>
        <li><a href="?action=config&object=scan" title="Schedule a recurring scan"><i class="icon-calendar"></i> Scan schedule</a></li>
        <li><a href="?action=config&object=scan_type" title="Configure a scanning script or program"><i class="icon-cog"></i> Script configuration</a></li>
        <li class="divider"></li>
        <li class="nav-header">Designations</li>
        <li><a href="?action=config&object=tags" title="Tag content"><i class="icon-tags"></i> Free tags</a></li>
        <li><a href="?action=config&object=host_group" title="Group hosts"><i class="icon-list"></i> Host groups</a></li>
        <li><a href="?action=config&object=location" title="Physical locations"><i class="icon-map-marker"></i> Locations</a></li>
        <li><a href="?action=config&object=risk" title="Categorize risk levels"><i class="icon-exclamation-sign"></i> Risk levels</a></li>
        <li><a href="?action=config&object=supportgroup" title="Support staff groups"><i class="icon-briefcase"></i> Support groups</a></li>
        <li><a href="?action=config&object=vuln" title="Vulnerabilities affecting systems"><i class="icon-warning-sign"></i> Vulnerabilities</a></li>
        <li class="divider"></li>
        <li class="nav-header">Data sources</li>
        <li><a href="?action=config&object=feeds" title="Configure RSS imports"><i class="icon-refresh"></i> RSS feeds</a></li>
        <li class="divider"></li>
        <li class="nav-header">Authorizations</li>
        <li><a href="?action=config&object=api_key" title="Keys for API authentication"><i class="icon-lock"></i> API key</a></li>
        <li><a href="?action=config&object=users" title="Add or modify system accounts"><i class="icon-user"></i> User accounts</a></li>
      </ul>
    </li>
    <li><a href="?action=logout" title="Log out of HECTOR">Log Out</a></li>
  </ul>

    <form class="navbar-search pull-right" method="post" name="<?php echo $ip_search_name;?>" id="<?php echo $ip_search_name;?>" action="?action=assets&object=search">
    	<input type="text" class="search-query" placeholder="Search for IP" name="ip">
    	<input type="hidden" name="token" value="<?php echo $ip_search_token;?>"/>
			<input type="hidden" name="form_name" value="<?php echo $ip_search_name;?>"/>
    </form>

</div></div>  <!-- End navbar -->

<div id="content">
