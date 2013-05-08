<?php
require_once($approot . 'lib/class.Form.php');
$ip_search = new Form();
$ip_search_name = 'search_ip_form';
$ip_search->set_name($ip_search_name);
$ip_search_token = $ip_search->get_token();
$ip_search->save();
?>
<h1>HECTOR</h1>
<div class="navbar"><div class="navbar-inner">
  <ul class="nav">
    <li><a href="?action=summary" title="Summary screen with overview statistics">Home</a></li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-globe"></i> Asset Management <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=browse_ip">Browse</a></li>
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
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-list-alt"></i> Reports <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=reports&report=by_port">Ports detected</a></li>
        <li><a href="?action=reports&report=danger_host">Dangerous hosts</a></li>
        <li><a href="?action=reports&report=nonisuswebservers">Non ISUS Server Report</a></li>
      </ul>
    </li>
     <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="View intrustion detection summaries."><i class="icon-eye-open"></i> Detection <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=detection">Detection summary</a></li>
        <li><a href="?action=honeypot">Honeypot data</a></li>
        <li><a href="?action=attackerip">Malicious IP database</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-wrench"></i> Configuration <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a href="?action=config">Overview</a></li>
        <li class="divider"></li>
        <li class="nav-header">Scans</li>
        <li><a href="?action=config&object=scan">Schedule</a></li>
        <li><a href="?action=config&object=scan_type">Configuration</a></li>
        <li class="divider"></li>
        <li class="nav-header">Designations</li>
        <li><a href="?action=config&object=hostgroups">Host groups</a></li>
        <li><a href="?action=config&object=location">Locations</a></li>
        <li><a href="?action=config&object=supportgroup">Support groups</a></li>
        <li><a href="?action=config&object=tags">Free tags</a></li>
        <li class="divider"></li>
        <li class="nav-header">Data sources</li>
        <li><a href="?action=config&object=feeds">RSS feeds</a></li>
        <li class="divider"></li>
        <li class="nav-header">Authorizations</li>
        <li><a href="?action=config&object=users">User accounts</a></li>
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
