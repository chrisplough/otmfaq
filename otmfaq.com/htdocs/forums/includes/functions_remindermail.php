<?php

/**
 * @author James MacDiarmid
 * @copyright 2011
 */

function ProcessInactiveMembersBasedOnLastActivity_FirstReminder($graceperiod, $usergroups, $excludeduserids, $batchlimit)
{
    global $vbulletin;

    echo "Looking for members who have become inactive and never received a reminder email...<br/><br/>";

    $sql = "SELECT userid, username, email, ".
           "usergroupid, ".
           "FROM_UNIXTIME(lastvisit) lastvisit, ". 
           "FROM_UNIXTIME(lastactivity) lastactivity, ". 
           "CASE WHEN lastpost = 0 THEN '' ELSE FROM_UNIXTIME(lastpost) END AS lastpost, ". 
           "CASE WHEN rmEmailDate = 0 THEN '' ELSE FROM_UNIXTIME(rmEmailDate) END AS lastreminder, ".
           "TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastvisit), FROM_UNIXTIME(UNIX_TIMESTAMP())) days_since_lastvisit, ".
           "TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastactivity), FROM_UNIXTIME(UNIX_TIMESTAMP())) days_since_lastactivity, ".
           "CASE WHEN lastpost = 0 THEN 0 ELSE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastpost), FROM_UNIXTIME(UNIX_TIMESTAMP())) END AS days_since_lastpost, ".
           "CASE WHEN rmEmailDate = 0 THEN 0 ELSE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(rmEmailDate), FROM_UNIXTIME(UNIX_TIMESTAMP())) END AS days_since_lastemail, ".
           "rmoptout ".
           "FROM " . TABLE_PREFIX . "user ".  
           "WHERE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastactivity), FROM_UNIXTIME(UNIX_TIMESTAMP())) >= ". $graceperiod . " ". 
           "AND usergroupid IN ('" . $usergroups . "') ".
           "AND rmEmailCount = 0 ".
           "AND options & 16 ". 
           "AND rmoptout = 0 ";         

	if (strlen($excludeduserids) > 0) $sql .= "AND userid NOT IN ('" . $excludeduserids . "') "; 

    if (intval($batchlimit) > 0) $sql .= "LIMIT 0, " . $batchlimit . ";";
		
	$result = $vbulletin->db->query_read($sql);
	//print($sql."<br/><br/>");
    
	if ( $vbulletin->db->num_rows( $result ) != 0 )
	{

		$sql = "UPDATE " . TABLE_PREFIX . "user ". 
               "SET rmEmailDate = UNIX_TIMESTAMP(), ".
               "rmEmailCount = 1 ".  
			   "WHERE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastactivity), FROM_UNIXTIME(UNIX_TIMESTAMP())) >= ". $graceperiod . " ".
               "AND usergroupid IN ('" . $usergroups . "') ".
		       "AND rmEmailCount = 0 ".
               "AND options & 16 ".
               "AND rmoptout = 0 ";

		if (strlen($excludeduserids) > 0) $sql .= "AND userid NOT IN ('" . $excludeduserids . "') "; 
        
        if (intval($batchlimit) > 0) $sql .= "LIMIT " . $batchlimit . ";";
				
		$vbulletin->db->query_write($sql);
		//print($sql."<br/><br/>");

        echo $vbulletin->db->num_rows( $result ) . " inactive members(s) found!<br/>";

        return $result;
	}
    else
    {
        echo "No inactive member(s) found!<br/><br/>";
    }
    
}

