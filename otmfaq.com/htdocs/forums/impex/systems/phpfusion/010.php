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
* phpfusion_010 Import Attachment module
*
* @package			ImpEx.phpfusion
* @version			$Revision: 1.3 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @date				$Date: 2006/04/03 10:47:20 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class phpfusion_010 extends phpfusion_000
{
	var $_dependent 	= '';


	function phpfusion_010(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['import_attachment'];
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_attachments'))
				{
					$displayobject->display_now("<h4>{$displayobject->phrases['attachments_cleared']}</h4>");
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['attachment_restart_failed'], $displayobject->phrases['check_db_permissions']);
				}
			}


			// Start up the table
			$displayobject->update_basic('title', $displayobject->phrases['import_attachment']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['attachments_per_page'],'attachmentperpage',500));


			// End the table
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('attachmentstartat','0');
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
		$attachment_start_at			= $sessionobject->get_session_var('attachmentstartat');
		$attachment_per_page			= $sessionobject->get_session_var('attachmentperpage');
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of attachment details
		$attachment_array 	= $this->get_phpfusion_attachment_details($Db_source, $source_database_type, $source_table_prefix, $attachment_start_at, $attachment_per_page);


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
		$displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . count($attachment_array) . " {$displayobject->phrases['attachments']}</h4><p><b>{$displayobject->phrases['from']}</b> : " . $attachment_start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . ($attachment_start_at + count($attachment_array)) . "</p>");


		$attachment_object = new ImpExData($Db_target, $sessionobject, 'attachment');


		foreach ($attachment_array as $attachment_id => $attachment_details)
		{
			$try = (phpversion() < '5' ? $attachment_object : clone($attachment_object));
			// Mandatory
			$try->set_value('mandatory', 'importattachmentid',		$attachment_details['importattachmentid']);
			$try->set_value('mandatory', 'filename',		$attachment_details['filename']);
			$try->set_value('mandatory', 'filedata',		$attachment_details['filedata']);


			// Non Mandatory
			$try->set_value('nonmandatory', 'extension',		$attachment_details['extension']);
			$try->set_value('nonmandatory', 'thumbnail_dateline',		$attachment_details['thumbnail_dateline']);
			$try->set_value('nonmandatory', 'thumbnail',		$attachment_details['thumbnail']);
			$try->set_value('nonmandatory', 'posthash',		$attachment_details['posthash']);
			$try->set_value('nonmandatory', 'filehash',		$attachment_details['filehash']);
			$try->set_value('nonmandatory', 'postid',		$attachment_details['postid']);
			$try->set_value('nonmandatory', 'filesize',		$attachment_details['filesize']);
			$try->set_value('nonmandatory', 'counter',		$attachment_details['counter']);
			$try->set_value('nonmandatory', 'visible',		$attachment_details['visible']);
			$try->set_value('nonmandatory', 'dateline',		$attachment_details['dateline']);
			$try->set_value('nonmandatory', 'userid',		$attachment_details['userid']);


			// Check if attachment object is valid
			if($try->is_valid())
			{
				if($try->import_attachment($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span>' . $displayobject->phrases['attachment'] . ' -> ' . $try->get_value('mandatory',''));
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error($attachment_id, $displayobject->phrases['attachment_not_imported'], $displayobject->phrases['attachment_not_imported_rem']);
					$displayobject->display_now("<br />{$impex_phrases['failed']} :: {$displayobject->phrases['attachment_not_imported']}");
				}
			}
			else
			{
				$displayobject->display_now("<br />{$impex_phrases['invalid_object']}" . $try->_failedon);
			}
			unset($try);
		}// End foreach


		// Check for page end
		if (count($attachment_array) == 0 OR count($attachment_array) < $attachment_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');


			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num, '_time_taken'),
				$sessionobject->return_stats($class_num, '_objects_done'),
				$sessionobject->return_stats($class_num, '_objects_failed')
			));


			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_attachment','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}


		$sessionobject->set_session_var('attachmentstartat',$attachment_start_at+$attachment_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
	}// End resume
}//End Class
# Autogenerated on : March 16, 2006, 12:05 pm
# By ImpEx-generator 2.1.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 010.php,v $ - $Revision: 1.3 $
|| ####################################################################
\*======================================================================*/
?>
