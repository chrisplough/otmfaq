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
* sitenetbbs_004 Import Forum module
*
* @package			ImpEx.sitenetbbs
* @version			$Revision: 1.4 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/07/15 03:00:22 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class sitenetbbs_004 extends sitenetbbs_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '003';
	var $_modulestring 	= 'Import Forum';


	function sitenetbbs_004()
	{
		// Constructor
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_forums'))
				{
					$displayobject->display_now('<h4>Imported forums have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_forums','Check database permissions');
				}
			}

			// Start up the table
			$displayobject->update_basic('title','Import Forum');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_forum','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));

			// Ask some questions
			$displayobject->update_html($displayobject->make_description('This will import all categorys and forums in two pages'));

			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));

			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('forumstartat','0');
			$sessionobject->add_session_var('forumdone','0');
			$sessionobject->add_session_var('categoriesfinished','FALSE');
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
		$forum_start_at			= $sessionobject->get_session_var('forumstartat');
		$forum_per_page			= $sessionobject->get_session_var('forumperpage');
		$class_num				= substr(get_class($this) , -3);
		$path 					= $sessionobject->get_session_var('admindata');

		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}

		// Get an array of forum details

		if($sessionobject->get_session_var('categoriesfinished') == 'FALSE')
		{
			// Sort out the categories
			$cats = $this->get_sitenetbbs_cats($path);
			
			$displayobject->display_now("<h4>Importing " . count($categories_array) . " categories</h4>");

			$category_object = new ImpExData($Db_target, $sessionobject, 'forum');

			foreach ($cats as $cat_id => $cat_name)
			{
				$try = (phpversion() < '5' ? $category_object : clone($category_object));

				$try->set_value('mandatory', 'title', 				trim($cat_name));
				$try->set_value('mandatory', 'displayorder',		1);
				$try->set_value('mandatory', 'parentid',			'-1');
				$try->set_value('mandatory', 'importforumid',		'0');
				$try->set_value('mandatory', 'importcategoryid',	$cat_id);
				$try->set_value('mandatory', 'options',				$this->_default_cat_permissions);

				if($try->is_valid())
				{
					if($try->import_category($Db_target, $target_database_type, $target_table_prefix))
					{
						$displayobject->display_now("<br /><span class=\"isucc\"><b>" . $try->how_complete() . "%</b></span> :: {$cat_name}");
						$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
					}
					else
					{
						$sessionobject->add_error('warning',
												 $this->_modulestring,
												 get_class($this) . "::import_category failed for " . $cat_name . " get_phpbb2_categories_details was ok.",
												 'Check database permissions and user table');
						$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
						$displayobject->display_now("<br />Got category " . $cat_name . " and <b>DID NOT</b> imported to the " . $target_database_type . " database");
					}
				}
				else
				{
					$displayobject->display_now("<br />Invalid category object, skipping." . $try->_failedon);
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
				}
				unset($try);
			}
			$sessionobject->add_session_var('categoriesfinished','TRUE');
		}
		else
		{
			// Sort out the forums
			$forum_array  	=  $this->get_sitenetbbs_forum_details($path);
			
			$cat_ids 		=  $this->get_category_ids($Db_target, $target_database_type, $target_table_prefix);

			$displayobject->display_now("<h4>Importing " . count($forum_array) . " forums</h4><p><b>From</b> : " . $forum_start_at . " ::  <b>To</b> : " . ($forum_start_at + $forum_per_page) ."</p>");

			$forum_object = new ImpExData($Db_target, $sessionobject, 'forum');

			foreach ($forum_array as $cat_id => $forum_array)
			{
				foreach($forum_array AS $forumid => $forum_name)
				{
					$try = (phpversion() < '5' ? $forum_object : clone($forum_object));

					$try->set_value('mandatory', 'parentid',			$cat_ids[$cat_id]);
					$try->set_value('mandatory', 'title', 				$forum_name);
					$try->set_value('mandatory', 'displayorder',		'1');
					$try->set_value('mandatory', 'importforumid',		$forumid);
					$try->set_value('mandatory', 'importcategoryid',	'0');
					$try->set_value('mandatory', 'options',				$this->_default_forum_permissions);

					$try->set_value('nonmandatory', 'description', 		$forum_name);
					$try->set_value('nonmandatory', 'visible', 			'1');

					if($try->is_valid())
					{
						if($try->import_forum($Db_target, $target_database_type, $target_table_prefix))
						{
							$displayobject->display_now("<br /><span class=\"isucc\"><b>" . $try->how_complete() . "%</b></span> :: {$forum_name}");
							$sessionobject->add_session_var($class_num  . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
							$imported = true;
						}
						else
						{
							$sessionobject->add_error('warning',
													 $this->_modulestring,
													 get_class($this) . "::import_category failed for {$forum_name}get_categories_details was ok.",
													 'Check database permissions and user table');
							$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num . '_objects_failed') + 1 );
							$displayobject->display_now("<br />Got forum {$forum_name} and <b>DID NOT</b> imported to the " . $target_database_type . " database");
						}
					}
					else
					{
						$displayobject->display_now("<br />Invalid forum object, skipping." . $try->_failedon);
					}
					unset($try);
				}// End foreach
			}// End foreach


		$this->build_forum_child_lists($Db_target, $target_database_type, $target_table_prefix);

		$sessionobject->timing($class_num, 'stop', $sessionobject->get_session_var('autosubmit'));
		$sessionobject->remove_session_var($class_num . '_start');

		$displayobject->update_html($displayobject->module_finished($this->_modulestring,
			$sessionobject->return_stats($class_num, '_time_taken'),
			$sessionobject->return_stats($class_num, '_objects_done'),
			$sessionobject->return_stats($class_num, '_objects_failed')
		));

		$sessionobject->set_session_var($class_num, 'FINISHED');
		$sessionobject->set_session_var('forums', 'done');
		$sessionobject->set_session_var('module', '000');
		$sessionobject->set_session_var('autosubmit', '0');
		}
		$displayobject->update_html($displayobject->print_redirect('index.php'));
	}//End resume
}//End Class
# Autogenerated on : June 13, 2005, 1:57 pm
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 004.php,v $ - $Revision: 1.4 $
|| ####################################################################
\*======================================================================*/
?>
