<?
if (!is_object($vbulletin->db))
{
	exit;
}
error_reporting(E_ALL & ~E_NOTICE);
if($vbulletin->options['reminder_active']){
	$now = time();
	$datetime = array();

	$quantity = $vbulletin->options['quantity'];
	$datetime[lastpost] = $now - (60 * 60 * 24 * $vbulletin->options['inactivity']);
	$datetime[lastemail] = $now - (60 * 60 * 24 * $vbulletin->options['frequency']);


	$usergroups = explode(",",$vbulletin->options['usergroups']);

	if($vbulletin->options['reminder_posts']){
		$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastpost < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16");
		//print("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastpost < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16<br/>");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET emailDate = '$now' WHERE lastpost < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16");
		//print("UPDATE " . TABLE_PREFIX . "user SET emailDate = '$now' WHERE lastpost < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16<br/>");
	} else {
		$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastactivity < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16");
		//print("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastactivity < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16<br/>");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET emailDate = '$now' WHERE lastactivity < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16");
		//print("UPDATE " . TABLE_PREFIX . "user SET emailDate = '$now' WHERE lastactivity < '$datetime[lastpost]' AND emailDate < '$datetime[lastemail]' AND options & 16<br/>");
	}
	print("Found " . mysql_num_rows($result) . " Inactive Users.<br/><br/>");
	while($row = $vbulletin->db->fetch_array($result)){		

		if(is_member_of($row, $usergroups)){
		
			print("Sent To: $row[username]<br/>");

			$username = $row[username];
			$toemail = $row[email];
			$userid = $row[userid];
			$bbtitle = $vbulletin->options['bbtitle'];
			$homeurl = $vbulletin->options['homeurl'];
			$forumurl = $vbulletin->options['bburl'];
			$hometitle = $vbulletin->options['hometitle'];		
	
			eval('$message = "' . addslashes($vbulletin->options['message']) . '";');
			$message = stripslashes($message);
	
			//$headers  = "MIME-Version: 1.0" . "\r\nContent-type: text/html; charset=iso-8859-1" . "\r\n";

			$uheaders .= "To: $username <$email>" . "\r\n";
			$uheaders .= "From: " . $vbulletin->options['bbtitle'] . " Reminder Service <" . $vbulletin->options['webmasteremail'] . ">" . "\r\n";
	
			eval('$subject = "' . addslashes($vbulletin->options['subject']) . '";');
			$subject = stripslashes($subject);
			
			if($vbulletin->options['reminder_emailfooter']){
				$message = $message . "Email Reminder System Provided By Mished.co.uk";
			}
			
			/*if(@mail($email, $subject, $message, $headers)){
				print("mailing $email (done)<br/>");
				
			} else {
				print("mailing $email (failed)<br/>");
			}*/
			
			
			if(is_valid_email($toemail)){				
				$sentlist .= "$username ";
				vbmail($toemail, $subject, $message, $notsubscription = false, $from = $vbulletin->options['bbtitle'], $uheaders = '', $username = '');
			} else {
				$failedlist .= "$username ";
			}

		}
		
	}
	if($sentlist == ""){
		log_cron_action("No Emails to send", $nextitem);
		vbmail($vbulletin->options['webmasteremail'], "Inactive User Reminder Email Report", "This email shows that the product is installed and working as it should be.\n\n\nThere were no inactive users at this time.", $notsubscription = false, $from = $vbulletin->options['bbtitle'], $uheaders = "From: " . $vbulletin->options['bbtitle'] . " Reminder Service <" . $vbulletin->options['webmasteremail'] . ">" . "\r\n", $username = '');
	} else {
		log_cron_action("Emails sent to:$sentlist. We tried to email the following users, but their email address was invalid:$failedlist", $nextitem);
		vbmail($vbulletin->options['webmasteremail'], "Inactive User Reminder Email Report", "This email shows that the product is installed and working as it should be.\n\n\nEmails sent to:" . $sentlist . ". We tried to email the following users, but their email address was invalid:" . $failedlist . "", $notsubscription = false, $from = $vbulletin->options['bbtitle'], $uheaders = "From: " . $vbulletin->options['bbtitle'] . " Reminder Service <" . $vbulletin->options['webmasteremail'] . ">" . "\r\n", $username = '');
	}
} else {
	print("Product is inactive at this time!");
}
?>