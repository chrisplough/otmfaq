<?php if (!defined('IDIR')) { die; }
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF98A5CB5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is ©2000-2006 Jelsoft Enterprises Ltd. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/**
* cutecast_003 Import User module
*
* @package			ImpEx.cutecast
* @version			$Revision: 1.5 $
* @author			Scott MacVicar <scott.macvicar@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/04/03 02:44:10 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class cutecast_003 extends cutecast_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '001';
	var $_modulestring 	= 'Import User';


	function cutecast_003()
	{
		// Constructor
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_users'))
				{
					$displayobject->display_now('<h4>Imported users have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_users','Check database permissions');
				}
			}


			// Start up the table
			$displayobject->update_basic('title','Import User');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_user','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code('Users to import per cycle (must be greater than 1)','userperpage',50));


			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('userstartat','0');
			$sessionobject->add_session_var('userdone','0');
		}
		else
		{
			// Dependant has not been run
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description('<p>This module is dependent on <i><b>' . $sessionobject->get_module_title($this->_dependent) . '</b></i> cannot run until that is complete.'));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));
			$sessionobject->set_session_var(substr(get_class($this) , -3),'FALSE');
			$sessionobject->set_session_var('module','000');
		}
	}


	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		// Set up working variables.
		$displayobject->update_basic('displaymodules','FALSE');
		$target_database_type	= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix	= $sessionobject->get_session_var('targettableprefix');


		// Per page vars
		$user_start_at			= $sessionobject->get_session_var('userstartat');
		$user_per_page			= $sessionobject->get_session_var('userperpage');
		$userpath 				= $sessionobject->get_session_var('userpath');
		$class_num				= substr(get_class($this) , -3);

		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of user details
		$user_array 	= $this->get_members_list($userpath, $user_start_at, $user_per_page);
		$doneuserids 	= $this->get_done_user_ids($Db_target, $target_database_type, $target_table_prefix);
		$user_object = new ImpExData($Db_target, $sessionobject, 'user');
		$user_object->_password_md5_already = false;

		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($user_array) . ' users</h4><p><b>From</b> : ' . $user_start_at . ' ::  <b>To</b> : ' . ($user_start_at + count($user_array)) . '</p>');


		foreach ($user_array as $usernumber => $userfile)
		{
			$try = (phpversion() < '5' ? $user_object : clone($user_object));
			if (array_key_exists(intval($usernumber), $doneuserids))
			{
				# Skipping the 1st in the loop as they are the same as the last in the previous, or associated users
			}
			else
			{
				$userarray = file("$userpath/$userfile");
				$try->set_value('mandatory', 'importuserid',		$usernumber);
				$try->set_value('mandatory', 'usergroupid',		2);
				foreach ($userarray AS $file_value)
				{
					$matches = array();
					preg_match("#(\w+)=(.*)#", $file_value, $matches);
					if (!empty($matches[2]))
					{
						switch ($matches[1])
						{
							case 'username':
								$try->set_value('mandatory', 'username',	$matches[2]);
							break;
							case 'email':
								$try->set_value('mandatory', 'email',	$matches[2]);
							break;
							case 'password':
								$try->set_value('nonmandatory', 'password',	trim($matches[2]));
							break;
							case 'dateregistered':
								$try->set_value('nonmandatory', 'joindate',	$matches[2]);
							break;
							case 'birthday':
								$birthdayarray = explode('-', $matches[2]);
								list($day, $month, $year) = $birthdayarray;
								if (empty($day) OR empty($month))
								{
									continue;
								}
								else if (($year > 1901) AND ($year < date('Y')))
								{
									if (!checkdate($day, $month, $year))
									{
										continue;
									}
								}
								else if (checkdate($day, $month, 1996))
								{
									$year = '0000';
								}
								else
								{
									continue;
								}
								$try->set_value('nonmandatory', 'birthday',	"$day-$month-$year");
							break;
							case 'homepage':
								if (preg_match("#^[a-z]+://#", $matches[2]) AND $matches[2] != 'http://')
								{
									$try->set_value('nonmandatory', 'homepage',	$matches[2]);
								}
							break;
							case 'timeoffset':
								$try->set_value('nonmandatory', 'timezoneoffset',	$matches[2]);
							break;
							case 'lastvisit':
								$try->set_value('nonmandatory', 'lastvisit',	$matches[2]);
								$try->set_value('nonmandatory', 'lastactivity',	$matches[2]);
							break;
							case 'totalposts':
								$try->set_value('nonmandatory', 'posts',	$matches[2]);
							break;
							case 'location':
								$try->add_default_value('Location',		 	addslashes($matches[2]));
							break;
							case 'occupation':
								$try->add_default_value('Occupation',	 	addslashes($matches[2]));
							break;
							case 'interests':
								$try->add_default_value('Interests', 		addslashes($matches[2]));
							break;
							case 'aim':
								$try->set_value('nonmandatory', 'aim', 		addslashes($matches[2]));
							break;
							case 'icq':
								$try->set_value('nonmandatory', 'icq', 		addslashes($matches[2]));
							break;
							case 'signature':
								$try->add_default_value('signature',		addslashes(unhtmlspecialchars($this->cutecast_bbcode_to_vb_bbcode($matches[2]))));
							break;
						}
					}
				}

				// Check if user object is valid
				if($try->is_valid())
				{
					if($try->import_user($Db_target, $target_database_type, $target_table_prefix))
					{
						$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: user -> ' . STRING_HERE);
						$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
					}
					else
					{
						$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
						$displayobject->display_now("<br />Found avatar user and <b>DID NOT</b> imported to the  {$target_database_type} database");
					}
				}
				else
				{
					$displayobject->display_now("<br />Invalid user object, skipping." . $try->_failedon);
				}
				unset($try);
			}
		}// End resume


		// Check for page end
		if (count($user_array) == 0 OR count($user_array) < $user_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');
			$this->build_user_statistics($Db_target, $target_database_type, $target_table_prefix);


			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
										$sessionobject->return_stats($class_num, '_time_taken'),
										$sessionobject->return_stats($class_num, '_objects_done'),
										$sessionobject->return_stats($class_num, '_objects_failed')
										));


			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_user','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php','1'));
		}


		$sessionobject->set_session_var('userstartat',$user_start_at+$user_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php'));
	}// End resume
}//End Class
# Autogenerated on : June 9, 2004, 6:55 pm
# By ImpEx-generator 1.0.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 003.php,v $ - $Revision: 1.5 $
|| ####################################################################
\*======================================================================*/
?>
