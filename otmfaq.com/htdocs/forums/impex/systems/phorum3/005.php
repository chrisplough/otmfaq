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
* phorum3_005 Import Thread module
*
* @package			ImpEx.phorum3
* @version			$Revision: 1.6 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/04/03 09:34:32 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class phorum3_005 extends phorum3_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '004';
	var $_modulestring 	= 'Import Thread';


	function phorum3_005()
	{
		// Constructor
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_threads'))
				{
					$displayobject->display_now('<h4>Imported threads have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_threads','Check database permissions');
				}
			}


			// Start up the table
			$displayobject->update_basic('title','Import Thread');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_thread','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code('Threads to import per cycle (must be greater than 1)','threadperpage',1000));


			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');

			$sdt = $sessionobject->get_session_var('sourcedatabasetype');
			$stp = $sessionobject->get_session_var('sourcetableprefix');
			$sessionobject->add_session_var('currentforumloop', '1');

			$details = $this->get_first_forum_name($Db_source,$sdt,$stp);

			$sessionobject->add_session_var('sourceforumtablename', $details['name']);
			$sessionobject->add_session_var('sourceforumid',  $details['id']);

			$sessionobject->add_session_var('threadstartat','0');
			$sessionobject->add_session_var('threaddone','0');
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
		$thread_start_at		= $sessionobject->get_session_var('threadstartat');
		$thread_per_page		= $sessionobject->get_session_var('threadperpage');
		$class_num				= substr(get_class($this) , -3);

		$sourceforumtablename	= $sessionobject->get_session_var('sourceforumtablename');

		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of thread details
		$thread_array 	= $this->get_phorum3_thread_details($Db_source, $source_database_type, $source_table_prefix, $thread_start_at, $thread_per_page, $sessionobject->get_session_var('sourceforumtablename'));

		$user_ids_array = $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix, $do_int_val = false);
		$forum_ids_array = $this->get_forum_ids($Db_target, $target_database_type, $target_table_prefix, $pad=0);


		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($thread_array) . ' threads</h4><p><b>From</b> : ' . $thread_start_at . ' ::  <b>To</b> : ' . ($thread_start_at + count($thread_array)) . '</p>');


		$thread_object = new ImpExData($Db_target, $sessionobject, 'thread');
		$importforumid = $sessionobject->get_session_var('sourceforumid');


		foreach ($thread_array as $thread_id => $thread_details)
		{
 
			
			$try = (phpversion() < '5' ? $thread_object : clone($thread_object));
			// Mandatory
			$try->set_value('mandatory', 'title',				$thread_details['subject']);
			$try->set_value('mandatory', 'forumid',				$forum_ids_array[$importforumid]);
			$try->set_value('mandatory', 'importthreadid',		$thread_id);
			$try->set_value('mandatory', 'importforumid',		$importforumid);


			// Non Mandatory
			#$try->set_value('nonmandatory', 'firstpostid',		$thread_details['firstpostid']);
			#$try->set_value('nonmandatory', 'lastpost',			$thread_details['lastpost']);
			#$try->set_value('nonmandatory', 'pollid',			$thread_details['pollid']);

			if($thread_details['closed'])
			{
				$try->set_value('nonmandatory', 'open',			'0');
			}
			else
			{
				$try->set_value('nonmandatory', 'open',			'1');
			}

			#$try->set_value('nonmandatory', 'replycount',		$thread_details['replycount']);
			$try->set_value('nonmandatory', 'postusername',		$thread_details['author']);
			$try->set_value('nonmandatory', 'postuserid',		$user_ids_array["$thread_details[userid]"]);
			#$try->set_value('nonmandatory', 'lastposter',		$thread_details['lastposter']);
			$try->set_value('nonmandatory', 'dateline',			strtotime($thread_details['datestamp']));
			#$try->set_value('nonmandatory', 'views',			$thread_details['views']);
			#$try->set_value('nonmandatory', 'iconid',			');
			$try->set_value('nonmandatory', 'notes',			'Imported thread');
			$try->set_value('nonmandatory', 'visible',			'1');
			$try->set_value('nonmandatory', 'sticky',			'0');
			#$try->set_value('nonmandatory', 'votenum',			$thread_details['votenum']);
			#$try->set_value('nonmandatory', 'votetotal',		$thread_details['votetotal']);
			#$try->set_value('nonmandatory', 'attach',			$thread_details['attach']);
			#$try->set_value('nonmandatory', 'similar',			$thread_details['similar']);


			// Check if thread object is valid
			if($try->is_valid())
			{
				if($try->import_thread($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: thread -> ' . $thread_details['subject']);
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
					$displayobject->display_now("<br />Found avatar thread and <b>DID NOT</b> imported to the  {$target_database_type} database");
				}
			}
			else
			{
				$displayobject->display_now("<br />Invalid thread object, skipping." . $try->_failedon);
			}
			unset($try);
		}// End foreach


		// Check for page end
		if (count($thread_array) == 0 OR count($thread_array) < $thread_per_page)
		{
			// If we are here the thread count is less that the per page
			// Though that could mean that be are moving between forums


			// Set to the next id
			$sessionobject->add_session_var('currentforumloop',intval($sessionobject->get_session_var('currentforumloop'))+1);


			// Get the details for the next pass
			$next_forum_details = $this->get_phorum3_forum_step($Db_source, $source_database_type, $source_table_prefix, $sessionobject->get_session_var('currentforumloop'));

			// Start back at the beginning
			$sessionobject->set_session_var('threadstartat','0');

			$sessionobject->add_session_var('sourceforumid', $next_forum_details['id']);
			$sessionobject->add_session_var('sourceforumtablename', $next_forum_details['table_name']);


			if($sessionobject->get_session_var('currentforumloop') > intval($next_forum_details['count']))
			{
				// We have done as many that are in the dB
				// Done so we don't have to rely on the id in the database as they could be out of sync

				$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
				$sessionobject->remove_session_var($class_num . '_start');


				$displayobject->update_html($displayobject->module_finished($this->_modulestring,
					$sessionobject->return_stats($class_num, '_time_taken'),
					$sessionobject->return_stats($class_num, '_objects_done'),
					$sessionobject->return_stats($class_num, '_objects_failed')
				));


				$sessionobject->set_session_var($class_num ,'FINISHED');
				$sessionobject->set_session_var('import_thread','done');
				$sessionobject->set_session_var('module','000');
				$sessionobject->set_session_var('autosubmit','0');
				$displayobject->update_html($displayobject->print_redirect('index.php','1'));
			}
			else
			{
				$jumping_forums = true;

			}
		}

		if($jumping_forums)
		{
			$sessionobject->set_session_var('threadstartat','0');
		}
		else
		{
			$sessionobject->set_session_var('threadstartat',$thread_start_at+$thread_per_page);
		}

		$displayobject->update_html($displayobject->print_redirect('index.php'));
	}// End resume
}//End Class
# Autogenerated on : September 24, 2004, 2:23 pm
# By ImpEx-generator 1.0.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 005.php,v $ - $Revision: 1.6 $
|| ####################################################################
\*======================================================================*/
?>
