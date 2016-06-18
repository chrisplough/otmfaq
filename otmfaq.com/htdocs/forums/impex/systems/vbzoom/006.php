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
* vbzoom_006 Import Post module
*
* @package			ImpEx.vbzoom
* @version			$Revision: 1.6 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/04/03 09:36:58 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class vbzoom_006 extends vbzoom_000
{
	var $_dependent 	= '005';

	function vbzoom_006(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['import_post']; 
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				$displayobject->display_now($displayobject->phrases['no_rerun']);
				die;
			}

			// Start up the table
			$displayobject->update_basic('title', $displayobject->phrases['import_post']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($displayobject->phrases['import_post']));

			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['posts_per_page'],'postperpage',2000));
			$displayobject->update_html($displayobject->make_description($displayobject->phrases['no_rerun']));
			

			// End the table
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));

			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('poststartat','0');
			$sessionobject->add_session_var('postdone','0');
		}
		else
		{
			// Dependant has not been run
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description("<p>{$displayobject->phrases['dependant_on']}<i><b> " . $sessionobject->get_module_title($this->_dependent) . "</b> {$displayobject->phrases['cant_run']}</i> ."));
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'], ''));
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
		$post_start_at			= $sessionobject->get_session_var('poststartat');
		$post_per_page			= $sessionobject->get_session_var('postperpage');
		$class_num				= substr(get_class($this) , -3);

		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}

		// Get an array of post details
		$post_array 	= $this->get_vbzoom_post_details($Db_source, $source_database_type, $source_table_prefix, $post_start_at, $post_per_page);

		$thread_ids_array 		= $this->get_threads_ids($Db_target, $target_database_type, $target_table_prefix);
		$username_to_ids_array 	= $this->get_username_to_ids($Db_target, $target_database_type, $target_table_prefix);
		
		// If the names were consistant or there were id's we wouldn't need this ! Don't blame me !
		$user_ids_array 		= $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix);
		
		// Display count and pass time
		$displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . count($post_array) . " {$displayobject->phrases['posts']}</h4><p><b>{$displayobject->phrases['from']}</b> : " . $post_start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . ($post_start_at + count($post_array)) . "</p>");

		$post_object = new ImpExData($Db_target, $sessionobject, 'post');

		foreach ($post_array as $post_id => $post_details)
		{
			$try = (phpversion() < '5' ? $post_object : clone($post_object));
			// Mandatory	
			$try->set_value('mandatory', 'threadid',			$thread_ids_array["$post_details[SubjectID]"]);
			
			$first_up 	= ucfirst($post_details['Replier']);
			$all_lower 	= strtolower($post_details['Replier']);
			$all_upper 	= strtoupper($post_details['Replier']);
			
			
			
			if ($username_to_ids_array["$post_details[Replier]"])
			{
				$try->set_value('mandatory', 'userid',				$username_to_ids_array["$post_details[Replier]"]);
			}
			else if ($username_to_ids_array[$first_up])
			{
				$try->set_value('mandatory', 'userid',				$username_to_ids_array[$first_up]);
			}
			else if ($username_to_ids_array[$all_lower])
			{
				$try->set_value('mandatory', 'userid',				$username_to_ids_array[$all_lower]);
			}
			else if ($username_to_ids_array[$all_upper])
			{
				$try->set_value('mandatory', 'userid',				$username_to_ids_array[$all_upper]);
			}				
			else
			{ 
				$userid = $this->last_ditch_name_search($Db_source, $source_database_type, $source_table_prefix, $post_details['Replier']);
				
				if ($userid)
				{
					$try->set_value('mandatory', 'userid',				$username_to_ids_array["$post_details[Replier]"]);
				}
				else
				{
					echo "<h1>'" . "Didn't get it" . "'</h1>";
					die;
				}
			}
			
			if ($try->get_value('mandatory', 'userid') == 0)
			{
				echo "<br>Couldn't get userid";
				
				/*
				echo "<h1>post id - '" . $post_id . "'</h1>"; 
				echo "<h1>Replier - '" . $post_details['Replier'] . "'</h1>";
				die;
				*/
			}
			
			$try->set_value('mandatory', 'importthreadid',		$post_details['SubjectID']);

			// Non Mandatory
			$try->set_value('nonmandatory', 'parentid',			'0');
			$try->set_value('nonmandatory', 'username',			$post_details['Replier']);
			$try->set_value('nonmandatory', 'title',			$post_details['ReplyTitle']);
			$try->set_value('nonmandatory', 'dateline',			$post_details['dateline']);
			$try->set_value('nonmandatory', 'pagetext',			$this->html_2_bb($post_details['Message']));
			$try->set_value('nonmandatory', 'allowsmilie',		'1');
			$try->set_value('nonmandatory', 'showsignature',	$post_details['ShowSignature']);
			$try->set_value('nonmandatory', 'ipaddress',		'127.0.0.1');
			$try->set_value('nonmandatory', 'iconid',			'0');
			$try->set_value('nonmandatory', 'visible',			'1');
			$try->set_value('nonmandatory', 'attach',			$post_details['attach']);
			$try->set_value('nonmandatory', 'importpostid',		$post_id);

			// Check if post object is valid
			if($try->is_valid())
			{
				if($try->import_post($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['post'] . ' -> ' . $try->get_value('nonmandatory','username'));
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error($post_id, $displayobject->phrases['post_not_imported'], $displayobject->phrases['post_not_imported_rem']);
					$displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['post_not_imported']}");					}
			}
			else
			{
				$displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
				$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
			}
			unset($try);
		}// End foreach

		// Check for page end
		if (count($post_array) == 0 OR count($post_array) < $post_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$displayobject->display_now($displayobject->phrases['updating_parent_id']);

			if ($this->update_post_parent_ids($Db_target, $target_database_type, $target_table_prefix))
			{
				$displayobject->display_now($displayobject->phrases['successful']);
			}
			else
			{
				$displayobject->display_now($displayobject->phrases['failed']);
			}

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num, '_time_taken'),
				$sessionobject->return_stats($class_num, '_objects_done'),
				$sessionobject->return_stats($class_num, '_objects_failed')
			));

			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_post','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}

		$sessionobject->set_session_var('poststartat',$post_start_at+$post_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
	}// End resume
}//End Class
# Autogenerated on : April 12, 2005, 4:16 pm
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 006.php,v $ - $Revision: 1.6 $
|| ####################################################################
\*======================================================================*/
?>
