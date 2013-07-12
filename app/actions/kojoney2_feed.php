<?php
/**
 * Show honeypot data
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @version 2013.7.12
 */
require_once($approot . 'lib/class.Api_key.php');
require_once($approot . 'lib/class.Db.php');
if(!isset($_GET['api_key']))
{
	print 'no key provided(go to home page)';
}
else
{
	$api_key=new Api_key();
	$isvalid=$api_key->validate($_GET['api_key']);
	
	if($isvalid)
	{
		$db = Db::get_instance();
		$sql = 'select time, ip from koj_login_attempts ' .
			'where time > date_sub(curdate(), interval 1 day) order by time desc';
		$results = $db->fetch_object_array($sql);
		foreach($results as $result)
		{
			print $result->ip . ' ' . $result->time."\r\n";
		}
		$db->close();
	}
	else
		print 'Access Denied';
}

?>