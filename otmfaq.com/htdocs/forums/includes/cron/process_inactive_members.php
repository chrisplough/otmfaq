<?php

/**
 * @author James MacDiarmid
 * @copyright 2011
 */

error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))
{
	exit;
}

require_once(DIR . '/includes/functions_remindermail.php');

if($vbulletin->options['reminder_enabled'])
{

    // Checked in plugin to determine who is requesting HTML Email
    // to be sent.
    $vbulletin->session->vars['CRONTASK_REMINDERMAIL'] = 1; 

    $batchlimit = $vbulletin->options['reminder_batch'];
	$maxemail = $vbulletin->options['reminder_maxemails'];
    $graceperiod = $vbulletin->options['reminder_inactivitygraceperiod'];
    $frequency = $vbulletin->options['reminder_frequency'];
    $temp_groups = explode(',', $vbulletin->options['reminder_usergroups']);
    $lastele = array_pop($temp_groups);
    $temp_groups = implode(',',$temp_groups);
    $usergroups = str_replace(",","','", trim($temp_groups));
    $excludeduserids = str_replace(",","','", trim($vbulletin->options['reminder_excludeduserids']) );
    
    $sql = "SELECT DATE_ADD(FROM_UNIXTIME(UNIX_TIMESTAMP()), INTERVAL 5 DAY) nextemail" ; 
	$result = $vbulletin->db->query_read($sql);
    $row = $vbulletin->db->fetch_array($result);
    $nextemail = $row['nextemail'];
        

    //Send reminders one time
    if($vbulletin->options['reminder_sendonce'])
    {
        //Process members who have become inactive and never received a reminder email.
        $result = ProcessInactiveMembersBasedOnLastActivity_FirstReminder($graceperiod, $usergroups, $excludeduserids, $batchlimit);
        if ($result)
        {
            $msg = "Members who have become inactive based on their last activity date and never received a reminder email";
            SendReminders($result, $msg);
        }
    }
    else
    {
        //Process members who have become inactive based on last activity date and never received a reminder email.
        $result = ProcessInactiveMembersBasedOnLastActivity_FirstReminder($graceperiod, $usergroups, $excludeduserids, $batchlimit);
        if ($result)
        {
            $msg = "Members who have become inactive based on their last activity date and never received a reminder email:";
            SendReminders($result, $msg);
        }

        //Process members who have become inactive based on their last activity date.
        $result = ProcessInactiveMembersBasedOnLastActivity($graceperiod, $frequency, $usergroups, $maxemail, $excludeduserids, $batchlimit, $nextemail);
        if ($result)
        {
            $msg = "Members who have become inactive based on their last activity date.";
            SendReminders($result, $msg);
        }
    }
 
    $vbulletin->session->vars['CRONTASK_REMINDERMAIL'] = 0; 
  
}
else 
{
	print("Product has been disabled in product settings!");
}

?>