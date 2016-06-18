<?php
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

error_reporting(E_ALL & ~E_NOTICE);

if (!defined('IDIR')) { die; }

#####################################
# ImpEx text, not really phrases
#####################################

# Phrased & Error logging
# phpBB2
# ipb2
# eve
# photopost-vBulletin user table
# vbzoom
# discuz2
# phpwind
# ipb1.3
# smf
# ubb_threads
# snitz
# vb2
# vb3

#####################################
# index.php phrases
#####################################

$impex_phrases['enter_customer_number'] = 'Enter Customer number';

$impex_phrases['using_local_config']	= 'Using includes/config.php for target config.';
$impex_phrases['using_impex_config']	= 'Using ImpExConfig.php for target config.';
$impex_phrases['no_mssql_support']		= 'You do not have MSSQL support in this version/compile of php, the importer will not be able to connect to the source database.';
$impex_phrases['no_mssql_support_link']	= '</br>Read this : <a target="_blank" href="http://www.php.net/manual/en/ref.mssql.php">PHP MSSQL</a>';
$impex_phrases['no_source_set']			= 'A source database was not entered in ImpExConfig.php';
$impex_phrases['source_not_exsist']		= 'The source database entered in ImpExConfig.php does not exist.';
$impex_phrases['sourceexists_is_false'] = 'You have set sourceexists to FALSE in ImpExConfig.php, this means you do not want to import from a database system, this system is a source database system.'; 
$impex_phrases['failed_connection']		= 'Connection to source server failed. Check username and password.';
$impex_phrases['db_cleanup']			= 'Database cleanup &amp; restart';
$impex_phrases['online_manual']			= 'Online manual';
$impex_phrases['cleanup_module_title']	= 'Clean up module';
$impex_phrases['feedback_module_title']	= 'Feedback';
$impex_phrases['build_version']			= 'ImpEx build version : ';
$impex_phrases['remove']				= 'Remove ImpEx once import is complete and final';
$impex_phrases['finished_import']		= 'Once you have finished these modules follow these instructions to complete the import';

#####################################
# help.php phrases
#####################################

$impex_phrases['action_1']				= '<p>These links will allow you to restart an import, remove a session or remove the importid\'s to allow consecutive imports.</p>';
$impex_phrases['action_2']				= '<br><h4>Cancel</h4><p><a href="index.php">To cancel and return to the import, click here.</a></p>';
$impex_phrases['action_3']				= '<br><h4>Delete Session</h4><p><a href="help.php?action=delsess">To delete the import session and continue with the import, click here.</a></p>';
$impex_phrases['action_4']				= '<br><h4>Delete Session and all <i>imported</i> data</h4><p><a href="help.php?action=delall">To delete the import session and all imported data for a clean retry, click here.</a></p>';
$impex_phrases['action_5']				= '<br><h4>Remove importids</h4><p><a href="help.php?action=delids">To delete the importids in the database, click here, also removes the session. This will allow you to do consecutive imports</a></p>';
$impex_phrases['action_6']				= '<br><h4>Remove duplicate forums/threads/posts.</h4><p><a href="help.php?action=deldupe">This will remove anything that has a duplicate importid and a diffrent vBulletin id. Use with EXTREME caution, results will vary between source systems, <b>ALWAYS TAKE A BACK UP BEFORE USING</b></a></p>';

$impex_phrases['dell_session_1']		= '<p><b>Deleteing Impex Session.</b></p>';
$impex_phrases['dell_session_2']		= '<p>After this is complete any previously imported data will be left in the data base so it is advised that you re run any modules that you already have twice to ensure that data is cleaned up.</p>';
$impex_phrases['dell_session_3']		= '<p>For instance, if the import timed out on the users import and you have come here to delete the session, once that is done and you restart the import run the import users module <b>twice</b>, that will ensure that on the start up of the second pass that <b>all</b> of the previously imported data will be removed.</p>';
$impex_phrases['dell_session_4']		= '<p>This happens with all modules, running them more that once will clear out and data for that module that has an import id</p>';
$impex_phrases['dell_session_5']		= '<p><b>Session deleted</b></p>';
$impex_phrases['dell_session_6']		= '<p><a href="index.php">Click here to return to the import page.</a></p>';

