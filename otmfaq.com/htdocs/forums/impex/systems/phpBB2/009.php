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
* phpBB2 Import Posts
*
* @package 		ImpEx.phpBB2
* @version		$Revision: 1.32 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/09/12 20:56:23 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/

class phpBB2_009 extends phpBB2_000
{
	var $_dependent 	= '007';

	function phpBB2_009(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['import_post']; 
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_posts'))
				{
					$displayobject->display_now("<h4>{$displayobject->phrases['post_restart_ok']}</h4>");
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['post_restart_failed'], $displayobject->phrases['check_db_permissions']);
				}
			}

			// Start up the table
			$displayobject->update_basic('title', $displayobject->phrases['import_post']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($displayobject->phrases['import_post']));

			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['posts_per_page'],'postperpage',2000));

			// End the table
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));

			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('poststartat','0');
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
		$target_database_type 	= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix  	= $sessionobject->get_session_var('targettableprefix');

		$source_database_type 	= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix  	= $sessionobject->get_session_var('sourcetableprefix');

		$post_start_at 			= $sessionobject->get_session_var('poststartat');
		$post_per_page 			= $sessionobject->get_session_var('postperpage');
		$class_num				= substr(get_class($this) , -3);

		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num,'start' ,$sessionobject->get_session_var('autosubmit'));
		}

		$post_array			= $this->get_phpbb2_posts_details($Db_source, $source_database_type, $source_table_prefix, $post_start_at, $post_per_page);
		$thread_ids			= $this->get_threads_ids($Db_target, $target_database_type, $target_table_prefix);
		$users_ids			= $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix);
		$user_names			= $this->get_username($Db_target, $target_database_type, $target_table_prefix);
		$truncated_smilies	= $this->get_phpbb_truncated_smilies($Db_source, $source_database_type, $source_table_prefix);

		$displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . count($post_array) . " {$displayobject->phrases['posts']}</h4><p><b>{$displayobject->phrases['from']}</b> : " . $post_start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . ($post_start_at + count($post_array)) . "</p>");


		$post_object = new ImpExData($Db_target, $sessionobject, 'post');

		foreach ($post_array as $post_id => $post)
		{
			$try = (phpversion() < '5' ? $post_object : clone($post_object));
			
			// Mandatory
			$try->set_value('mandatory', 'threadid', 			$thread_ids["$post[topic_id]"]);
			$try->set_value('mandatory', 'userid', 				$users_ids["$post[poster_id]"]);
			$try->set_value('mandatory', 'importthreadid', 		$post['topic_id']);
			
			// Non Mandatory
			$try->set_value('nonmandatory', 'visible', '1');
			$try->set_value('nonmandatory', 'dateline',			$post['post_time']);
			$try->set_value('nonmandatory', 'allowsmilie', 		$post['enable_smilies']);
			$try->set_value('nonmandatory', 'showsignature', 	$post['enable_sig']);

			if($post['poster_id'] <= 0)
			{
				$try->set_value('nonmandatory', 'username', 	$post['post_username']);
			}
			else
			{
				$try->set_value('nonmandatory', 'username', 	$user_names["$post[poster_id]"]);
			}

			$try->set_value('nonmandatory', 'ipaddress',		$this->reverse_ip($post['poster_ip']));

			unset($page_text);
			$page_text = $this->get_phpbb_post_text($Db_source, $source_database_type, $source_table_prefix, $post['post_id']);
		
			$try->set_value('nonmandatory', 'title', 			$page_text['post_subject']);
			$try->set_value('nonmandatory', 'pagetext', 		$this->html_2_bb($this->phpbb_html($page_text['post_text'],$truncated_smilies)));
			$try->set_value('nonmandatory', 'importpostid',		$post_id);

			if($try->is_valid())
			{
				if($try->import_post($Db_target, $target_database_type, $target_table_prefix))
				{
					if(shortoutput)
					{
						$displayobject->display_now('.');
					}
					else
					{					
						$displayobject->display_now('<br />' . $post_id . ' <span class="isucc"><b>' . $try->how_complete() . '%</b></span> ' . $displayobject->phrases['post'] . ' -> ' . $try->get_value('nonmandatory','username'));
					}
					
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
			unset($try);
		}

		if (count($post_array) == 0 OR count($post_array) < $post_per_page)
		{
			$displayobject->display_now($displayobject->phrases['updating_parent_id']);

			if ($this->update_post_parent_ids($Db_target, $target_database_type, $target_table_prefix))
			{
				$displayobject->display_now($displayobject->phrases['successful']);
			}
			else
			{
				$displayobject->display_now($displayobject->phrases['failed']);
			}

			// TODO :  redo the image cache so the smilies display
			// adminfunctions.php, build_image_cache($table)


			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num, '_time_taken'),
				$sessionobject->return_stats($class_num, '_objects_done'),
				$sessionobject->return_stats($class_num, '_objects_failed')
			));
			
			$sessionobject->set_session_var($class_num,'FINISHED');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}
		else
		{
			$sessionobject->set_session_var('poststartat',$post_start_at+$post_per_page);
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}
	}// End resume
}//End Class
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 009.php,v $ - $Revision: 1.32 $
|| ####################################################################
\*======================================================================*/
?>