function ProcessInactiveMembersBasedOnLastActivity($graceperiod, $frequency, $usergroups, $maxemail, $excludeduserids, $batchlimit, $nextemail)
{
    global $vbulletin;

    echo "Looking for members who have become inactive and <br/>".
         "haven't received a reminder email in the last $frequency days...<br/><br/>";

    $sql = "SELECT userid, username, email, ".
           "usergroupid, ".
           "FROM_UNIXTIME(lastvisit) lastvisit, ". 
           "FROM_UNIXTIME(lastactivity) lastactivity, ". 
           "CASE WHEN lastpost = 0 THEN '' ELSE FROM_UNIXTIME(lastpost) END AS lastpost, ". 
           "CASE WHEN rmEmailDate = 0 THEN '' ELSE FROM_UNIXTIME(rmEmailDate) END AS lastreminder, ".
           "TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastvisit), FROM_UNIXTIME(UNIX_TIMESTAMP())) days_since_lastvisit, ".
           "TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastactivity), FROM_UNIXTIME(UNIX_TIMESTAMP())) days_since_lastactivity, ".
           "CASE WHEN lastpost = 0 THEN 0 ELSE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastpost), FROM_UNIXTIME(UNIX_TIMESTAMP())) END AS days_since_lastpost, ".
           "CASE WHEN rmEmailDate = 0 THEN 0 ELSE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(rmEmailDate), FROM_UNIXTIME(UNIX_TIMESTAMP())) END AS days_since_lastemail, ".
           "rmoptout ".
           "FROM " . TABLE_PREFIX . "user ".  
           "WHERE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastactivity), FROM_UNIXTIME(UNIX_TIMESTAMP())) >= ". $graceperiod . " ". 
           "AND TIMESTAMPDIFF(DAY, FROM_UNIXTIME(rmEmailDate), FROM_UNIXTIME(UNIX_TIMESTAMP())) = ". $frequency . " ".
           "AND usergroupid IN ('" . $usergroups . "') ".
           "AND options & 16 ". 
           "AND rmoptout = 0 ";         


     // Do we want to set a maximum limit? 
    if (intval($maxemail) > 0) $sql .= "AND rmEmailCount < $maxemail "; 

    // Do we want to exclude specific users from getting reminders?
	if (strlen($excludeduserids) > 0) $sql .= "AND userid NOT IN ('" . $excludeduserids . "') "; 					

    // Now many users do we want to process during this job?
    if (intval($batchlimit) > 0) $sql .= "LIMIT 0, " . $batchlimit . ";";

    // let's go get 'em!                    
	$result = $vbulletin->db->query_read($sql);
	//print($sql."<br/><br/>");
    
	if ( $vbulletin->db->num_rows( $result ) != 0 )
    {		  

		$sql = "UPDATE " . TABLE_PREFIX . "user ".
		       "SET rmEmailDate = UNIX_TIMESTAMP() ";
        
        if (intval($maxemail) > 0) $sql .= ", rmEmailCount = rmEmailCount + 1 ";
        
        $sql .= "WHERE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(lastactivity), FROM_UNIXTIME(UNIX_TIMESTAMP())) >= ". $graceperiod . " ".
                "AND TIMESTAMPDIFF(DAY, FROM_UNIXTIME(rmEmailDate), FROM_UNIXTIME(UNIX_TIMESTAMP())) = ". $frequency . " ".
                "AND options & 16 ".
                "AND usergroupid IN ('" . $usergroups . "') ".
                "AND rmoptout = 0 "; 

        if (intval($maxemail) > 0) $sql .= "AND rmEmailCount < '$maxemail' ";
        
		if (strlen($excludeduserids) > 0) $sql .= "AND userid NOT IN ('" . $excludeduserids . "') ";
        
        if (intval($batchlimit) > 0) $sql .= "LIMIT " . $batchlimit . ";";
				
		$vbulletin->db->query_write($sql);
		//print($sql."<br/><br/>");

        echo $vbulletin->db->num_rows( $result ) . " inactive user(s) found!<br/><br/>";

        echo "Reminders will be sent to the following users every ". $frequency ." days ";     
        if (intval($maxemail) > 0) 
        {
            echo "up to a maximum of ".$maxemail.":<br/><br/>";            
        } 
		echo "<br/><br/>Inactivity reminders for the following members will be sent out again on: $nextemail  <br/>".
             "unless they logged in or post before then.<br/><br/>";

        return $result;

    }
    else
    {
        print("No inactive member(s) found!<br/><br/>");
    }
}