$impex_phrases['deleting_session']		= '<p><b>Deleteing Impex Session.</b></p>';
$impex_phrases['session_deleted']		= '<p><b>Session deleted</b></p>';
$impex_phrases['deleting_duplicates']	= '<p><b>Deleting duplicates</b></p>';

$impex_phrases['deleting_from']			= '<p>Deleting imported data from ';

$impex_phrases['click_to_return']		= '<p><a href="index.php">Click here to return to the import page.</a></p>';

$impex_phrases['del_ids_1']				= 'Setting';
$impex_phrases['del_ids_2']				= 'in table';
$impex_phrases['del_ids_3']				= 'to 0....';

$impex_phrases['cant_read_config']		= 'ImpEx can not read the target database details from impex/ImpExConfig.php OR ../includes/config.php.<br> Please enter the target database details in ImpExConfig, or run ImpEx installed opposed to standalone.';
#####################################
# ImpExDisplay.php phrases
#####################################

$impex_phrases['title']					= 'Import / Export';
$impex_phrases['redo']					= 'Redo Module';
$impex_phrases['start_module']			= 'Start Module';
$impex_phrases['minute_title']			= ' min(s)'; # Note space
$impex_phrases['seconds_title']			= ' sec(s)'; # Note space
$impex_phrases['totals']				= 'Totals:';

$impex_phrases['select_system']			= 'Select Source Board Format :: ';
$impex_phrases['select_target_system']	= 'Select version to export to :: ';

$impex_phrases['installed_systems']		= 'The installed importers';
$impex_phrases['start_import']			= 'Begin Import';

$impex_phrases['module']				= 'Module';
$impex_phrases['action']				= 'Action';
$impex_phrases['completed']				= 'Completed';

$impex_phrases['second']				= 'Second';
$impex_phrases['seconds']				= 'Seconds';

$impex_phrases['successful']			= 'Successful';
$impex_phrases['failed']				= 'Failed';
$impex_phrases['redirecting']			= 'Redirecting';
$impex_phrases['timetaken']				= 'Time taken';


$impex_phrases['associate']				= 'Associate';
$impex_phrases['quit']					= 'Quit';

#####################################
# Import Common
#####################################

$impex_phrases['continue']				= 'Continue';
$impex_phrases['reset']					= 'Reset';
$impex_phrases['importing']				= 'Importing';
$impex_phrases['import']				= 'Import';
$impex_phrases['imported']				= 'Imported';
$impex_phrases['from']					= 'From';
$impex_phrases['to']					= 'To'; # i.e. Importing 300 posts From 500 To 800
$impex_phrases['dependant_on']			= 'This module is dependent on';
$impex_phrases['cant_run']				= 'cannot run until that is complete';
$impex_phrases['user_id']				= 'User id';
$impex_phrases['updating_parent_id']	= 'Updateing parent ids, please wait';
$impex_phrases['avatar_ok']				= 'Avatar OK';
$impex_phrases['avatar_too_big']		= 'Avatar too big';
$impex_phrases['no_rerun']				= 'You cannot RERUN this module, you need to clear the whole import and session and start again.';
$impex_phrases['no_system']				= 'ImpEx has attempted to start a system it cannot find, this is most likely because the session was saved correctly after you chose the system. Please check database and try again or contact support.';

#####################################
# 001 Setup
#####################################

$impex_phrases['check_update_db']		= 'Check and update database';
$impex_phrases['get_db_info']			= 'Get database information';
$impex_phrases['check_tables']			= 'This module will check the tables in the database as well as the connection.';

