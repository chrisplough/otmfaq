<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
 
// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');
 
// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');


print_cp_header();


$do = $_GET['do'];

switch($do){
case('test'):
	$testemail = $_POST['testemail'];
	
		
		$useridlist[] = $row['userid'];
		
		$userid = "0";
		$username = "Test User";
		$email = $testemail;
		$from = $vbulletin->options['reminder_fromemail'];
		$subject = $vbulletin->options['reminder_messagetitle'];
		$bbtitle = $vbulletin->options['bbtitle'];
		$homeurl = $vbulletin->options['homeurl'];
		$forumurl = $vbulletin->options['bburl'];
		$hometitle = $vbulletin->options['hometitle'];	
		$salt = "TST";
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "To: $username <$email>" . "\r\n";
		$headers .= "From: " . $vbulletin->options['bbtitle'] . " Reminder Service <" . $vbulletin->options['reminder_fromemail'] . ">" . "\r\n";
		$headers .= "Return-Path: " . $vbulletin->options['reminder_fromemail'] . "\r\n";
		$headers .= "X-Remindermail-BounceId: $userid\r\n";
		$headers .= "X-Remindermail-BounceSalt: $salt\r\n";

		
		if($row['reminder_reminders'] > 0){
		$message = $vbulletin->options['reminder_followup'];
		} else {
		$message = $vbulletin->options['reminder_message'];
		}
		
		$message = addslashes($message);		
		eval('$message = "' . $message . '";');
		$message = stripslashes($message);
		$message = addslashes($message);
		
		$subject = addslashes($subject);		
		eval('$subject = "' . $subject . '";');
		$subject = stripslashes($subject);
		$subject = addslashes($subject);
		
		if(is_valid_email($email)){
			$valid = "1";
			if($vbulletin->options['usemailqueue'] == "1"){				
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "mailqueue (dateline,toemail,fromemail,subject,message,header) VALUES ('$now','$email','$from','$subject','$message','$headers')");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "datastore SET data = data + 1 WHERE title = 'mailqueue'");
			} else {
				mail($to, $subject, $message, $headers);
			}
		} else {
			$valid = "0";
		}
				
		//Logging (Keeping records. Not cutting wood)
		
		if(is_valid_email($email)){
			$valid = "1";
		} else {
			$valid = "0";
		}
		
		if($vbulletin->options['reminder_logenabled']){
			$username = $db->escape_string($username);
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "remindermail_log (username,userid,dateline,validemail,email) VALUES ('$username','$userid','$now','$valid','$email')");
			if($vbulletin->options['reminder_logduration'] > 0){
				$deleteperiod = $now - ($vbulletin->options['reminder_logduration'] * 24 * 60 * 60);
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "remindermail_log WHERE dateline < '$deleteperiod'");
			}
		}
		
		print_cp_message('Email Sent.', 'remindermail.php?do=stats', 3); 
break;
case('stats'):
print_table_start();
print_table_header("Stats (Based on your current configuration)",2);

$inactivity = $now - ($vbulletin->options['reminder_duration'] * 24 * 60 * 60);

$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "remindermail_stats");
while($row = $vbulletin->db->fetch_array($result)){
	switch($row['title']){
	case "sent":
		$title = "Emails Sent:";
	break;
	case "bounced":
		$title = "Emails Bounced:";
	break;
	}
	print("<tr class=\"" . fetch_row_bgclass() . "\"><td>$title</td><td>" . $row['value'] . "</td></tr>");
}


