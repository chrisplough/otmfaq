<?php
/*======================================================================*\
|| #################################################################### ||
|| # Easy Bounced Email/User Management for Admins, by Antivirus		||
|| # http://www.vbulletin.org/forum/showthread.php?t=138884 			||
|| # Last tested on vBulletin 3.6.8										||
|| #    The white zone is for loading & unloading only... 				||
|| #    If you have to load or unload, please go to the white zone.		||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ########################### 
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS ####################### 
define("THIS_SCRIPT", "anti_ezbounce");

// ######################### REQUIRE BACK-END #############################
// Include the required vBulletin files
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminsettings'))
{
	print_cp_no_permission();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################


// ################## Begin 'do' Branching ###################

// set default branch action
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'manage';
}


// ###################### Start Manage #######################
if ($_REQUEST['do'] == 'manage')
{
	$vbulletin->input->clean_gpc('r', 'u', TYPE_UINT);
	
	// get bounced user info
	$buser = $db->query_first("
			SELECT * FROM " . TABLE_PREFIX . "user
			WHERE userid = " . $vbulletin->GPC['u'] . "
	");
	
	// Do a safety check to make sure managed member is not an Admin, supermoderator, or Moderator 
	if (is_member_of($buser['usergroupid'], 5,6,7))
	{
		// Exit script
		define('CP_REDIRECT', 'user.php?do=modify');
		print_stop_message('anti_member_x_is_in_a_protected_usergroup', $buser['username']);
		exit;
	}
	
	// if buser's ezb_oldugid is == 0 then this is first time buser has bounced
	if ($buser['ezb_oldugid'] == 0)
	{
		if ($vbulletin->options['ezb_updateusergroup'] == true)
		{
			// Save Member's Original UsergroupID
			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "user
				SET ezb_oldugid = " . $buser['usergroupid'] . "
				WHERE userid = " . $buser['userid'] . "
			");
			
			// Update usergroupid
			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "user 
				SET usergroupid	= '" . $vbulletin->options['ezb_newusergroupid'] . "'
				WHERE userid	= " . $buser['userid'] . "	
			");
			
		}

		// Update Receive Email from Administrators to NO ( UPDATE user SET options = options + 16 WHERE NOT (options & 16) )
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user 
			SET options = options - 16 
			WHERE options & 16 AND userid = " . $buser['userid'] . "
		");
		
		// Update Receive Email Notification of New Private Messages to NO ( UPDATE user SET options = options + 4096 WHERE NOT (options & 4096) )
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user 
			SET options = options - 4096 
			WHERE options & 4096 AND userid = " . $buser['userid'] . "
		");
		
		// Update Receive Email from Other Members to NO ( UPDATE user SET options = options + 256 WHERE NOT(options & 256) )
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user 
			SET options = options - 256 
			WHERE options & 256 AND userid = " . $buser['userid'] . "
		");
		
		// Update all of bouncing user's subscribed THREADS to emailupdate = 0
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "subscribethread
			SET emailupdate = 0
			WHERE userid = " . $buser['userid'] . "
		");
		
		// Update all of bouncing user's subscribed FORUMS to emailupdate = 0
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "subscribeforum
			SET emailupdate = 0
			WHERE userid = " . $buser['userid'] . "
		");
		
		// Ensure Popup notification when new PM is received is turned ON
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user 
			SET pmpopup = 1
			WHERE userid = " . $buser['userid'] . "
		");
		
		
		// CONSTRUCT PRIVATE MESSAGE NOTIFICATION OF BOUNCING EMAIL
		// Send PM to user letting them know their email bounced & they need to update it
		require_once(DIR . '/includes/functions_misc.php'); 
	
		// Create url for user to update email addy
		$updatelink = $vbulletin->options['bburl'] . "/profile.php?" . $session['sessionurl'] . "do=editpassword";
		
		// create the DM to do error check & insert new PM
		$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY); 
		
		// Set other funky stuff for PM
		$pmdm->set('fromuserid', $vbulletin->userinfo['userid']); 
		$pmdm->set('fromusername', $vbulletin->userinfo['username']); 
		$pmdm->overridequota = true;
		$pmdm->set('title', $vbphrase['anti_your_email_bounced_sub']); 
		$pmdm->set('message', construct_phrase($vbphrase['anti_your_email_bounced_body'], $buser['username'], $vbulletin->userinfo['username'], $updatelink)); 
		$pmdm->set_recipients($buser['username'], $botpermissions); 
		$pmdm->set('dateline', TIMENOW);
		$pmdm->pre_save(); 
	
		// process errors if there are any 
		$errors = array_merge((array)$errors, $pmdm->errors); 
	
		if (!empty($errors)) 
		{ 
			$errorlist = ''; 
			foreach ($pmdm->errors AS $index => $error) 
			{ 
				return $errorlist .= "<li>$error</li>"; 
			} 
		} 
		else 
		{ 
			// the pm was sent... 
			$pmdm->save(); 
			
			// Display confirmation message
			define('CP_REDIRECT', 'user.php?do=modify');
			print_stop_message('anti_member_x_has_been_managed_for_bounces', $buser['username']);
			
		} 

	}
	else
	{
		// else do nothing because buser has already been managed for bounces
	}

}


?>