$impex_phrases['altering_tables']		= 'Altering tables';
$impex_phrases['alter_desc_1']			= 'ImpEx will now Alter the tables in the vB database to include import id numbers.';
$impex_phrases['alter_desc_2']			= 'This is needed during the import process for maintaining refrences between the tables during an import.';
$impex_phrases['alter_desc_3']			= 'If you have large tables (i.e. lots of posts) this can take some time.';
$impex_phrases['alter_desc_4']			= 'They will also be left after the import if you need to link back to the origional vB userid.';

$impex_phrases['alter_desc_4']			= 'They will also be left after the import if you need to link back to the origional vB userid.';

#####################################
# Associate Users
#####################################

$impex_phrases['associate_users']		= 'Associate Users';

$impex_phrases['assoc_desc_1']			= 'Warning !! Assosiated users will currently be deleted if you run the import user module twice as it removes users with an importuserid. You cannot associate with an existing admin user at this stage.';
$impex_phrases['assoc_desc_2']			= 'If you want to associate a source user (in the left column) with an existing vBulletin user, enter the user id number of the vBulletin user in the box provided, and click the Associate Users button.';
$impex_phrases['assoc_desc_3']			= 'To view the list of existing vBulletin users, together with their userid';

$impex_phrases['assoc_list']			= 'Association list';
$impex_phrases['assoc_match']			= 'Put the exsisting vbulletin user id next to the source user id that you wish to associate them with';

$impex_phrases['no_users']				= 'There are NO more vBulletin users, quit to continue.';
$impex_phrases['assoc_not_matched']		= 'NOT done. It is most likely that vBulletin user';


#####################################
# Import Usergroups
#####################################

$impex_phrases['usergroup']				= 'Usergroup';
$impex_phrases['usergroups']			= 'Usergroups';
$impex_phrases['import_usergroup']		= 'Import usergroup';
$impex_phrases['usergroups_cleared']	= 'Imported usergroup have been cleared';

$impex_phrases['usergroups_per_page']	= 'Usergroups to import per cycle (must be greater than 1)';
$impex_phrases['usergroups_all']		= 'ImpEx will now import all the usergroups and ranks';

#####################################
# Import Users
#####################################

$impex_phrases['user']					= 'User';
$impex_phrases['users']					= 'Users';
$impex_phrases['import_user']			= 'Import user';
$impex_phrases['users_cleared']			= 'Imported users have been cleared';

$impex_phrases['users_per_page']		= 'Users to import per cycle (must be greater than 1)';
$impex_phrases['email_match']			= 'Would you like to associated imported users with existing users if the email address matches ?';
$impex_phrases['avatar_path']			= 'What is the full path to your avatars directory ? (make sure the web server has access to read them).';
$impex_phrases['get_avatars']			= 'Would you like to import the avatars, this can take some time if they are remotely linked';
$impex_phrases['which_email']			= 'Which email would you like to import';
$impex_phrases['which_username']		= 'Which username would you like to import';
$impex_phrases['avatar_size']			= 'Select largest avatar size allowed (setting this will force ImpEx to import them).';

#####################################
# Import Banlists
#####################################

$impex_phrases['banlist']				= 'Banlist';
$impex_phrases['banlists']				= 'Banlists';
$impex_phrases['import_banlist']		= 'Import banlist';
$impex_phrases['banlists_cleared']		= 'Imported banlist have been cleared';

$impex_phrases['useridban']				= 'User id ban list';
$impex_phrases['ipban']					= 'IP ban list';
$impex_phrases['emailban']				= 'Email ban list';

$impex_phrases['banlists_per_page']		= 'Would you like to import the banlist?';
$impex_phrases['banlists_number']		= 'How many lists per page ?';
$impex_phrases['banlists_skip']			= 'You have skipped the Importing of the ban list.';

#####################################
# Import Avatars
#####################################

$impex_phrases['avatar']				= 'Avatar';
$impex_phrases['avatars']				= 'Avatars';
$impex_phrases['import_avatar']			= 'Import avatar';
$impex_phrases['avatars_cleared']		= 'Imported avatars have been cleared';

$impex_phrases['avatar_per_page']		= 'Avatars to import per cycle (must be greater than 1)';

