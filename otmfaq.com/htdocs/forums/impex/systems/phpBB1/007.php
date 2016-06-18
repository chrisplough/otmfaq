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
* phpBB1_007 Import Smilie module
*
* @package			ImpEx.phpBB1
* @version			$Revision: 1.5 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/08/21 21:13:10 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class phpBB1_007 extends phpBB1_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '006';
	var $_modulestring 	= 'Import Smilie';


	function phpBB1_007()
	{
		// Constructor
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_smilies'))
				{
					$displayobject->display_now('<h4>Imported smilies have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_smilies','Check database permissions');
				}
			}


			// Start up the table
			$displayobject->update_basic('title','Import Smilie');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_smilie','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code('Smilies to import per cycle (must be greater than 1)','smilieperpage',50));
			$displayobject->update_html($displayobject->make_yesno_code("Would you like the phpBB smilies to over write the vB ones if there is a duplication ?","over_write_smilies",1));

			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('smiliestartat','0');
			$sessionobject->add_session_var('smiliedone','0');
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
		$smilie_start_at			= $sessionobject->get_session_var('smiliestartat');
		$smilie_per_page			= $sessionobject->get_session_var('smilieperpage');
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}

		// If the image category dosn't exsist for the imported smilies, create it
		$imported_smilie_group = new ImpExData($Db_target, $sessionobject, 'imagecategory');

		$imported_smilie_group->set_value('nonmandatory', 'title',		'Imported Smilies');
		$imported_smilie_group->set_value('nonmandatory', 'imagetype',		'3');
		$imported_smilie_group->set_value('nonmandatory', 'displayorder',	'1');


		$smilie_group_id = $imported_smilie_group->import_smilie_image_group($Db_target, $target_database_type, $target_table_prefix);

		// Get an array of smilie details
		$smilie_array 	= $this->get_phpBB1_smilie_details($Db_source, $source_database_type, $source_table_prefix, $smilie_start_at, $smilie_per_page);
		
		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($smilie_array) . ' smilies</h4><p><b>From</b> : ' . $smilie_start_at . ' ::  <b>To</b> : ' . ($smilie_start_at + count($smilie_array)) . '</p>');

		$smilie_object = new ImpExData($Db_target, $sessionobject, 'smilie');

		foreach ($smilie_array as $smilie_id => $smilie)
		{
			$try = (phpversion() < '5' ? $smilie_object : clone($smilie_object));
			$import_smilie = false;
			
			// Set the correct key names to pass.
			$pass_array = array(
				'title'	=> $smilie['emotion'],
				'smilietext' => $smilie['code'],
				'smiliepath' => $smilie['smile_url']
			);

			// Check the lenght of it
			if(strlen($pass_array['smilietext']) > 20)
			{
				$truncation = substr($pass_array['smilietext'],0,19) . ':';

				$displayobject->display_now("<br /><font color=\"red\"><b>Too long</font></b> '  " . $pass_array['smilietext']  . "'"  .
						"<br /><font color=\"red\"><b>Truncating to</font></b> '" . $truncation . "'");

				$pass_array['smilietext'] = $truncation;
			}

			$pass_array['smilietext'] = addslashes($pass_array['smilietext']);

			// Is it a duplicate ?

			$it_is_a_duplicate = $this->does_smilie_exists($Db_target, $target_database_type, $target_table_prefix, addslashes($pass_array['smilietext']));

			if ($it_is_a_duplicate)				// Its there
			{
				if ($over_write_smilies)		// And want to over write
				{
					$import_smilie = true;
				}
			}
			else								// Its not there so it dosn't matter
			{
				$import_smilie = true;
			}

			$try->set_value('mandatory', 	'smilietext', 		$pass_array['smilietext']);
			$try->set_value('nonmandatory', 'title',		$pass_array['title']);
			$try->set_value('nonmandatory', 'smiliepath', 		$pass_array['smiliepath']);
			$try->set_value('nonmandatory', 'imagecategoryid', 	$smilie_group_id);
			$try->set_value('nonmandatory', 'displayorder', 	'1');
			$try->set_value('mandatory', 	'importsmilieid',	$smilie_id);

			if($try->is_valid())
			{
				if($try->import_smilie($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: smilie -> ' . $pass_array['smilietext']);
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
					$displayobject->display_now("<br />Found smilie and <b>DID NOT</b> imported to the  {$target_database_type} database" . $try->_failedon);
				}
			}
			else
			{
				$displayobject->display_now("<br />Invalid smilie object, skipping." . $try->_failedon);
			}
			unset($try,$pass_array,$import_smilie);
		}// End foreach


		// Check for page end
		if (count($smilie_array) == 0 OR count($smilie_array) < $smilie_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');




			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
										$sessionobject->return_stats($class_num, '_time_taken'),
										$sessionobject->return_stats($class_num, '_objects_done'),
										$sessionobject->return_stats($class_num, '_objects_failed')
										));


			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_smilie','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php','1'));
		}


		$sessionobject->set_session_var('smiliestartat',$smilie_start_at+$smilie_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php'));
	}// End resume
}//End Class
# Autogenerated on : December 13, 2004, 9:50 pm
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 007.php,v $ - $Revision: 1.5 $
|| ####################################################################
\*======================================================================*/
?>