function SendReminders($result, $msg)
{
    global $vbulletin;

	while($row = $vbulletin->db->fetch_array($result))
    {		
        
		print("Sent To: $row[username]<br/>");
		
        $userid = $row[userid];                     // userid 
		$username = stripslashes($row['username']);   // username
        $toemail = stripslashes($row['email']);       // user email address
        $email = $row['email'];         // user email address 
		$lastactivity = $row['lastactivity'];
        $lastvisit = $row['lastvisit'];
        $lastpost = $row['lastpost'];  
        $dayssincelastactivity = $row['days_since_lastactivity'];
        $dayssincelastvisit = $row['days_since_lastvisit'];
        $dayssincelastpost = $row['days_since_lastpost'];

		$bbtitle = $vbulletin->options['bbtitle'];
		$homeurl = $vbulletin->options['homeurl'];
		$forumurl = $vbulletin->options['bburl'];
		$hometitle = $vbulletin->options['hometitle'];
        
        /*********************************************************************
        *  Are we sending reminders in html or plain text?
        **********************************************************************/           
        if (strtolower($vbulletin->options['reminder_emailformat'] == 'html'))
        {
            eval(fetch_email_phrases('inactivity_reminder_html'));
        }
        else
        {
            eval(fetch_email_phrases('inactivity_reminder_plaintext'));
        }
         
        /*********************************************************************
        * Validate the To Email address before sending
        **********************************************************************/           
		if(is_valid_email($toemail))
        {				

			$sentlist .= $username . ',';

			//vbmail($toemail, $subject, $message, $sendnow = false, $from = $vbulletin->options['bbtitle'], $uheaders = '', $username = '');
			vbmail($toemail, $subject, $message, $sendnow = false);
    
           	if ( $vbulletin->options['reminder_logenabled'] )
        	{
                logsentreminders($username, $userid, 1, $toemail, $sentlist, $failedlist);
        	}
            
		} 
		else 
		{

			$failedlist .= $username . ',';
       
        	if ( $vbulletin->options['reminder_logenabled'] )
        	{
                logsentreminders($username, $userid, 0, $toemail, $sentlist, $failedlist);
            }
		}
	}

    /*
     * === Administrator Logging and Email Section ===
     */
     
	if($sentlist == "")
    {
        if ($vbulletin->options['reminder_disablereport'])
        {
            $toemail = $vbulletin->options['webmasteremail'];
            $sub = "Inactive User Reminder Email Report";
            $msg = "This email shows that the product is installed and working as it should be.\r\nThere are no inactive users at this time.";
    
            if (strcmp($vbulletin->options['reminder_emailformat'], 'html') == 0)
            {
                $msg = str_replace("\r\n", "<br/><br/>", $msg);
            }
        
    		//vbmail($toemail,$sub,$msg,$sendnow = false,$from = $bbtitle,$uheaders = "" , $username = "");
    		vbmail($toemail,$sub,$msg,$sendnow = false);
        }
		log_cron_action("No Emails to send", $nextitem);
	} 
    else 
    {

        if ($vbulletin->options['reminder_disablereport'])
        {
            $toemail = $vbulletin->options['webmasteremail'];
            $sub  = "Inactive User Reminder Email Report";
            $msg .= "Emails sent to:\r\n " . $sentlist . "\r\n\r\n";
            
            if(strlen($failedlist) > 0)
            {
                $msg .= "We tried to email the following users, but their email address was invalid:\r\n" . $failedlist . "";
            }
            
            if (strcmp($vbulletin->options['reminder_emailformat'], 'html') == 0)
            {
                $msg = str_replace("\r\n", "<br/>", $msg);
            }
    		
    		//vbmail($toemail, $sub, $msg, $sendnow = false, $from = $bbtitle, $uheaders = "", $username = ""); 
    		vbmail($toemail, $sub, $msg, $sendnow = false); 
        }
        
        log_cron_action("Emails sent to: $sentlist. We tried to email the following users, but their email address was invalid:\r\n$failedlist", $nextitem);
	}
            
    echo "<br/>";
    
}


function logsentreminders($username, $userid, $valid, $toemail, $sentlist, $failedlist)
{
    global $vbulletin;

    $username = $vbulletin->db->escape_string($username);
    $logduration = $vbulletin->options['reminder_logduration'];
   
	$sql = "INSERT INTO " . TABLE_PREFIX . "inactiveuserlog (username,userid,dateline,validemail,email) 
            VALUES ('$username','$userid',UNIX_TIMESTAMP(),'$valid','$toemail')";
	$vbulletin->db->query_write( $sql );
    //print("$sql<br />");
	
	if ( $vbulletin->options['reminder_logduration'] > 0 )
	{
		$sql = "DELETE FROM " . TABLE_PREFIX . "inactiveuserlog " .
               "WHERE TIMESTAMPDIFF(DAY, FROM_UNIXTIME(dateline), FROM_UNIXTIME(UNIX_TIMESTAMP())) >= ". $logduration . " ";
		$vbulletin->db->query_write( $sql );
		//print $sql;
	}
    
}

function field_exists($table, $column)
{
    global $db;

	$exists = false;
	$columns = $db->query_read("SHOW COLUMNS FROM ". TABLE_PREFIX ."$table");
	while ($result = $db->fetch_array($columns))
	{
		if (strcasecmp($result['Field'], $column) == 0 )
		{
			$exists = true;
			break;
		}
	}

    return $exists;
}



?>