#####################################
# Import Custom pictures
#####################################

$impex_phrases['cus_pic']				= 'Custom picture';
$impex_phrases['cust_pics']				= 'Custom pictures';
$impex_phrases['import_cust_pic']		= 'Import Custom pictures';
$impex_phrases['cust_pic_cleared']		= 'Imported custom pics have been cleared';

$impex_phrases['cust_pics_per_page']	= 'Custom pictures to import per cycle (must be greater than 1)';

#####################################
# Import Ranks
#####################################

$impex_phrases['rank']					= 'Rank';
$impex_phrases['ranks']					= 'Ranks';
$impex_phrases['import_rank']			= 'Import rank';
$impex_phrases['ranks_cleared']			= 'Imported ranks have been cleared';

$impex_phrases['ranks_per_page']		= 'Ranks to import per cycle (must be greater than 1)';

#####################################
# Import Forums
#####################################

$impex_phrases['forum']					= 'Forum';
$impex_phrases['forums']				= 'Forums';
$impex_phrases['category']				= 'Category';
$impex_phrases['categories']			= 'Categorys';
$impex_phrases['import_forum']			= 'Import forum';
$impex_phrases['forums_cleared']		= 'Imported forums have been cleared';

$impex_phrases['forums_per_page']		= 'Forums to import per cycle (must be greater than 1)';

#####################################
# Import Threads
#####################################

$impex_phrases['thread']				= 'Thread';
$impex_phrases['threads']				= 'Threads';
$impex_phrases['import_thread']			= 'Import thread';
$impex_phrases['threads_cleared']		= 'Imported Threads have been cleared';

$impex_phrases['threads_per_page']		= 'Threads to import per cycle (must be greater than 1)';

#####################################
# Import Post
#####################################

$impex_phrases['post']					= 'Post';
$impex_phrases['posts']					= 'Posts';
$impex_phrases['import_post']			= 'Import post';
$impex_phrases['posts_cleared']			= 'Imported Posts have been cleared';

$impex_phrases['posts_per_page']		= 'Posts to import per cycle (must be greater than 1)';

#####################################
# Import Smilies
#####################################

$impex_phrases['smilie']				= 'Smilie';
$impex_phrases['smilies']				= 'Smilies';
$impex_phrases['import_smilie']			= 'Import smilie';
$impex_phrases['smilies_cleared']		= 'Imported Smilies have been cleared';

$impex_phrases['smilies_per_page']		= 'The importer will now start to import smilies from your source board. Please remember to move the smilie images into the vB smilies directory (images/smilies/).';
$impex_phrases['smilie_overwrite']		= 'Would you like the source smilies to over write the vBulletin ones if there is a duplication ?';

$impex_phrases['too_long']				= 'Too long';
$impex_phrases['truncating']			= 'truncating to';
$impex_phrases['duplication']			= 'Duplication';

#####################################
# Import Attachment
#####################################

$impex_phrases['attachment']			= 'Attachment';
$impex_phrases['attachments']			= 'Attachments';
$impex_phrases['import_attachment']		= 'Import attachment';
$impex_phrases['attachments_cleared']	= 'Imported Attachments have been cleared';

$impex_phrases['attachments_per_page']	= 'Attachments to import per cycle (must be greater than 1)';
$impex_phrases['path_to_upload']		= 'Full path to uploads/attachments folder.';
$impex_phrases['source_file_not']		= 'Source file not found';

#####################################
# Import Poll
#####################################

$impex_phrases['poll']					= 'Poll';
$impex_phrases['polls']					= 'Polls';
$impex_phrases['import_poll']			= 'Import poll';
$impex_phrases['polls_cleared']			= 'Imported Polls have been cleared';

$impex_phrases['polls_per_page']		= 'Polls to import per cycle (must be greater than 1)';

#####################################
# Import Moderators
#####################################

$impex_phrases['moderator']				= 'Moderator';
$impex_phrases['moderators']			= 'Moderators';
$impex_phrases['import_moderator']		= 'Import moderator';
$impex_phrases['moderators_cleared']	= 'Imported moderators have been cleared';

