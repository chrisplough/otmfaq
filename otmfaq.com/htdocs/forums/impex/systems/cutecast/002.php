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
* cutecast
/**
* cutecast_001 Associate Users
*
* @package			ImpEx.cutecast
* @version			$Revision: 1.3 $
* @author			Scott MacVicar <scott.macvicar@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/04/03 02:44:10 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class cutecast_002 extends cutecast_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '001';
	var $_modulestring 	= 'Associate Users';


	function cutecast_002()
	{
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$proceed = $this->check_order($sessionobject,$this->_dependent);
		if ($proceed)
		{
			if($sessionobject->get_session_var('associateperpage') == 0)
			{
				$sessionobject->add_session_var('associateperpage','25');
			}


			$displayobject->update_basic('title','Assocatie users');
			$displayobject->update_html($displayobject->do_form_header('index','002'));
			$displayobject->update_html($displayobject->make_hidden_code('002','WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('associateusers','1'));
			$displayobject->update_html($displayobject->make_table_header('Associate Users'));
			$displayobject->update_html($displayobject->make_description('<h4>Warning !!</h4> Assosiated users will currently be deleted if you run the import user module twice as it removes users with an importuserid'));
			$displayobject->update_html($displayobject->make_description('<p>If you want to associate a Cutecast user (in the left column) with an existing vBulletin user,
					enter the user id number of the vBulletin user in the box provided, and click the <i>Associate Users</i> button.<br /><br />
					To view the list of existing vBulletin users, together with their userid'));
			$displayobject->update_html($displayobject->make_input_code('Users per page','associateperpage',$sessionobject->get_session_var('associateperpage'),1,60));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));


			$sessionobject->add_session_var('doassociate','0');
			$sessionobject->add_session_var('associatestartat','0');
		}
		else
		{
			$sessionobject->add_error('notice',
									 $this->_modulestring,
									 "$this->_modulestring:init Called when dependent not complete.",
									 'Call the oduiles in the correct order');
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description('<p>This module is dependent on <i><b>' . $sessionobject->get_module_title($this->_dependent) . '</b></i> cannot run until that is complete.'));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));
			$sessionobject->set_session_var('002','FALSE');
			$sessionobject->set_session_var('module','000');
		}
	}


	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		// Turn off the modules display
		$displayobject->update_basic('displaymodules','FALSE');

		// Get some usable variables
		$associate_users		= 	$sessionobject->get_session_var('associateusers');
		$do_associate			=	$sessionobject->get_session_var('doassociate');
		$class_num				= 	substr(get_class($this) , -3);


		// Start the timings
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num, 'start', $sessionobject->get_session_var('autosubmit'));
		}


		//	List from the start_at number
		if ($associate_users == 1)
		{
			// Get some more usable local vars
			$associate_start_at   = $sessionobject->get_session_var('associatestartat');
			$associate_per_page   = $sessionobject->get_session_var('associateperpage');
			$target_database_type = $sessionobject->get_session_var('targetdatabasetype');
			$target_table_prefix  = $sessionobject->get_session_var('targettableprefix');

			// Get a list of all the user file (numbers) of the ubb members
			$userpath = $sessionobject->get_session_var('userpath');
			$userarray = $this->get_members_list($userpath, false, false);

			$nummembers = sizeof($userarray);

			// Build a list of the ubb users with a box to enter a vB user id into
			$displayobject->update_html($displayobject->do_form_header('index','002'));
			$displayobject->update_html($displayobject->make_table_header('Association list'));
			$displayobject->update_html($displayobject->make_description('Put the exsisting <b>vbulletin user id</b> next to the Cutecast user that you wish to associate them with'));

			// Set up list variables
			$any_more = false;
			$counter  = 1;

			// Advance the array pointer
			for ($i = 1; $i <= $associate_start_at; $i++)
			{
				next($userarray);
			}

			// Build the list
			while ( $counter <= $associate_per_page )
			{
				$ubb_user = each($userarray);
				$displayobject->update_html($displayobject->make_input_code("$counter) Userid - " . substr($ubb_user[1], 0, strpos($ubb_user[1], '.')),'user_to_ass_' . $ubb_user[0],'',10));
				$any_more = true;
				$counter++;
			}

			// If there arn't any more, tell the user and quit out for them.
			if(!$any_more)
			{
				$displayobject->update_html($displayobject->make_description('There are <b>NO</b> more vBulletin users, quit to continue.'));
			}
			else
			{
				$sessionobject->set_session_var('associatestartat',$associate_start_at + $associate_per_page);
			}

			// Continue with the association
			$sessionobject->set_session_var('associateusers','0');
			$sessionobject->set_session_var('doassociate','1');
			$displayobject->update_html($displayobject->make_hidden_code('doassociate','1'));
			$displayobject->update_html($displayobject->do_form_footer('Associate',''));

			// Quit button
			$displayobject->update_html($displayobject->do_form_header('index','002'));
			$displayobject->update_html($displayobject->make_hidden_code('associateusers','2'));
			$displayobject->update_html($displayobject->make_hidden_code('doassociate','0'));
			$displayobject->update_html($displayobject->do_form_footer('Quit',''));
		}


		//	If there are some to assosiate
		if ($do_associate == 1)
		{
			$displayobject->update_html($displayobject->display_now('<p align="center">Associating the users...</p>'));

			$users_to_associate = $sessionobject->get_users_to_associate();

			foreach ($users_to_associate as $key => $value)
			{
				if($this->associate_user($Db_target,
									  $sessionobject->get_session_var('targetdatabasetype'),
									  $sessionobject->get_session_var('targettableprefix'),
									  substr(key($value),12),
									  current($value)))
				{
					$displayobject->update_html($displayobject->display_now('<p align="center">Ubb user ' .  substr(key($value),12) . ' done.</p>'));
				}
				else
				{
					$displayobject->update_html($displayobject->display_now('<p align="center">' . substr(key($value),12) . ' NOT done. It is most likely that vBulletin user ' . current($value) . ' dose not exsist.</p>'));
				}
			}

			$sessionobject->delete_users_to_associate();

			// Continue with the association
			$sessionobject->set_session_var('associateusers','1');
			$sessionobject->set_session_var('doassociate','0');
			$displayobject->update_html($displayobject->display_now('<p align="center">Continuing with the association.</p>'));
			$displayobject->update_html($displayobject->print_redirect('index.php','1'));
		}


		//	Finish the module
		if ($associate_users == 2)
		{
			$sessionobject->set_session_var('002','FINISHED');
			$sessionobject->set_session_var('module','000');

			$displayobject->update_html($displayobject->display_now('<p align="center">Quitting back to main menu.</p>'));
			$displayobject->update_html($displayobject->print_redirect('index.php','1'));
		}
	}
}// End class
# Autogenerated on : June 9, 2004, 6:55 pm
# By ImpEx-generator 1.0.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 002.php,v $ - $Revision: 1.3 $
|| ####################################################################
\*======================================================================*/
?>
