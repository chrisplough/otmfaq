<?php if (!defined('IDIR')) { die; }
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF98A5CB5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is �2000-2006 Jelsoft Enterprises Ltd. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/**
* oxygen_003 Import Usergroup module
*
* @package			ImpEx.megaBBS
* @version			$Revision: 1.4 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/04/03 09:34:32 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class oxygen_003 extends oxygen_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '001';
	var $_modulestring 	= 'Import Usergroup';


	function oxygen_003()
	{
		// Constructor
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_usergroups'))
				{
					$displayobject->display_now('<h4>Imported usergroups have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_usergroups','Check database permissions');
				}
			}


			// Start up the table
			$displayobject->update_basic('title','Import Usergroup');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_usergroup','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_description('<p>Going to import all the usersgroups.</p>'));


			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('usergroupstartat','0');
			$sessionobject->add_session_var('usergroupdone','0');
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
		$source_database_type	= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix	= $sessionobject->get_session_var('sourcetableprefix');


		// Per page vars
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of usergroup details
		$usergroup_array 	= $this->get_oxygen_usergroup_details($Db_source, $source_database_type, $source_table_prefix);

		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($usergroup_array) . ' usergroups</h4><p><b>From</b> : ' . $usergroup_start_at . ' ::  <b>To</b> : ' . ($usergroup_start_at + count($usergroup_array)) . '</p>');


		$usergroup_object = new ImpExData($Db_target, $sessionobject, 'usergroup');


		foreach ($usergroup_array as $usergroup_id => $usergroup_details)
		{
			$try = (phpversion() < '5' ? $usergroup_object : clone($usergroup_object));
			// Mandatory
			$try->set_value('mandatory', 'importusergroupid',		$usergroup_id);


			// Non Mandatory
			$try->set_value('nonmandatory', 'title',				$usergroup_details['status']);
			/*
			$try->set_value('nonmandatory', 'description',			$usergroup_details['description']);
			$try->set_value('nonmandatory', 'usertitle',			$usergroup_details['usertitle']);
			$try->set_value('nonmandatory', 'passwordexpires',		$usergroup_details['passwordexpires']);
			$try->set_value('nonmandatory', 'passwordhistory',		$usergroup_details['passwordhistory']);
			$try->set_value('nonmandatory', 'pmquota',				$usergroup_details['pmquota']);
			$try->set_value('nonmandatory', 'pmsendmax',			$usergroup_details['pmsendmax']);
			$try->set_value('nonmandatory', 'pmforwardmax',			$usergroup_details['pmforwardmax']);
			$try->set_value('nonmandatory', 'opentag',				$usergroup_details['opentag']);
			$try->set_value('nonmandatory', 'closetag',				$usergroup_details['closetag']);
			$try->set_value('nonmandatory', 'canoverride',			$usergroup_details['canoverride']);
			$try->set_value('nonmandatory', 'ispublicgroup',		$usergroup_details['ispublicgroup']);
			$try->set_value('nonmandatory', 'forumpermissions',		$usergroup_details['forumpermissions']);
			$try->set_value('nonmandatory', 'pmpermissions',		$usergroup_details['pmpermissions']);
			$try->set_value('nonmandatory', 'calendarpermissions',	$usergroup_details['calendarpermissions']);
			$try->set_value('nonmandatory', 'wolpermissions',		$usergroup_details['wolpermissions']);
			$try->set_value('nonmandatory', 'adminpermissions',		$usergroup_details['adminpermissions']);
			$try->set_value('nonmandatory', 'genericpermissions',	$usergroup_details['genericpermissions']);
			$try->set_value('nonmandatory', 'genericoptions',		$usergroup_details['genericoptions']);
			$try->set_value('nonmandatory', 'pmpermissions_bak',	$usergroup_details['pmpermissions_bak']);
			$try->set_value('nonmandatory', 'attachlimit',			$usergroup_details['attachlimit']);
			$try->set_value('nonmandatory', 'avatarmaxwidth',		$usergroup_details['avatarmaxwidth']);
			$try->set_value('nonmandatory', 'avatarmaxheight',		$usergroup_details['avatarmaxheight']);
			$try->set_value('nonmandatory', 'avatarmaxsize',		$usergroup_details['avatarmaxsize']);
			$try->set_value('nonmandatory', 'profilepicmaxwidth',	$usergroup_details['profilepicmaxwidth']);
			$try->set_value('nonmandatory', 'profilepicmaxheight',	$usergroup_details['profilepicmaxheight']);
			$try->set_value('nonmandatory', 'profilepicmaxsize',	$usergroup_details['profilepicmaxsize']);
			*/

			// Check if usergroup object is valid
			if($try->is_valid())
			{
				if($try->import_usergroup($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: usergroup -> ' . $usergroup_details['status']);
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
					$displayobject->display_now("<br />Found avatar usergroup and <b>DID NOT</b> imported to the  {$target_database_type} database");
				}
			}
			else
			{
				$displayobject->display_now("<br />Invalid usergroup object, skipping." . $try->_failedon);
			}
			unset($try);
		}// End foreach


		$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
		$sessionobject->remove_session_var($class_num . '_start');

		$displayobject->update_html($displayobject->module_finished($this->_modulestring,
									$sessionobject->return_stats($class_num, '_time_taken'),
									$sessionobject->return_stats($class_num, '_objects_done'),
									$sessionobject->return_stats($class_num, '_objects_failed')
		));


		$sessionobject->set_session_var($class_num ,'FINISHED');
		$sessionobject->set_session_var('import_usergroup','done');
		$sessionobject->set_session_var('module','000');
		$sessionobject->set_session_var('autosubmit','0');
		$displayobject->update_html($displayobject->print_redirect('index.php','1'));


	}// End resume
}//End Class
# Autogenerated on : December 22, 2004, 5:35 pm
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 003.php,v $ - $Revision: 1.4 $
|| ####################################################################
\*======================================================================*/
?>