$impex_phrases['moderators_per_page']	= 'Moderators to import per cycle (must be greater than 1)';

#####################################
# Import Phrase
#####################################

$impex_phrases['phrase']				= 'Phrase';
$impex_phrases['phrases']				= 'Phrases';
$impex_phrases['import_phrase']			= 'Import phrase';
$impex_phrases['phrases_cleared']		= 'Imported phrases have been cleared';

$impex_phrases['phrases_per_page']		= 'Phrases to import per cycle (must be greater than 1)';

#####################################
# Import Subscription
#####################################

$impex_phrases['subscription']			= 'Subscription';
$impex_phrases['subscriptions']			= 'Subscriptions';
$impex_phrases['import_subscription']	= 'Import subscription';
$impex_phrases['subscriptions_cleared']	= 'Imported subscriptions have been cleared';

$impex_phrases['subscriptions_per_page']= 'Subscriptions to import per cycle (must be greater than 1)';

$impex_phrases['subscriptionlogs']		= 'Subscription logs';

#####################################
# Import Private Message
#####################################

$impex_phrases['pm']					= 'Private message';
$impex_phrases['pms']					= 'Private messages';
$impex_phrases['import_pm']				= 'Import Private messages';
$impex_phrases['pms_cleared']			= 'Imported Import Private have been cleared';

$impex_phrases['pms_per_page']			= 'Private messages to import per cycle (must be greater than 1)';

#####################################
# Import Errors & Remedys
#####################################

$impex_phrases['check_db_permissions']	= 'Check database permissions and connection, or table prefix to ensure its correct.';
$impex_phrases['invalid_object']		= 'Invalid ImpExData object, skipping. Failed on : ';
$impex_phrases['table_alter_fail']		= 'Failed trying to alter a table to add a colum : '; #Note space
$impex_phrases['table_alter_fail_rem']	= 'Ensure that you have ALTER permmission on the target database';

$impex_phrases['usergroup_not_imported']		= "Usergroup not imported";
$impex_phrases['usergroup_not_imported_rem'] = "Check source users profile is as complete as possiable";
$impex_phrases['usergroup_restart_failed']	= 'Restart failed, clear_imported_usergroups';
$impex_phrases['usergroup_restart_ok']	= 'Imported usergroups have been cleared';

$impex_phrases['rank_not_imported']		= "Rank not imported";
$impex_phrases['rank_not_imported_rem'] = "Check source rank is as complete as possiable";
$impex_phrases['rank_restart_failed']	= 'Restart failed, clear_imported_ranks';
$impex_phrases['rank_restart_ok']		= 'Imported ranks have been cleared';

$impex_phrases['user_not_imported']		= "User not imported";
$impex_phrases['user_not_imported_rem']	= "Check source users profile is as complete as possiable";
$impex_phrases['user_restart_failed']	= 'Restart failed, clear_imported_users';
$impex_phrases['user_restart_ok']		= 'Imported users have been cleared';

$impex_phrases['smilie_not_imported']	= "Smilie not imported";
$impex_phrases['smilie_not_imported_rem'] = "Check source smilie details are as complete as possiable";
$impex_phrases['smilie_restart_failed']	= 'Restart failed, clear_imported_smilie';
$impex_phrases['smilie_restart_ok']		= 'Imported smilies have been cleared';

$impex_phrases['post_not_imported']		= 'Post not imported';
$impex_phrases['post_not_imported_rem']	= 'Use the import id to check the source post content and size';
$impex_phrases['post_restart_failed']	= 'Restart failed, clear_imported_posts';
$impex_phrases['post_restart_ok']		= 'Imported posts have been cleared';

$impex_phrases['forum_not_imported']	= 'Forum not imported';
$impex_phrases['forum_not_imported_rem']= 'Use the import id to check the source forum content and size';
$impex_phrases['forum_restart_failed']	= 'Restart failed, clear_imported_forums';
$impex_phrases['forum_restart_ok']		= 'Imported forums have been cleared';

