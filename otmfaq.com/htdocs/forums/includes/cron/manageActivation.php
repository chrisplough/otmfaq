<?php
// version 3.1 updated instructions urghh


// ## Error Reporting ( we use error reporting in php so we can control the display of error messages
// ## we will use this because all vBulletin files follow the same error reporting rules) ##
error_reporting(E_ALL & ~E_NOTICE);

// Get all users who have not activated their accounts.
$userArray = $vbulletin->db->query_read("
	SELECT u.username, u.userid, u.email, u.joindate, ua.activationid, u.languageid  
	FROM " . TABLE_PREFIX . "user u
	LEFT JOIN " . TABLE_PREFIX . "useractivation ua ON (u.userid = ua.userid)
	WHERE u.usergroupid = 3 AND u.posts = 0 
");

while ($user = $vbulletin->db->fetch_array($userArray))
{
	if (empty($user['activationid'])) //none exists so create one
	{
		$user['activationid'] = vbrand(0, 100000000);
		$vbulletin->db->query("
				INSERT INTO " . TABLE_PREFIX . "useractivation(userid, dateline, activationid, type, usergroupid)
				VALUES(" . $user['userid'] . ", " . TIMENOW . ", " . $user['activationid'] . ", 0, 2)
		");
		echo ("ActivationID created to " . $user['username'] . "<br />");
	}

	// Calculate days since joining
	$currentday = time();
	$day = ($currentday - $user['joindate'])/86400;
	$username = $user['username'];
	$activateid  = $user['activationid'];
	$userid = $user['userid'];
   
	
	if ($day > 2 AND $day < 4) // Email users who have not activated after 3 days.
	{
		eval(fetch_email_phrases('ActivationEmail_v3_Day3', $user['languageid']));
		vbmail($user['email'], $subject, $message);
		echo("Sent 3 Day email to  " . $user['username'] . "<br />");

		$threedayemail[] = $user['username'];
	}
	elseif ($day > 4 AND $day < 6) // Email users who have not activated after 5 days.
	{
		eval(fetch_email_phrases('ActivationEmail_v3_Day5', $user['languageid']));
		vbmail($user['email'], $subject, $message);
		echo("Sent 5 Day email to  " . $user['username'] . "<br />");

		$fivedayemail[] = $user['username'];
	}	
	elseif ($day > 7 AND $day < 9) // Email users who have not activated after 8 days.
	{
		eval(fetch_email_phrases('ActivationEmail_v3_Day8', $user['languageid']));
		vbmail($user['email'], $subject, $message);
		echo("Sent 8 Day email to  " . $user['username'] . "<br />");

		$eightdayemail[] = $user['username'];
	}
	elseif ($day > 10) // Delete users.
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "post SET username = '" . $vbulletin->db->escape_string($user['username']) . "', userid = 0 WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "user WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "userfield WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "usertextfield WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "access WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "customavatar WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "moderator WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "pm WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribeforum WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribethread WHERE userid = " . $user['userid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "session WHERE userid = " . $user['userid']);
		echo("Deleted " . $user['username'] . "<br />");

		$deletedusers[] = $user['username'];
	}
}

$threedayemail = (!empty($threedayemail[0])) ? implode(', ', $threedayemail) : 'None';
$fivedayemail = (!empty($fivedayemail[0])) ? implode(', ', $fivedayemail) : 'None';
$eightdayemail = (!empty($eightdayemail[0])) ? implode(', ', $eightdayemail) : 'None';
$deletedusers = (!empty($deletedusers[0])) ? implode(', ', $deletedusers) : 'None';

$sentdate = vbdate($vbulletin->options['dateformat'], TIMENOW) ;
$senttime = vbdate($vbulletin->options['timeformat'], TIMENOW) ;

$logmessage = "manageActivation Complete; \n 3 Day Reminder Sent To: " . $threedayemail . ".\n\n 5 Day Reminder Sent To: " . $fivedayemail . ". \n\n 8 Day Reminder Sent To: " . $eightdayemail . ". \n\n Users Deleted: " . $deletedusers . ".";

if ($vbulletin->options['uac_notifyadmin'])
{
	vbmail("" . $vbulletin->options['webmasteremail'] . "","Activation Reminder Report (Activation Notification)", $logmessage, "From: \"" . $vbulletin->options['bbtitle'] . " Mailer\" <" . $vbulletin->options['webmasteremail'] . ">");
}

log_cron_action($logmessage, $nextitem);

?>