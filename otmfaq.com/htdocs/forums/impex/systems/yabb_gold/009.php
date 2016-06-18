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
* yabb_gold Import Attachments
*
* @package 		ImpEx.yabb_gold
* @version		$Revision: 1.3 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout 	$Name:  $
* @date 		$Date: 2006/04/03 03:15:50 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
class yabb_gold_009 extends yabb_gold_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '007';
	var $_modulestring 	= 'Import Attachments';

	function yabb_gold_009()
	{
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$proceed = $this->check_order($sessionobject,$this->_dependent);
		if ($proceed)
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_attachments'))
				{
					$this->_restart = FALSE;
					$displayobject->display_now("<h4>Imported attachments have been cleared</h4>");
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . "::restart failed , clear_imported_attachments",
											 'Check database permissions and attachemnts table');
				}
			}
			$displayobject->update_basic('title','Import attachments');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this), -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this), -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('attachment','working'));
			$displayobject->update_html($displayobject->make_table_header('Import Attachments'));
			$displayobject->update_html($displayobject->make_description('<p>The importer will now start to import the attachments from your Yabb-Gold board.</p>'));
			$displayobject->update_html($displayobject->make_input_code('Number of attachments to import per cycle','attachmentperpage','250'));
			$displayobject->update_html($displayobject->do_form_footer('Import Attachments',''));


			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');


			$sessionobject->add_session_var('attachmentstartat','0');
		}
		else
		{
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description('<p>This module is dependent on <i><b>' . $sessionobject->get_module_title($this->_dependent) . '</b></i> cannot run until that is complete.'));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));
			$sessionobject->set_session_var(substr(get_class($this), -3),'FALSE');
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
		$attachment_array 	= $this->get_yabb_gold_attachment_details($sessionobject->get_session_var('forumspath'), $attachment_start_at, $attachment_per_page);


		$user_ids_array = $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix);


		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($attachment_array) . ' attachments</h4><p><b>From</b> : ' . $attachment_start_at . ' ::  <b>To</b> : ' . ($attachment_start_at + count($attachment_array)) . '</p>');


		$attachment_object = new ImpExData($Db_target, $sessionobject, 'attachment');

		if(is_array($attachment_array))
		{
			foreach ($attachment_array as $attachment_id => $attachment_details)
			{
				$try = (phpversion() < '5' ? $attachment_object : clone($attachment_object));
				
				$dir 				= $sessionobject->get_session_var('uploadspath');
				$attachment_details = explode('|', $attachment_details);
				$filename 			= trim($attachment_details[7]); // Take off the new line
				
				$postid = $this->get_yabb_gold_attachment_extra_details($Db_target, $target_database_type, $target_table_prefix, $attachment_details[0], $attachment_details[1]);
				if(!$postid) // If there isn't a matching postid
				{
					$displayobject->display_now('<br> Cannot find imported post for attachment, skipping :: ' . $filename);
					continue;
				}
	
				/*
					0 - importthreadid 
					1 - post reply number
					2 - subject
					3 - username
					4 - current board
					5 - filesize
					6 - date
					7 - filename
				*/
				
				if(!is_file($dir . '/' . $filename))
				{
					$displayobject->display_now('<br /><b>Source file not found </b> :: attachment -> ' . $filename);
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					continue;
				}
	
				$file = $this->vb_file_get_contents( $dir . '/' . $filename);
		
				// Mandatory
				$try->set_value('mandatory', 'filename',				addslashes($filename));
				$try->set_value('mandatory', 'filedata',				$file);
				$try->set_value('mandatory', 'importattachmentid',		'1');
	
				// Non Mandatory
				$try->set_value('nonmandatory', 'dateline',				strtotime($attachment_details[6]));
				$try->set_value('nonmandatory', 'visible',				'1');
				$try->set_value('nonmandatory', 'counter',				$attachment_details['downloads']);
				$try->set_value('nonmandatory', 'filesize',				filesize($dir . '/' . $filename));
				$try->set_value('nonmandatory', 'postid',				$postid);
				$try->set_value('nonmandatory', 'filehash',				md5($file));
				#$try->set_value('nonmandatory', 'posthash',			$attachment_details['posthash']);
				#$try->set_value('nonmandatory', 'thumbnail',			$attachment_details['thumbnail']);
				#$try->set_value('nonmandatory', 'thumbnail_dateline',	$attachment_details['thumbnail_dateline']);
	
				if (!$file)
				{
					continue;
				}
	
				// Check if attachment object is valid
				if($try->is_valid())
				{
					if($try->import_attachment($Db_target, $target_database_type, $target_table_prefix, false))
					{
						$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: attachment -> ' . $filename);
						$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
					}
					else
					{
						$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
						$displayobject->display_now("<br />Found attachment and <b>DID NOT</b> imported to the  {$target_database_type} database possiably the origional post is missing");
					}
				}
				else
				{
					$displayobject->display_now("<br />Invalid attachment object, skipping." . $try->_failedon);
				}
				unset($try);
			}// End foreach
		}

		
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
			$displayobject->update_html($displayobject->print_redirect('index.php','1'));
		}


		$sessionobject->set_session_var('attachmentstartat',$attachment_start_at+$attachment_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php'));
	}// End resume
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 009.php,v $ - $Revision: 1.3 $
|| ####################################################################
\*======================================================================*/
?>