$impex_phrases['thread_not_imported']	= 'Thread not imported';
$impex_phrases['thread_not_imported_rem']= 'Use the import id to check the source thread content and size and forum parent';
$impex_phrases['thread_restart_failed']	= 'Restart failed, clear_imported_threads';
$impex_phrases['thread_restart_ok']		= 'Imported threads have been cleared';

$impex_phrases['moderator_not_imported'] = 'Moderator not imported';
$impex_phrases['moderator_not_imported_rem'] = 'Use the import id to check the source moderator and forum they are linked to';
$impex_phrases['moderator_restart_failed'] = 'Restart failed, clear_imported_moderators';
$impex_phrases['moderator_restart_ok']	= 'Imported moderators have been cleared';

$impex_phrases['poll_not_imported_1']	= 'The poll was imported though not attached to the correct thread.';
$impex_phrases['poll_not_imported_rem']	= 'Use the import id to check the source poll id and thread it matches in the source';
$impex_phrases['poll_not_imported_2']	= 'The poll was not imported.';
$impex_phrases['poll_not_imported_3']	= 'The poll voters were not attached to the correct thread.';

$impex_phrases['poll_restart_failed']	= 'Restart failed, clear_imported_polls';
$impex_phrases['poll_restart_ok']		= 'Imported polls have been cleared';

$impex_phrases['attachment_not_imported']	= 'Attachment not imported';
$impex_phrases['attachment_not_imported_rem_1']= 'Check the path is correct and the file is present and readable by the webserver ';
$impex_phrases['attachment_not_imported_rem_2']= 'Use the import id to check the source attachment and ensure the post is present';
$impex_phrases['attachment_restart_failed']	= 'Restart failed, clear_imported_attachments';
$impex_phrases['attachment_restart_ok']		= 'Imported attachments have been cleared';

$impex_phrases['pm_not_imported']		= 'Private message not imported';
$impex_phrases['pm_not_imported_rem_1']	= 'Use the import id to check the source Private message userid';
$impex_phrases['pm_not_imported_rem_2']	= 'pmtext imported though pm not assigend to user, find the importpmid';
$impex_phrases['pm_restart_failed']		= 'Restart failed, clear_imported_private_messages';
$impex_phrases['pm_restart_ok']			= 'Imported Private message have been cleared';

$impex_phrases['avatar_not_imported']	= 'Avatar not imported';
$impex_phrases['avatar_not_imported_rem'] = 'Use the import id to check the source database and avatar size';
$impex_phrases['avatar_restart_failed']	= 'Restart failed, clear_imported_avatars';
$impex_phrases['avatar_restart_ok']		= 'Imported Avatars have been cleared';

$impex_phrases['custom_profile_pic_not_imported']	= 'Custom profile pic not imported';
$impex_phrases['custom_profile_pic_not_imported_rem'] = 'Use the import id to check the source database and pic size';
$impex_phrases['custom_profile_pic_restart_failed']	= 'Restart failed, clear_imported_custom_pics';
$impex_phrases['custom_profile_pic_restart_ok']		= 'Imported Custom Profile Pics have been cleared';

$impex_phrases['phrase_not_imported']	= 'Phrase not imported';
$impex_phrases['phrase_not_imported_rem'] = 'Use the import id to check the source database and pic size';
$impex_phrases['phrase_restart_failed']	= 'Restart failed, clear_imported_phrases';
$impex_phrases['phrase_restart_ok']		= 'Imported Phrase have been cleared';


#####################################
# Specific importer text
#####################################

$impex_phrases['discus_mess_file']	=	'Full path and file name of the discus tab messages file';
$impex_phrases['discus_admin_path']	=	'Full Path to discus admin folder (where the users.txt file is located)';

$impex_phrases['ipb_default_admin']	=	'Default admin, userid may need checking';

$impex_phrases['username_email']	=	'Would you like to use the eve/groupee USERNAME instead of the email address for the username in vBulletin';

?>
