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
* Ubb Import Private Messages
*
* The ubb users are stored in files with each lines corresponding to a
* diffrent values.
*
* @package 		ImpEx.ubb_classic
* @version		$Revision: 1.17 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/04/03 10:41:37 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
class ubb_classic_010 extends ubb_classic_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '003';
	var $_modulestring 	= 'Import Private Messages';

	function ubb_classic_010()
	{
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$proceed = $this->check_order($sessionobject, $this->_dependent);
		if ($proceed)
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source, 'clear_imported_private_messages'))
				{
					$this->_restart = false;
					$displayobject->display_now("<h4>Imported PM's have been cleared</h4>");
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . "::restart failed , clear_imported_private_messages",
											 'Check database permissions and user table');
				}
			}

			$displayobject->update_basic('title','Import Private Messages');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('privatemessages','working'));
			$displayobject->update_html($displayobject->make_table_header("Step 11: Import Private Messages"));
			$displayobject->update_html($displayobject->make_description("<p>We will now import all private messages from your UBB</p>"));
			$displayobject->update_html($displayobject->make_yesno_code("Would you like the page to automaticall submit till all Private Messages are done ?","autosubmit",1));
			$displayobject->update_html($displayobject->make_input_code("Messages to process per cycle (must be greater than 1)","pmperpage",50));
			$displayobject->update_html($displayobject->do_form_footer("Import PM's"));

			$sessionobject->add_session_var('pmstartat','0');
		}
		else
		{
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description('<p>This module is dependent on <i><b>' . $sessionobject->get_module_title($this->_dependent) . '</b></i> cannot run until that is complete.'));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));
			$sessionobject->set_session_var(substr(get_class($this) , -3),'FALSE');
			$sessionobject->set_session_var('module','000');
		}
	}

	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($sessionobject->get_session_var('privatemessages') == 'working')
		{
			// Set up working variables.
			$displayobject->update_basic('displaymodules','FALSE');
			$target_database_type	= $sessionobject->get_session_var('targetdatabasetype');
			$target_table_prefix	= $sessionobject->get_session_var('targettableprefix');
			$class_num				= substr(get_class($this) , -3);



			if(!$sessionobject->get_session_var($class_num . '_start'))
			{
				$sessionobject->timing($class_num,'start' ,$sessionobject->get_session_var('autosubmit'));
			}

			$ubbmemberspath = $sessionobject->get_session_var('ubbmemberspath');

			$pmusers = $this->get_pm_users($ubbmemberspath);


			$counter=0;

			$passdone = false;
			$finsihed = false;

			$vbuserid = $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix);
			$username = $this->get_username($Db_target, $target_database_type, $target_table_prefix);


			$pmtext_object 		= new ImpExData($Db_target, $sessionobject, 'pmtext');
			$pmreceipt_object 	= new ImpExData($Db_target, $sessionobject, 'pmreceipt');
			$pm_object 			= new ImpExData($Db_target, $sessionobject, 'pm');

			foreach ($pmusers as $filename)
			{
				if ($username[intval(substr($filename,0,strpos($filename,".")))])
				{
					$listfile = file("$ubbmemberspath/pm_users/$filename");

					while (list($uline,$ucontents)=each($listfile))
					{
						if (preg_match('#(q?q)?([^a-z])([A-Z])-([0-9]*)\\2\s*=>\s*(q?q)?([^a-z])([.0-9]*)\\6#U', $ucontents, $resultsarray))
						{
							$counter++;
							if ($counter >= $sessionobject->get_session_var('pmstartat'))
							{
								$imported = false;

								$done++;

								$_pmtext = $pmtext_object;
								$_pmreceipt = $pmreceipt_object;


								$filename = "$ubbmemberspath/pm_topics/$resultsarray[3]/$resultsarray[4].cgi";

								if (file_exists($filename))
								{

									$messagefile = file($filename);
									$lines = count($messagefile);
									if ($lines == 0)
									{
										return FALSE;
									}
									else
									{
										if (strstr($messagefile[1], "! => q!") OR strstr($messagefile[1], "' => '") OR strstr($messagefile[1], "subject => q|") OR  strstr($messagefile[1], "' => ") OR strstr($messagefile[1], "q!subject! => q~") OR strstr($messagefile[1], "q~close~ => q~") OR strstr($messagefile[1], "read => q|"))
										{
											$data = implode('', file($filename));

											if (phpversion() < '4.0.5')
											{
												$data = str_replace("'", "\\'", str_replace('\\', '\\\\', $data));
											}

											$data = preg_replace("/q?([^a-z0-9]?)([a-z0-9_]+)\\1\s+=>\s+q?([^a-z0-9])([^\n]*)\\3(,\s+?)/siUe","\$this->convert_ubb6_pm_to_pm('\\2', '\\4')", $data);
											$data = preg_replace("/%([a-z0-9_]+) = \(/siU", '$\1 = array(', $data);
											$data = preg_replace("#q!([0-9]+)! => undef#iU", "'\\1' => NULL", $data);


											//Strange cases
											$data = str_replace("q!close! => undef", "'close' => 'undef'", $data);
											$data = str_replace("q~close~ => q~~","'close' => ''", $data);


											$data = str_replace("', );", "');", $data);
											$data = str_replace("1;", "", $data);
											$data = str_replace("\\\'", "\'", $data);
											eval($data);

											$arraycount = count($pm_date);
											$i=0;

											$_pmtext->set_value('mandatory', 'importpmid',		'0');
											$_pmtext->set_value('mandatory', 'fromuserid', $vbuserid["$pm_from[$i]"]);
											$_pmtext->set_value('mandatory', 'title', $pm_topic_data['subject']);
											$_pmtext->set_value('mandatory', 'message', $this->clean_pm_text($this->html_2_bb($pm_post[$i])));
											$_pmtext->set_value('mandatory', 'touserarray', $vbuserid[intval($pm_to[$i])]);

											$_pmtext->set_value('nonmandatory', 'fromusername', $this->get_one_username($Db_target, $Db_object, $target_database_type, $target_table_prefix, $pm_from[$i]));
											$_pmtext->set_value('nonmandatory', 'iconid', $pm_icon[$i]);
											$_pmtext->set_value('nonmandatory', 'dateline',$this->ubbdate2unix($pm_date[$i],$pm_time[$i]));
											$_pmtext->set_value('nonmandatory', 'showsignature', $value);
											$_pmtext->set_value('nonmandatory', 'allowsmilie', $value);

											if ($_pmtext->is_valid())
											{
												$sent_to = array();

												$pmimporttextid = $_pmtext->import_pm_text($Db_target, $target_database_type, $target_table_prefix);
												for ($i=0; $i<$arraycount; ++$i)
												{
													$temppm = $pm_object;

													$pm_sent_to_user_id = $this->get_vb_userid($Db_target, $target_database_type, $target_table_prefix, $pm_to[$i]);

													if(!in_array($pm_sent_to_user_id,$sent_to))
													{
														array_push($sent_to,$pm_sent_to_user_id);

														$temppm->set_value('mandatory', 'userid', $pm_sent_to_user_id);
														$temppm->set_value('mandatory', 'pmtextid', $pmimporttextid);
														$temppm->set_value('mandatory', 'importpmid', $pmimporttextid);
														$temppm->set_value('nonmandatory', 'folderid', '0');
														$temppm->set_value('nonmandatory', 'messageread', '0');
														
														if (!$temppm->is_valid())
														{
															continue;
														}

														if ($temppm->import_pm($Db_target, $target_database_type, $target_table_prefix))
														{
															// The import is done with in the get_pm_details due to the way ubb stores the lists of PM's
															$displayobject->display_now("<br /><span class=\"isucc\"><b>" . $temppm->how_complete() . "%</b></span> ::  $resultsarray[4] " . $temppm->get_value('mandatory','username'));
															$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );

														}
														else
														{
															// Couldn't get it
															$sessionobject->add_error('notice',
																 $this->_modulestring,
																 get_class($this) . "::get_thread_details failed for " . $contents . "||" . $forum . "||" . $regs[1] . ". with ubb/thread/" . get_class($try),
																 'None, will try next object, check getdetails errors.');
																 $displayobject->display_now("<br />Failed with current object, trying next. Failed on ;  " .$try->_failedon);
														}
														unset($temppm);
													}
													else
													{
														//Its a duplicate

													}
												}
												unset($sent_to);
												//return $pmimporttextid;
											}
											else
											{
												# TODO: Error handeling here error on PM text import
												echo "<br /><b>IS NOT</b> Valid pmtext object" . $_pmtext->_failedon . "::" . $_pmtext->get_value('mandatory', 'fromuserid') . "::" .  $pm_from[$i];
												exit();
											}
										}
									}
								}
							}
							unset($_pmtext);
							unset($_pmreceipt);
						}
						if ($done == intval($sessionobject->get_session_var('pmperpage')))
						{
							$passdone = true;
							break;
						}
					}
					if ($passdone)
					{
						break;
					}
				}
			}
		}

		if ($done==0)
		{
			if ($this->update_user_pm_count($Db_target, $target_database_type, $target_table_prefix))
			{
				$displayobject->display_now($displayobject->phrases['completed']);
			}
			else
			{
				$displayobject->display_now($displayobject->phrases['failed']);
			}
			
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num,'_time_taken'),
				$sessionobject->return_stats($class_num,'_objects_done'),
				$sessionobject->return_stats($class_num,'_objects_failed')
			));

			$sessionobject->set_session_var('privatemessages','done');
			$sessionobject->set_session_var('pmstartat','0');
			$sessionobject->set_session_var('autosubmit','0');
			$sessionobject->set_session_var($class_num,'FINISHED');
			$sessionobject->set_session_var('module','000');

			$displayobject->update_html($displayobject->print_redirect('index.php','2'));
		}
		else
		{
			$sessionobject->add_session_var('pmstartat',($sessionobject->get_session_var('pmstartat') + $counter));
			$displayobject->update_html($displayobject->print_redirect('index.php'));
		}
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 010.php,v $ - $Revision: 1.17 $
|| ####################################################################
\*======================================================================*/
?>
