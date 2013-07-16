<?php
/**
 * Show honeypot data
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @version 2013.7.12
 */
require_once($approot . 'lib/class.Api_key.php');
require_once($approot . 'lib/class.Db.php');
if(!(isset($_GET['api_key']) and isset($_GET['ajax'])))
{
	header("Location: ?action=summary");
}
else
{
	header("Content-type: text/plain");
	$api_key=new Api_key();
	$isvalid=$api_key->validate($_GET['api_key']);
	if($isvalid)
	{
		print "#Kojoney2 feed\n";
		print "#description: Kojoney2 login attempts\n";
		print "#values: address, detecttime\n";
		print "#delimiters: \\t, \\n\n\n";
		$db = Db::get_instance();
		$sql = 'select distinct(ip), time from koj_login_attempts ' .
			'where time between date_sub(curdate(), interval 1 day) and curdate() group by ip';
		$results = $db->fetch_object_array($sql);
		foreach($results as $result)
		{
			print $result->ip . "\t" . $result->time."\n";
		}
		$db->close();
	}
	else
		print 'Access Denied';
}

?>