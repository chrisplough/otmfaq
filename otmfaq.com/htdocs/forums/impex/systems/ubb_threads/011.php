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
* ubb_threads Import Attachments
*
*
* @package 		ImpEx.ubb_threads
* @version		$Revision: 1.8 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout 	$Name:  $
* @date 		$Date: 2006/04/03 09:58:48 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
class ubb_threads_011 extends ubb_threads_000
{
	var $_dependent 	= '007';

	function ubb_threads_011(&$displayobject)
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
					$displayobject->display_now("<h4>{$displayobject->phrases['attachment_restart_ok']}</h4>");
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['attachment_restart_failed'], $displayobject->phrases['check_db_permissions']);
				}
			}

			// Start up the table
			$displayobject->update_basic('title',$displayobject->phrases['import_attachment']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));

			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['attachments_per_page'],'attachmentperpage',250));
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['path_to_upload'], 'attachmentsfolder',$sessionobject->get_session_var('attachmentsfolder'),1,60));
		
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
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'], ''));
			$sessionobject->set_session_var(substr(get_class($this) , -3),'FALSE');
			$sessionobject->set_session_var('module','000');
		}
	}

	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$displayobject->update_basic('displaymodules','FALSE');

		// Set up working variables.
		$target_database_type 	= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix  	= $sessionobject->get_session_var('targettableprefix');

		$source_database_type 	= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix  	= $sessionobject->get_session_var('sourcetableprefix');

		$start_at				= $sessionobject->get_session_var('attachmentstartat');
		$per_page				= $sessionobject->get_session_var('attachmentperpage');

		$class_num		= 	substr(get_class($this) , -3);

		if(intval($per_page) == 0)
		{
			$per_page = 200;
		}

		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num,'start' ,$sessionobject->get_session_var('autosubmit'));
		}

		$attachment_array 		= $this->get_ubb_threads_attachment_details($Db_source, $source_database_type, $source_table_prefix, $start_at, $per_page);

		$attachment_object 		= new ImpExData($Db_target, $sessionobject,'attachment');

		$displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . count($attachment_array) . " {$displayobject->phrases['attachmnets']}</h4><p><b>{$displayobject->phrases['from']}</b> : " . $start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . ($start_at + count($attachment_array)) . "</p>");

		foreach ($attachment_array as $post_id => $details)
		{
			$the_file = $this->get_ubb_threads_attachment($sessionobject->get_session_var('attachmentsfolder') , $details['B_File']);

			if($the_file AND $details['B_File'] != '')
			{
				$try = (phpversion() < '5' ? $attachment_object : clone($attachment_object));

				$try->set_value('mandatory', 'importattachmentid',	$post_id);
				$try->set_value('mandatory', 'filename',			$details['B_File']);
				$try->set_value('mandatory', 'filedata',			$the_file['data']);

				$try->set_value('nonmandatory', 'dateline',			$details['B_Posted']);
				$try->set_value('nonmandatory', 'visible',			'1');
				$try->set_value('nonmandatory', 'counter',			$details['B_FileCounter']);
				$try->set_value('nonmandatory', 'filesize',			$the_file['filesize']);
				$try->set_value('nonmandatory', 'postid',			$post_id);
				$try->set_value('nonmandatory', 'filehash',			$the_file['filehash']);

				if($try->is_valid())
				{
					if($try->import_attachment($Db_target,$target_database_type,$target_table_prefix))
					{
						$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['attachment'] . ' -> ' . $try->get_value('mandatory','filename'));
						$sessionobject->add_session_var($class_num . '_objects_done', intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1);
					}
					else
					{
						$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						$sessionobject->add_error($attachment_id, $displayobject->phrases['attachment_not_imported'], $displayobject->phrases['attachment_not_imported_rem_2']);
						$displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['attachment_not_imported']}");
					}
				}
				else
				{
					$displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
				}
				unset($try);
			}
			else
			{
				$displayobject->display_now("<br /><b>{$displayobject->phrases['source_file_not']} </b> :: {$details['B_File']}");
				$sessionobject->add_error($attachment_id, $displayobject->phrases['attachment_not_imported'], $details['B_File'] . ' - ' . $displayobject->phrases['attachment_not_imported_rem_1']);
				$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
			}
		}

		if (count($attachment_array) == 0 OR count($attachment_array) < $per_page)
		{
			$sessionobject->timing($class_num ,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num , '_time_taken'),
				$sessionobject->return_stats($class_num , '_objects_done'),
				$sessionobject->return_stats($class_num , '_objects_failed')
			));

			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}
		else
		{
			$sessionobject->set_session_var('attachmentstartat',$start_at+$per_page);
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 011.php,v $ - $Revision: 1.8 $
|| ####################################################################
\*======================================================================*/
?>
