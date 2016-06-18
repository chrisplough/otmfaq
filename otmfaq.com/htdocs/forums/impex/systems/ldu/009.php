<?php
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
* ldu_009 Import Poll module
*
* @package			ImpEx.lud
* @version			$Revision: 1.1 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @date				$Date: 2006/05/27 00:47:17 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class ldu_009 extends ldu_000
{
	var $_dependent 	= '';


	function ldu_009(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['import_poll'];
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_polls'))
				{
					$displayobject->display_now("<h4>{$displayobject->phrases['polls_cleared']}</h4>");
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['poll_restart_failed'], $displayobject->phrases['check_db_permissions']);
				}
			}


			// Start up the table
			$displayobject->update_basic('title', $displayobject->phrases['import_poll']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['polls_per_page'],'pollperpage',500));


			// End the table
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('pollstartat','0');
		}
		else
		{
			// Dependant has not been run
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description("<p>{$displayobject->phrases['dependant_on']}<i><b> " . $sessionobject->get_module_title($this->_dependent) . "</b> {$displayobject->phrases['cant_run']}</i> ."));
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],''));
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
		$poll_start_at			= $sessionobject->get_session_var('pollstartat');
		$poll_per_page			= $sessionobject->get_session_var('pollperpage');
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of poll details
		$poll_array 	= $this->get_ldu_poll_details($Db_source, $source_database_type, $source_table_prefix, $poll_start_at, $poll_per_page);


		// Get some refrence arrays (use and delete as nessesary).
		// User info
		#$this->get_vb_userid($Db_target, $target_database_type, $target_table_prefix, $importuserid);
		#$this->get_one_username($Db_target, $target_database_type, $target_table_prefix, $theuserid, $id='importuserid');
		#$users_array = $this->get_user_array($Db_target, $databasetype, $tableprefix, $startat = null, $perpage = null);
		#$done_user_ids = $this->get_done_user_ids($Db_target, $target_database_type, $target_table_prefix);
		#$user_ids_array = $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix, $do_int_val = false);
		#$user_name_array = $this->get_username($Db_target, $target_database_type, $target_table_prefix);
		// Groups info
		#$user_group_ids_array = $this->get_imported_group_ids($Db_target, $target_database_type, $target_table_prefix);
		#$user_group_ids_by_name_array = $this->get_imported_group_ids_by_name($Db_target, $target_database_type, $target_table_prefix);
		#$bannded_groupid = $this->get_banned_group($Db_target, $target_database_type, $target_table_prefix);
		// Thread info
		#$this->get_thread_id($Db_target, $target_database_type, $target_table_prefix, &$importthreadid, &$forumid); // & left to show refrence
		#$thread_ids_array = $this->get_threads_ids($Db_target, $target_database_type, $target_table_prefix);
		// Post info
		#$this->get_posts_ids($Db_target, $target_database_type, $target_table_prefix);
		#$this->get_vb_post_id($Db_target, $target_database_type, $target_table_prefix, $import_post_id);
		// Category info
		#$cat_ids_array = $this->get_category_ids($Db_target, $target_database_type, $target_table_prefix);
		#$style_ids_array = $this->get_style_ids($Db_target, $target_database_type, $target_table_prefix, $pad=0);
		// Forum info
		#$forum_ids_array = $this->get_forum_ids($Db_target, $target_database_type, $target_table_prefix, $pad=0);


		// Display count and pass time
		$displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . count($poll_array) . " {$displayobject->phrases['polls']}</h4><p><b>{$displayobject->phrases['from']}</b> : " . $poll_start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . ($poll_start_at + count($poll_array)) . "</p>");


		$poll_object = new ImpExData($Db_target, $sessionobject, 'poll');


		foreach ($poll_array as $poll_id => $poll_details)
		{
			$try = (phpversion() < '5' ? $poll_object : clone($poll_object));
			// Mandatory
			$try->set_value('mandatory', 'importpollid',		$poll_details['importpollid']);
			$try->set_value('mandatory', 'question',		$poll_details['question']);
			$try->set_value('mandatory', 'dateline',		$poll_details['dateline']);
			$try->set_value('mandatory', 'options',		$poll_details['options']);
			$try->set_value('mandatory', 'votes',		$poll_details['votes']);


			// Non Mandatory
			$try->set_value('nonmandatory', 'lastvote',		$poll_details['lastvote']);
			$try->set_value('nonmandatory', 'public',		$poll_details['public']);
			$try->set_value('nonmandatory', 'voters',		$poll_details['voters']);
			$try->set_value('nonmandatory', 'multiple',		$poll_details['multiple']);
			$try->set_value('nonmandatory', 'timeout',		$poll_details['timeout']);
			$try->set_value('nonmandatory', 'numberoptions',		$poll_details['numberoptions']);
			$try->set_value('nonmandatory', 'active',		$poll_details['active']);


			// Check if poll object is valid
			if($try->is_valid())
			{
				if($try->import_poll($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span>' . $displayobject->phrases['poll'] . ' -> ' . $try->get_value('mandatory',''));
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error($poll_id, $displayobject->phrases['poll_not_imported'], $displayobject->phrases['poll_not_imported_rem']);
					$displayobject->display_now("<br />{$impex_phrases['failed']} :: {$displayobject->phrases['poll_not_imported']}");
				}
			}
			else
			{
				$displayobject->display_now("<br />{$impex_phrases['invalid_object']}" . $try->_failedon);
			}
			unset($try);
		}// End foreach


		// Check for page end
		if (count($poll_array) == 0 OR count($poll_array) < $poll_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');


			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num, '_time_taken'),
				$sessionobject->return_stats($class_num, '_objects_done'),
				$sessionobject->return_stats($class_num, '_objects_failed')
			));


			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_poll','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}


		$sessionobject->set_session_var('pollstartat',$poll_start_at+$poll_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
	}// End resume
}//End Class
# Autogenerated on : May 19, 2006, 7:40 pm
# By ImpEx-generator 2.1.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 009.php,v $ - $Revision: 1.1 $
|| ####################################################################
\*======================================================================*/
?>
