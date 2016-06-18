<?php if (!defined('IDIR')) { die; }
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF98A5CB5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is ©2000-2006 Jelsoft Enterprises Ltd. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/**
* Ubb Import Posts
*
* The ubb users are stored in files with each lines corresponding to a
* diffrent values.
*
* @package 		ImpEx.ubb_classic
* @version		$Revision: 1.20 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/04/03 10:41:37 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
class ubb_classic_007 extends ubb_classic_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '006';
	var $_modulestring 	= 'Import Posts';

	function ubb_classic_007()
	{
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$proceed = $this->check_order($sessionobject,$this->_dependent);
		if ($proceed)
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source, 'clear_imported_posts'))
				{
					$this->_restart = FALSE;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . "::restart failed , clear_imported_posts",
											 'Check database permissions and user table');
				}
			}
			$displayobject->update_basic('title','Import posts');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this), -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this), -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('posts','working'));
			$displayobject->update_html($displayobject->make_table_header('Step 8: Import Posts'));
			$displayobject->update_html($displayobject->make_description("<p>The importer will now start to import posts from your UBB.Classic board. Depending on the size of your board, this may take some time.</p>"));
			$displayobject->update_html($displayobject->make_yesno_code("Would you like the page to continute till all posts are done ? Can help clicking if there are a lot","autosubmit",1));
			$displayobject->update_html($displayobject->make_input_code("Number of threads to import per cycle","threadsperpage","10"));
			$displayobject->update_html($displayobject->do_form_footer("Import Posts"));

			$sessionobject->add_session_var('finishedcurrectforum','true');
			$sessionobject->add_session_var('currectforum','0');
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
		if ($sessionobject->get_session_var('posts') == 'working')
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

			// Setup some working vars
			$threads_start_at 	= $sessionobject->get_session_var('threadstartat');
			$threads_per_page 	= $sessionobject->get_session_var('threadsperpage');
			$threads_to_do		= $threads_start_at	+ $threads_per_page;


			if ($sessionobject->get_session_var('finishedcurrectforum')=='true')
			{
				if ($sessionobject->get_session_var('skipped') == 'TRUE')
				{
					$currentforum = intval($sessionobject->get_session_var('currectforum'));
					$sessionobject->add_session_var('skipped','FALSE');
				}
				else
				{
					$currentforum = intval($sessionobject->get_session_var('currectforum'))+1;
				}

				$forumcount = $Db_target->query_first("select importforumid from " . $sessionobject->get_session_var('targettableprefix') ."forum where importforumid=$currentforum");

				if ($forumcount)
				{
					$sessionobject->set_session_var('currectforum',$currentforum);
					$sessionobject->set_session_var('finishedcurrectforum','false');
					$sessionobject->set_session_var('threadstartat','1');
					$sessionobject->set_session_var('threadsfilepath',"");
				}
				else
				{
					$higestforum = $Db_target->query_first("select importforumid from " . $sessionobject->get_session_var('targettableprefix') ."forum order by importforumid desc limit 1;");

					if ($currentforum >= $higestforum[0] )
					{
						$finished = TRUE;
					}
					elseif ($currentforum < $higestforum)
					{
						$displayobject->display_now("Skipping forum $currentforum as it isn't there.");
						$currentforum++;
						$sessionobject->set_session_var('currectforum',$currentforum);
						$sessionobject->add_session_var('skipped','TRUE');
					}
				}
			}

			if ($sessionobject->get_session_var('finishedcurrectforum')=='false')
			{
				$forum = $sessionobject->get_session_var('currectforum');				$sessionobject->add_error('fatal',
										 $this->_modulestring,
										 get_class($this) . "::resume failed trying to modify table forum to add importforumid",
										 'Check database permissions and forum table');
				$displayobject->display_now("<h3>$forumtitle</h3>");

				if ($sessionobject->get_session_var('threadstartat')=='')
				{
					$sessionobject->add_session_var('threadstartat','1');
				}

				if ($sessionobject->get_session_var('threadsfilepath')=="")
				{
					// get path to threads file if not already found
					$displayobject->display_now("<p>Locating <i>forum_$forum.threads</i> file ....\n");


					$handle=opendir($sessionobject->get_session_var('ubbpath') . "/Forum$forum");

					while ($file = readdir($handle))
					{
						if (strstr($file, "private-"))
						{
							$privatedir = $file;
						}
					}
					closedir($handle);

					if ($privatedir!="")
					{

						if (file_exists($sessionobject->get_session_var('ubbpath') . "/Forum$forum/$privatedir/forum_$forum.threads"))
						{
							$sessionobject->add_session_var('threadsfilepath', $sessionobject->get_session_var('ubbpath') . "/Forum$forum/$privatedir");
						}
						elseif (file_exists($sessionobject->get_session_var('ubbpath') . "/Forum$forum/forum_$forum.threads"))
						{
							$sessionobject->add_session_var('threadsfilepath', $sessionobject->get_session_var('ubbpath') . "/Forum$forum");
						}
						else
						{
							$sessionobject->add_error('fatal',
										 $this->_modulestring,
										 get_class($this) . " Cannot find threads file path",
										 'That the threads file is in the correct path');

							$displayobject->display_now("<b>not found</b>. No threads to import.</p>\n");
						}
					}
					else
					{
						if (file_exists($sessionobject->get_session_var('ubbpath') . "/Forum$forum/forum_$forum.threads"))
						{
							$sessionobject->add_session_var('threadsfilepath', $sessionobject->get_session_var('ubbpath') . "/Forum$forum");
						}
						else
						{
							$sessionobject->add_error('fatal',
										 $this->_modulestring,
										 get_class($this) . " Cannot find threads file path",
										 'That the threads file is in the correct path');

							$displayobject->display_now("<b>not found</b>. No threads to import.</p>\n");

						}
					}

					if ($sessionobject->get_session_var('threadsfilepath')=="")
					{
						$sessionobject->add_error('fatal',
									 $this->_modulestring,
									 get_class($this) . " Cannot find threads file path",
									 'That the threads file is in the correct path');

						$displayobject->display_now("<b>not found</b>. No threads to import.</p>\n");
					}
					else
					{
						$displayobject->display_now("... <b>Found</b><br /><br />");
					}
				}

				$threadslist = file($sessionobject->get_session_var('threadsfilepath') . "/forum_$forum.threads");
				$numlines = sizeof($threadslist)-2;

				$vbuserid 		= $this->get_user_ids($Db_target, $target_database_type, $target_table_prefix,true);
				$vbforumid 		= $this->get_forum_ids($Db_target, $target_database_type, $target_table_prefix);
				$vb_thread_ids 	= $this->get_ubb_threads_ids($Db_target, $target_database_type, $target_table_prefix);
				$name_to_ids 	= $this->get_username_to_ids($Db_target, $target_database_type, $target_table_prefix);

				$counter=0;
				$displayobject->display_now("\n<h4>Importing forum_$forum.threads</h4>");

				$post_object = new ImpExData($Db_target, $sessionobject, 'post');

				for ($i = intval($sessionobject->get_session_var('threadstartat')); $i < (intval($sessionobject->get_session_var('threadstartat'))+intval($sessionobject->get_session_var('threadsperpage'))); $i++)
				{
					$finished = FALSE;
					$counter++;
					if (preg_match("/q!([0-9]*)!/", $threadslist[$i], $regs) or preg_match("/\"([0-9]*)\"/",$threadslist[$i],$regs) or preg_match("/q~([0-9]*)~/", $threadslist[$i], $regs) or preg_match("/'([0-9]*)'/", $threadslist[$i], $regs))
					{
						if (is_file($sessionobject->get_session_var('threadsfilepath') . "/$regs[1].cgi"))
						{


							$threadfile = file($sessionobject->get_session_var('threadsfilepath') . "/$regs[1].cgi");


							while (list($line,$contents)=each($threadfile))
							{
								if ($line==0)
								{
									// Its a thread
									// Though the posts need the thread id
									// First post id
								}
								else
								{
									$try = (phpversion() < '5' ? $post_object : clone($post_object));
									$vBthreadid = '0';
									$userid = '0';
									$custom_smilies = '';
									if(is_array($this->_ubb_smilies))
									{
										$this->_smilies = array_merge($this->_smilies, $this->_ubb_smilies);
									}

									if(is_dir($sessionobject->get_session_var('ubbgraemlinspath')))
									{
										$custom_smilies = $this->get_custom_smilie($sessionobject->get_session_var('ubbgraemlinspath'));
									}

									$postbits = explode("||", trim($contents));

									if (array_key_exists(intval($postbits[11]),$vbuserid))
									{
										$userid = $vbuserid[intval($postbits[11])];
									}
									else
									{
										if($name_to_ids[$postbits[2]])
										{
											$userid = $name_to_ids[$postbits[2]];
										}
										else
										{
											$errortext = intval($postbits[11]) . " user not found";
											// QUESTION: What is the best way of dealing with this
											// TODO: Complete add_error correctly
											//$sessionobject->add_error('008',$errortext,'moo','moo');
											// DEBUG: have to put some more checking in here to make sure that the user file dosn't actually exsist
											echo'<br />' . intval($postbits[11]) . ' <span class="ifail">User id being set to 0 because origional user not imported</span>';
											$userid = '0';
										}
									}

									// Is it in the array, if not bash the database, though its redundant as
									// that where the array came from.
									// TODO: Need a fail safe here.
									if($vb_thread_ids[$forum][intval($regs[1])])
									{
										$try->set_value('mandatory', 'threadid',			$vb_thread_ids[$forum][intval($regs[1])]);
									}
									else
									{
										$try->set_value('mandatory', 'threadid',			$this->get_forum_thread_id($Db_target, $target_database_type, $target_table_prefix, intval($regs[1]), $forum));
									}

									$try->set_value('mandatory', 'userid', 				$userid);
									$try->set_value('mandatory', 'importthreadid', 		intval($regs[1]));
									$try->set_value('nonmandatory', 'username', 		$postbits[2]);

									$date = explode('-',$postbits[3]);
									$time = explode(' ',$postbits[4]);
									$hrmn = explode(':',$time[0]);

									if ($hrmn[0] == '12' and $time[1] == "AM")
									{
										$hrmn[0] = 0;
									}
									elseif ($time[1]=="PM" && $hrmn[0] != '12')
									{
										$hrmn[0] = intval($hrmn[0]) + 12;
									}

									$try->set_value('nonmandatory', 'dateline', 		mktime ($hrmn[0],$hrmn[1],0,$date[0],$date[1],$date[2]));
									$try->set_value('nonmandatory', 'pagetext', 		$this->local_html_2bb($this->html_2_bb($postbits[6]),1,1));
									$try->set_value('nonmandatory', 'showsignature', 	$try->option2bin($postbits[12]));
									$try->set_value('nonmandatory', 'ipaddress', 		$postbits[7]);
									$try->set_value('nonmandatory', 'iconid', 			intval($postbits[9]));
									$try->set_value('nonmandatory', 'visible', 			'1');
									$try->set_value('nonmandatory', 'allowsmilie', 		'1');
									$try->set_value('nonmandatory', 'importpostid',		'1');
									//$try->set_value('nonmandatory', 'parentid', );
									//$try->set_value('nonmandatory', 'title', );
									//$try->set_value('nonmandatory', 'attach', );



									if($try->is_valid())
									{
										if($try->import_post($Db_target, $target_database_type, $target_table_prefix))
										{



											$displayobject->display_now("<br /><span class=\"isucc\"><b>" . $try->how_complete() . "%</b></span> :: Post from -> " . $try->get_value('nonmandatory','username'));
											$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
											$imported = true;
										}
										else
										{
											$sessionobject->add_error('warning',
																	 $this->_modulestring,
																	 get_class($this) . "::import_thread failed for " . $cat['cat_title'] . " get_phpbb2_categories_details was ok.",
																	 'Check database permissions and user table');
											$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
											$displayobject->display_now("<br />Got thread " . $cat['cat_title'] . " and <b>DID NOT</b> imported to the " . $target_database_type . " database");
										}
									}
									else
									{
										$displayobject->display_now("<br />Invalid post object, skipping.'" . $try->_failedon . "'");
									}
									unset($try);
								}
							}
						}
						else
						{
							$sessionobject->add_error('warning',
													  $this->_modulestring,
													  get_class($this) . ":: failed, no posts/thread file",
													  'Check consistance of threads file against posts in ubb.');
							$displayobject->display_now("<br /><b>No file :</b> " . $sessionobject->get_session_var('threadsfilepath') . "/$regs[1].cgi");
						}
					}// prereg
				}// counter
			}//finishedcurrectforum=='false'

			if (intval($sessionobject->get_session_var('threadstartat'))+$counter > $numlines)
			{
				$displayobject->display_now("<h4>Done this forum</h4>");
				$sessionobject->set_session_var('finishedcurrectforum','true');
			}

			if ($finished)
			{
				$displayobject->display_now('Updateing parent ids to allow for a threaded view....');

				if ($this->update_post_parent_ids($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('Done !');
				}
				else
				{
					$displayobject->display_now('Error updating parent ids');
				}


				$sessionobject->end_timing(substr(get_class($this), -3));
				$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
				$sessionobject->remove_session_var($class_num . '_start');

				$displayobject->update_html($displayobject->module_finished($this->_modulestring,
					$sessionobject->return_stats($class_num,'_time_taken'),
					$sessionobject->return_stats($class_num,'_objects_done'),
					$sessionobject->return_stats($class_num,'_objects_failed')
				));

				$displayobject->display_now('User import done.');
				$displayobject->update_basic('displaymodules','FALSE');


				$sessionobject->set_session_var('posts','done');
				$sessionobject->set_session_var('threadstartat','0');
				$sessionobject->set_session_var('autosubmit','0');
				$sessionobject->set_session_var('007','FINISHED');
				$sessionobject->set_session_var('module','000');
				$displayobject->update_html($displayobject->print_redirect('index.php','2'));
			}
			else
			{
				$sessionobject->add_session_var('threadstartat',($sessionobject->get_session_var('threadstartat') + $counter));
				$displayobject->update_html($displayobject->print_redirect('index.php'));
			}
		}
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 007.php,v $ - $Revision: 1.20 $
|| ####################################################################
\*======================================================================*/
?>

