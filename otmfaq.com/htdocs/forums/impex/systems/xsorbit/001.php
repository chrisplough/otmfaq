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
* xsorbit_001 Check system module
*
* @package			ImpEx.xsorbit
* @version			$Revision: 1.6 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @date				$Date: 2006/09/12 21:11:27 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class xsorbit_001 extends xsorbit_000
{


	function xsorbit_001(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['check_update_db'];
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$displayobject->update_basic('title','Get database information');
		$displayobject->update_html($displayobject->do_form_header('index','001'));
		$displayobject->update_html($displayobject->make_table_header('Get database information'));
		$displayobject->update_html($displayobject->make_hidden_code('database','working'));


		$displayobject->update_html($displayobject->make_description('This module will check the tables in the database as well as the connection.'));


		$displayobject->update_html($displayobject->do_form_footer('Check database',''));
		$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
		$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
	}


	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		// Setup some working variables
		$displayobject->update_basic('displaymodules','FALSE');
		$target_db_type 		= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix 	= $sessionobject->get_session_var('targettableprefix');
		$source_db_type			= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix 	= $sessionobject->get_session_var('sourcetableprefix');


		$class_num        = substr(get_class($this) , -3);
		$databasedone     = true;


		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num, 'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		$displayobject->update_basic('title','Modifying database');
		$displayobject->display_now("<h4>Altering tables</h4>");
		$displayobject->display_now("<p>ImpEx will now Alter the tables in the vB database to include <i>import id numbers</i>.</p>");
		$displayobject->display_now("This is needed during the import process for maintaining refrences between the tables during an import.");
		$displayobject->display_now("If you have large tables (i.e. lots of posts) this can take some time.</p>");
		$displayobject->display_now("<p> They will also be left after the import if you need to link back to the origional vB userid.</p>");


		// Add an importids
		$fields = array (
				'0' 	=> array('moderator'		=>  'importmoderatorid'),
				'1'		=> array('usergroup'		=>  'importusergroupid'),
				'2' 	=> array('ranks'			=>  'importrankid'),
				'3' 	=> array('poll'				=>  'importpollid'),
				'4' 	=> array('forum'			=>  'importforumid'),
				'5' 	=> array('forum'			=>  'importcategoryid'),
				'6' 	=> array('user'				=>  'importuserid'),
				'7' 	=> array('style'			=>  'importstyleid'),
				'8' 	=> array('thread'			=>  'importthreadid'),
				'9'		=> array('post'				=>  'importthreadid'),
				'10'	=> array('thread'			=>  'importforumid'),
				'11' 	=> array('smilie'			=>  'importsmilieid'),
				'12' 	=> array('pmtext'			=>  'importpmid'),
				'13' 	=> array('avatar'			=>  'importavatarid'),
				'14' 	=> array('customavatar'		=>  'importcustomavatarid'),
				'15' 	=> array('customprofilepic'	=>  'importcustomprofilepicid'),
				'16' 	=> array('post'				=>  'importpostid'),
				'17' 	=> array('attachment'		=>  'importattachmentid')
				);


		// Do them now
		foreach ($fields as $id => $table_array)
		{
			foreach ($table_array as $tablename => $column)
			{
				if ($this->add_import_id($Db_target, $target_db_type, $target_table_prefix, $tablename, $column))
				{
					$displayobject->display_now("\n<br /><b>$tablename</b> - $column <i>OK</i>");
				}
				else
				{
					$sessionobject->add_error('fatal',
								$this->_modulestring,
								get_class($this) . "::resume failed trying to modify table $tablename to add $column",
								'Check database permissions');
				}
			}
		}


		// Add the importpostid for the attachment imports and the users for good measure
		$this->add_index($Db_target, $target_db_type, $target_table_prefix, 'post');
		$this->add_index($Db_target, $target_db_type, $target_table_prefix, 'user');


		// Check the database connection
		$result = $this->check_database($Db_source, $source_db_type, $source_table_prefix, $sessionobject->get_session_var('sourceexists'));
		$displayobject->display_now($result['text']);

		if ($result['code'])
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num,'_time_taken'),
				$sessionobject->return_stats($class_num,'_objects_done'),
				$sessionobject->return_stats($class_num,'_objects_failed')
			));

			$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
			$sessionobject->set_session_var(substr(get_class($this), -3), 'FINISHED');
			$sessionobject->set_session_var('module','000');
			$displayobject->update_basic('displaymodules','FALSE');
			$displayobject->update_html($displayobject->print_redirect_001('index.php',$sessionobject->get_session_var('pagespeed')));
		}
		else
		{
			$sessionobject->add_session_var($class_num . '_objects_failed',intval($sessionobject->get_session_var($class_num . '_objects_failed')) + 1 );
			$displayobject->update_html($displayobject->make_description("{$displayobject->phrases['failed']} {$displayobject->phrases['check_db_permissions']}"));
			$sessionobject->set_session_var('001','FAILED');
			$sessionobject->set_session_var('module','000');
			$displayobject->update_html($displayobject->print_redirect_001('index.php',$sessionobject->get_session_var('pagespeed')));
		}
	}
}// End class
# Autogenerated on : February 13, 2006, 3:37 pm
# By ImpEx-generator 2.1.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 001.php,v $ - $Revision: 1.6 $
|| ####################################################################
\*======================================================================*/
?>