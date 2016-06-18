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
* snitz_005 Import Thread module
*
* @package			ImpEx.snitz
* @version			$Revision: 1.8 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/05/26 21:20:46 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class snitz_005 extends snitz_000
{
	var $_dependent 	= '004';

	function snitz_005(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['import_thread']; 
	}

		function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_threads'))
				{
					$displayobject->display_now("<h4>{$displayobject->phrases['threads_cleared']}</h4>");
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['thread_restart_failed'], $displayobject->phrases['check_db_permissions']);
				}
			}

			// Start up the table
			$displayobject->update_basic('title',$displayobject->phrases['import_thread']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));

			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['threads_per_page'],'threadperpage',500));

			// End the table
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));

			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('threadstartat','0');
			$sessionobject->add_session_var('nonarchivefinished','FALSE');
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
		$thread_start_at		= $sessionobject->get_session_var('threadstartat');
		$thread_per_page		= $sessionobject->get_session_var('threadperpage');
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}

		$user_ids_array 	= $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix, $do_int_val = false);
		$user_name_array 	= $this->get_username($Db_target, $target_database_type, $target_table_prefix);
		$cat_ids_array		= $this->get_category_ids($Db_target, $target_database_type, $target_table_prefix);
		$forum_ids_array	= $this->get_forum_ids($Db_target, $target_database_type, $target_table_prefix, $pad=0);

		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($thread_array) . ' threads</h4><p><b>From</b> : ' . $thread_start_at . ' ::  <b>To</b> : ' . ($thread_start_at + count($thread_array)) . '</p>');

		$thread_object 	= new ImpExData($Db_target, $sessionobject, 'thread');
		$post_object	= new ImpExData($Db_target, $sessionobject, 'post');

		if($sessionobject->get_session_var('nonarchivefinished') == 'FALSE') # NOW DO THE NORMAL THREADS
		{
			$thread_array 		= $this->get_snitz_thread_details($Db_source, $source_database_type, $source_table_prefix, $thread_start_at, $thread_per_page);
			
			foreach ($thread_array as $thread_id => $thread_details)
			{
				$try = (phpversion() < '5' ? $thread_object : clone($thread_object));
				// Mandatory
				$try->set_value('mandatory', 'title',				$thread_details['T_SUBJECT']);
				$try->set_value('mandatory', 'forumid',				$forum_ids_array["$thread_details[FORUM_ID]"]);
				$try->set_value('mandatory', 'importthreadid',		$thread_details['TOPIC_ID']);
				$try->set_value('mandatory', 'importforumid',		$thread_details['FORUM_ID']);
	
				// Non Mandatory
				$try->set_value('nonmandatory', 'open',				$thread_details['T_STATUS']);
				$try->set_value('nonmandatory', 'replycount',		$thread_details['T_REPLIES']);
				$try->set_value('nonmandatory', 'postusername',		$user_name_array["$thread_details[T_AUTHOR]"]);
				$try->set_value('nonmandatory', 'postuserid',		$user_ids_array["$thread_details[T_AUTHOR]"]);
				$try->set_value('nonmandatory', 'lastposter',		$user_ids_array["$thread_details[T_LAST_POST_AUTHOR]"]);
				$try->set_value('nonmandatory', 'dateline',			$this->time_to_stamp($thread_details['T_DATE']));
				$try->set_value('nonmandatory', 'views',			$thread_details['T_VIEW_COUNT']);
				$try->set_value('nonmandatory', 'visible',			'1');
				$try->set_value('nonmandatory', 'sticky',			$thread_details['T_STICKY']);
	
				// Check if thread object is valid
				if($try->is_valid())
				{
					$thread_id = $try->import_thread($Db_target, $target_database_type, $target_table_prefix);
					if($thread_id)
					{
						$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['thread'] . ' -> ' . $try->get_value('mandatory','title'));
						$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
	
	
						// Also do the first post as that is in the thread details as well
						$first_post = $post_object;
	
						// Mandatory
						$first_post->set_value('mandatory', 'threadid',			$thread_id);
						$first_post->set_value('mandatory', 'userid',			$user_ids_array["$thread_details[T_AUTHOR]"]);
						$first_post->set_value('mandatory', 'importthreadid',	$thread_details['TOPIC_ID']);
	
	
						// Non Mandatory
						$first_post->set_value('nonmandatory', 'parentid',		'0');
						$first_post->set_value('nonmandatory', 'username',		$user_name_array["$thread_details[T_AUTHOR]"]);
						$first_post->set_value('nonmandatory', 'title',			$thread_details['T_SUBJECT']);
						$first_post->set_value('nonmandatory', 'dateline',		$this->time_to_stamp($thread_details['T_DATE']));
						$first_post->set_value('nonmandatory', 'pagetext',		$this->snitz_html($this->html_2_bb($thread_details['T_MESSAGE'])));
						$first_post->set_value('nonmandatory', 'ipaddress',		$thread_details['T_IP']);
						$first_post->set_value('nonmandatory', 'visible',		'1');
						$first_post->set_value('nonmandatory', 'allowsmilie',	'1');
	
						// Check if post object is valid
						if($first_post->is_valid())
						{
							if($first_post->import_post($Db_target, $target_database_type, $target_table_prefix))
							{
								$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['post'] . ' -> ' . $first_post->get_value('nonmandatory','username'));
								$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
							}
							else
							{
								$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
								$sessionobject->add_error($post_id, $displayobject->phrases['post_not_imported'], $displayobject->phrases['post_not_imported_rem']);
								$displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['post_not_imported']}");				
							}
						}
						else
						{
							$displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
							$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						}
						unset($first_post);
	
					}
					else
					{
						$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						$sessionobject->add_error($post_id, $displayobject->phrases['post_not_imported'], $displayobject->phrases['post_not_imported_rem']);
						$displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['post_not_imported']}");				
					}
				}
				else
				{
					$displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
				}
				unset($try);
			}// End resume
	
			// Check for page end
			if (count($thread_array) == 0 OR count($thread_array) < $thread_per_page)
			{
				$sessionobject->add_session_var('nonarchivefinished','TRUE');
				$sessionobject->add_session_var('threadstartat','0');
				$sessionobject->add_session_var('threaddone','0');
				$displayobject->update_html($displayobject->print_redirect('index.php'));
			}
			else
			{
				$sessionobject->set_session_var('threadstartat',$thread_start_at+$thread_per_page);
				$displayobject->update_html($displayobject->print_redirect('index.php'));
			}
		}
		else # NOW DO THE ARCHIVE THREADS
		{
			$thread_array = $this->get_snitz_archive_thread_details($Db_source, $source_database_type, $source_table_prefix, $thread_start_at, $thread_per_page);
			
			foreach ($thread_array as $thread_id => $thread_details)
			{
				$try = (phpversion() < '5' ? $thread_object : clone($thread_object));
				// Mandatory
				$try->set_value('mandatory', 'title',				$thread_details['T_SUBJECT']);
				$try->set_value('mandatory', 'forumid',				$forum_ids_array["$thread_details[FORUM_ID]"]);
				$try->set_value('mandatory', 'importthreadid',		$thread_details['TOPIC_ID']);
				$try->set_value('mandatory', 'importforumid',		$thread_details['FORUM_ID']);
	
				// Non Mandatory
				$try->set_value('nonmandatory', 'open',				$thread_details['T_STATUS']);
				$try->set_value('nonmandatory', 'replycount',		$thread_details['T_REPLIES']);
				$try->set_value('nonmandatory', 'postusername',		$user_name_array["$thread_details[T_AUTHOR]"]);
				$try->set_value('nonmandatory', 'postuserid',		$user_ids_array["$thread_details[T_AUTHOR]"]);
				$try->set_value('nonmandatory', 'lastposter',		$user_ids_array["$thread_details[T_LAST_POST_AUTHOR]"]);
				$try->set_value('nonmandatory', 'dateline',			$this->time_to_stamp($thread_details['T_DATE']));
				$try->set_value('nonmandatory', 'views',			$thread_details['T_VIEW_COUNT']);
				$try->set_value('nonmandatory', 'visible',			'1');
				$try->set_value('nonmandatory', 'sticky',			$thread_details['T_STICKY']);
	
				// Check if thread object is valid
				if($try->is_valid())
				{
					$thread_id = $try->import_thread($Db_target, $target_database_type, $target_table_prefix);
					if($thread_id)
					{
						$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['thread'] . ' -> ' . $try->get_value('mandatory','title'));
						$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
	
						// Also do the first post as that is in the thread details as well
						$first_post = $post_object;
	
						// Mandatory
						$first_post->set_value('mandatory', 'threadid',			$thread_id);
						$first_post->set_value('mandatory', 'userid',			$user_ids_array["$thread_details[T_AUTHOR]"]);
						$first_post->set_value('mandatory', 'importthreadid',	$thread_details['TOPIC_ID']);
	
	
						// Non Mandatory
						$first_post->set_value('nonmandatory', 'parentid',		'0');
						$first_post->set_value('nonmandatory', 'username',		$user_name_array["$thread_details[T_AUTHOR]"]);
						$first_post->set_value('nonmandatory', 'title',			$thread_details['T_SUBJECT']);
						$first_post->set_value('nonmandatory', 'dateline',		$this->time_to_stamp($thread_details['T_DATE']));
						$first_post->set_value('nonmandatory', 'pagetext',		$this->snitz_html($this->html_2_bb($thread_details['T_MESSAGE'])));
						$first_post->set_value('nonmandatory', 'ipaddress',		$thread_details['T_IP']);
						$first_post->set_value('nonmandatory', 'visible',		'1');
						$first_post->set_value('nonmandatory', 'allowsmilie',	'1');
	
						// Check if post object is valid
						if($first_post->is_valid())
						{
							if($first_post->import_post($Db_target, $target_database_type, $target_table_prefix))
							{
								$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['post'] . ' -> ' . $first_post->get_value('nonmandatory','username'));
								$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
							}
							else
							{
								$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
								$sessionobject->add_error($post_id, $displayobject->phrases['post_not_imported'], $displayobject->phrases['post_not_imported_rem']);
								$displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['post_not_imported']}");				
							}
						}
						else
						{
							$displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
							$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						}
						unset($first_post);
	
					}
					else
					{
						$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						$sessionobject->add_error($post_id, $displayobject->phrases['post_not_imported'], $displayobject->phrases['post_not_imported_rem']);
						$displayobject->display_now("<br />{$displayobject->phrases['failed']} :: {$displayobject->phrases['post_not_imported']}");				
					}
				}
				else
				{
					$displayobject->display_now("<br />{$displayobject->phrases['invalid_object']}" . $try->_failedon);
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
				}
				unset($try);
			}// End foreach			
			
			// The real end
			if (count($thread_array) == 0 OR count($thread_array) < $thread_per_page) 
			{
				$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
				$sessionobject->remove_session_var($class_num . '_start');
	
				$displayobject->update_html($displayobject->module_finished(
					"{$displayobject->phrases['import']} {$displayobject->phrases['threads']}",
					$sessionobject->return_stats($class_num, '_time_taken'),
					$sessionobject->return_stats($class_num, '_objects_done'),
					$sessionobject->return_stats($class_num, '_objects_failed')
				));
	
				$sessionobject->set_session_var($class_num ,'FINISHED');
				$sessionobject->set_session_var('import_thread','done');
				$sessionobject->set_session_var('module','000');
				$sessionobject->set_session_var('autosubmit','0');
				$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
			}
			else
			{
				$sessionobject->set_session_var('threadstartat',$thread_start_at+$thread_per_page);
				$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
			}
		}// End else
	}// End resume
}//End Class			
# Autogenerated on : May 20, 2004, 12:45 am
# By ImpEx-generator 1.0.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 005.php,v $ - $Revision: 1.8 $
|| ####################################################################
\*======================================================================*/
?>