$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastactivity < '$inactivity' AND usergroupid IN('" . $vbulletin->options['reminder_usergroup'] . "')");
print("<tr class=\"" . fetch_row_bgclass() . "\"><td>Total inactive users:</td><td>" . $vbulletin->db->num_rows($result) . "</td></tr>");
$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastactivity < '$inactivity' AND options & 16 AND usergroupid IN('" . $vbulletin->options['reminder_usergroup'] . "')");
print("<tr class=\"" . fetch_row_bgclass() . "\"><td>-of which accept admin emails:</td><td>" . $vbulletin->db->num_rows($result) . "</td></tr>");
$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastactivity < '$inactivity' AND reminder_reminders = 0 AND options & 16 AND usergroupid IN('" . $vbulletin->options['reminder_usergroup'] . "')");
print("<tr class=\"" . fetch_row_bgclass() . "\"><td>Inactive users left to contact:</td><td>" . $vbulletin->db->num_rows($result) . "</td></tr>");
if($vbulletin->options['reminder_quantity'] != 0){
$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE lastactivity < '$inactivity' AND reminder_reminders = '" . $vbulletin->options['reminder_quantity'] . "' AND options & 16 AND usergroupid IN('" . $vbulletin->options['reminder_usergroup'] . "')");
print("<tr class=\"" . fetch_row_bgclass() . "\"><td>Users that have been sent the maximum emails(" . $vbulletin->options['reminder_quantity'] . "):</td><td>" . $vbulletin->db->num_rows($result) . "</td></tr>");
}

print_table_footer(2, '', '', 0);
break;
case('sendtest'):
print_table_start();
print_form_header('remindermail','test','','true','','','','','POST');
print_table_header("Send a test email: (Based on your current configuration)");
print_input_row('Send test email to:', 'testemail'); 
print_submit_row("Submit!", 0);  
print_table_footer();
break;
case('inactive'):
print_table_start();
print_table_header("Inactive Users",5);



print("<tr class=\"" . fetch_row_bgclass() . "\"><td>ID</td><td>Username</td><td>Email</td><td>Valid Email?</td><td>Bounced?</td></tr>");

$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "remindermail_log ORDER BY dateline DESC");
while($row = $vbulletin->db->fetch_array($result)){
	
	$userid = $row['userid'];
	$username = $row['username'];
	$dateline = $row['dateline'];
	$email = $row['email'];
	$msgid = $row['id'];
	
	if($row['validemail'] == 1){
		$validemail = "<div style=\"color: #72ff00;\">Yes</div>";
	} else {
		$validemail = "<div style=\"color: #ff0000; font-weight: bold;\">No</div>";
	}
	if($row['bounced'] == 1){
		$bounced = "<div style=\"color: #ff0000; font-weight: bold;\">Yes</div><a href=\"remindermail.php?do=view&msgid=$msgid\" style=\"color: #ff0000;\" class=\"smallfont\">[ View ]</a>";
	} else {
		$bounced = "<div style=\"color: #72ff00;\">No</div>";
	}
	print("<tr class=\"" . fetch_row_bgclass() . "\"><td>$userid</td><td><a href=\"user.php?do=edit&u=$userid\">$username</a></td><td>$email</td><td>$validemail</td><td>$bounced</td></tr>");
}
print_table_footer(5, '', '', 0);
break;
case('purge'):
print_table_start();
print_form_header('remindermail','purgedo','','true','','','','','GET');
print_table_header("Delete all log entries? This cannot be un-done");
print_submit_row("Submit!", 0);  
print_table_footer();
break;
case('purgedo'):
$vbulletin->db->query_write("TRUNCATE TABLE " . TABLE_PREFIX . "remindermail_log");
$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "remindermail_stats SET value='0'");
print_cp_message('Logs Deleted.', 'remindermail.php?do=stats', 3); 
break;
case('view'):
$msgid = $_GET['msgid'];
$row = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "remindermail_log WHERE id = '$msgid'");
print_table_start();
print_table_header("Message Details",1);
print("<tr class=\"" . fetch_row_bgclass() . "\"><td>");
print("<pre>");
print($row['message']);
print("</pre>");
print("</td></tr>");
print("<tr class=\"" . fetch_row_bgclass() . "\"><td><a href=\"remindermail.php?do=removestatus&userid=" . $row['userid'] . "\">Click here to remove 'Bounced' status from the database.</a></td></tr>");
print_table_footer(1, '', '', 0);
break;
case('removestatus'):
$userid = $_GET['userid'];
$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET bounced = '0' WHERE userid = '$userid'");
print_cp_message('Status Changed.', 'remindermail.php?do=stats', 3); 
break;
}
print_cp_footer();
?>