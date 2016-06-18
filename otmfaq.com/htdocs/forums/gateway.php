<?php /* $Id: gateway.php,v 1.42.2.3 2005/07/21 02:18:03 ns Exp $

   P a c k a g e   R e l e a s e   V e r s i o n  3.5.0 Alpha 1
   Release date:  21 July 2005
																		*/
/********************************************
   NNTP (Usenet/Newsgroup) Gateway 1.9
   By Kevin Gilbertson <mail@gilby.com>
   Initial Release: Feb. 20, 2002
   Version Release: Mar. 23, 2002

   For support, please visit the thread
   at vbulletin.org
********************************************/
/**************************************************\
*   Hacked and improved by Nikol S <ns@eyo.com.au> *
*   to work with vBulletin V3.                     *
*   Script is based on Kevin Gilbertson's          *
*   release as indicated above.                    *
\**************************************************/

error_reporting(E_ALL & ~E_NOTICE);

define('THIS_SCRIPT', 'gateway.php');
define('VB_AREA', 'NNTP');
define('VERSION_STAMP', 20050720);
define('VERSION', '2.3.2');
define('MY_DIR', dirname(__FILE__));

if (function_exists("set_time_limit") AND !get_cfg_var("safe_mode"))
{
	@set_time_limit(1800);
}

if (!headers_sent())
{
	header("Content-Type: text/plain");
}

require_once(MY_DIR . '/includes/init.php');

// contain convert_url_to_bbcode function
require_once(MY_DIR . '/includes/functions_newpost.php');

// build_post_index function
require_once(MY_DIR . '/includes/functions_databuild.php');

// strip_bbcode
require_once(MY_DIR . '/includes/functions.php');

// Load NNTP classes
require_once(MY_DIR . '/includes/nntp.php');

// POP3 class
require_once(MY_DIR . '/includes/pop.php');

// Load Mime message parser
require_once(MY_DIR . '/includes/mime.php');

// NNTP functions
require_once(MY_DIR . '/includes/functions_nntp.php');

//Making a reference
$nntp =& $GLOBALS['nntp'];

/*
	Variables made availabe in global scope
	cron_log
	settings[]
	good_to_set_postid
	news (object)
	group[]
	grouptype
	msgid_date
	message[]
*/

$nntp['debug'] = intval($_GET['debug']);
$nntp['cron_log'] = '';

if (function_exists('log_cron_action'))
{
	$db =& $vbulletin->db;
	logging("Gateway was invoked by vB Task Scheduler");
}

$technicalemail =& $vbulletin->config['Database']['technicalemail'];

$nntp['msgid_date'] = time() - 1013577770;

// Load nntp_settings
$get_settings = $db->query("
	SELECT varname, value FROM " . TABLE_PREFIX . "nntp_settings
");

while ($setting=$db->fetch_array($get_settings))
{
	$nntp['settings'][$setting['varname']] = $setting['value'];
}

$db->free_result($get_settings);

$nntp_settings =& $nntp['settings'];

if ($nntp_settings['version_stamp'] < VERSION_STAMP)
{
	exit("Please log into the AdminCP and open the Gateway Settings page to upgrade to the latest version");
}

/*Record the Max postid in the post table, so next time the script is run,
	we don't have to query the whole table. We will insert this at the
	end of the run
*/
$get_max_postid = $db->query_first("
	SELECT MAX(postid) as postid FROM " . TABLE_PREFIX . "post
");

$nntp['good_to_set_postid'] = 1;

if (!isset($nntp_settings['last_postid']))
{
	exit("There is no 'last_postid' record in nntp_settings table. Quit");
}

/* Make sure there is no other instance of gateway.php running
	since 30 minutes ago to avoid multiple posts import */
if (isset($nntp_settings['is_running']) AND isset($nntp_settings['last_run']))
{

	if ($nntp_settings['is_running'] == 1)
	{
		//either it is running or it was crashed less than 30 minutes ago.
		if (time() - intval($nntp_settings['last_run']) < 1800)
		{
			exit("Another instance of gateway.php is running, " .
			"try again in 30 minutes if the script was crashed.");
		}
		else
		{
			//it was crashed, need to find the groupid which it was crashed
			$last_group_crashed = $nntp_settings['last_group'];

			//Log the current time
	                $db->query("UPDATE " . TABLE_PREFIX . "nntp_settings
	                SET value = " . time() . " WHERE varname = 'last_run'");
		}
	}
	else
	{
		//Log the current time
		$db->query("
			UPDATE " . TABLE_PREFIX . "nntp_settings
			SET value = 1 WHERE varname = 'is_running'
		");

                $db->query("
			UPDATE " . TABLE_PREFIX . "nntp_settings
			SET value = '" . time() . "' WHERE varname = 'last_run'
		");

	}
}
else
{
	exit("No 'is_running' or 'last_run' record found in the nntp_settings table");
}

$get_groups = $db->query("
	SELECT * FROM " . TABLE_PREFIX . "nntp_groups
	WHERE enabled = 1
");


logging("Gateway version " . VERSION . "  " . $db->num_rows($get_groups) . " group(s) gatewayed.");

while ($nntp['group'] = $db->fetch_array($get_groups))
{
	$group =& $nntp['group'];
	//first we note down the group we are running, in case the script crashes
	$db->query("
		UPDATE " . TABLE_PREFIX . "nntp_settings
		SET value = '" . $group['newsgroupid'] . "'
		WHERE varname = 'last_group'
	");

	// loop through each group
	/*******************************************************************
		send forum posts to usenet
	*******************************************************************/

        if (!ereg('@', $group['newsgroup'])){
		$nntp['grouptype'] = 'news';
		$nntp['news'] = new Net_Nntp();
		$news =& $nntp['news'];
                $news->prepare_connection($group['server'] , 119, $group['newsgroup'], $group['username'], $group['password']);
	} else {
		$nntp['grouptype'] = 'mail';
		$pop3 = new pop();
            if ($pop3->prepare_connection($group['username'], $group['password'], $group['server'])) {

                // Lets connect.
                $pop_connect = $pop3->connect();
                switch ($pop_connect)
                {
                case "-1":
                        logging("Can't connect to server {$group['server']}");
                        break;
                case "-2":
                        logging("Can't read anything from server?");
                        break;
                case "-3":
                        logging("Bad User Name!");
                        break;
                case "-4":
                        logging("Bad Password!");
                        break;
                default:
                        logging("Successfully connected to the '$group[server]' pop3 server.");
			break;
                }
            } else {
                logging("One or more of the following setting is missing: Username; Password; Server");
            }


	}

	//Do not send messages to USENET if send_message settings is false
	if ($nntp_settings['send_message'] != 0)
	{
		// post new threads
		$get_newthreads = $db->query("
			SELECT post.*, thread.*,
			post.dateline AS postdateline, post.msgid AS postmsgid,
			thread.title AS threadtitle, post.visible AS postvisible,
			thread.visible AS threadvisible
			FROM " . TABLE_PREFIX . "post as post LEFT JOIN " .
			TABLE_PREFIX . "thread as thread
			ON (thread.threadid = post.threadid
			AND post.userid = thread.postuserid
			AND post.postid = thread.firstpostid)
			WHERE post.isusenetpost = 0
			AND post.postid > $nntp_settings[last_postid]
			AND thread.forumid = $group[forum]
		");

		while ($newthread = $db->fetch_array($get_newthreads))
		{
			//print_r($newthread);
			sendnews($newthread);
		}

		$db->free_result($get_newthreads);

		// post new replies
		$get_newposts=$db->query("
			SELECT post.*, thread.*,
			post.dateline AS postdateline, post.msgid AS postmsgid,
			post.title AS posttitle, thread.title AS threadtitle,
			post.visible AS postvisible, thread.visible AS threadvisible
			FROM " . TABLE_PREFIX . "post AS post LEFT JOIN " .
			TABLE_PREFIX . "thread AS thread
			ON (post.threadid = thread.threadid
			AND post.postid <> thread.firstpostid)
			WHERE post.isusenetpost = 0
			AND post.postid > $nntp_settings[last_postid]
			AND thread.forumid = $group[forum]
		");

		while ($newpost=$db->fetch_array($get_newposts)){
			// print_r($newpost);
			sendnews($newpost,1);
		}
		$db->free_result(get_newposts);
	}

	/*****************************************************************
		Now retrieve messages from NNTP/Pop3 server
	*****************************************************************/

	if ($nntp['grouptype'] == 'news') {
                // log into the NNTP server
                $lastmsg = ($group['lastmsg'] >= $news->min()) ? $group['lastmsg'] : $news->min();
                $max = $news->max();

		//it was crashed. So skip that message this time
		if ($group['newsgroupid'] == $last_group_crashed)
		{
			$lastmsg = $lastmsg + 2;
		}
	} else {
                $lastmsg = 0;
                $max = 0;

		// Lets connect, and see how many messages await us.
		$max = $pop3->howmany();
		logging("Retrieving $max E-mails from the '$group[server]' pop3 server.");
	}

	for ($current = $lastmsg+1; $current <= $max; $current++){

		if ($nntp['grouptype'] == 'news'){
			$post = $news->get_article($current);
		} else {

			$post = $pop3->getemail($current);

			//logging($pop3->showlog());
		}
		$last_loaded = 0;
		if ($post) {
			logging("Getting message number $current: ");
			//print_r($post);
			$last_loaded = $current;

			$mime = new mime();
			$mime->decode($post);
			$nntp['message'] = $mime->get_msg_array();
			$message =& $nntp['message'];

			// Decode the subject and from headers";
			if (function_exists('imap_mime_header_decode')){
				if ($message['subject']){
					$elements=imap_mime_header_decode($message['subject']);
					$message['subject'] = '';
					for($i=0;$i<count($elements);$i++) {
					    $message['subject'] .= $elements[$i]->text;
					}
				}
				if ($message['from']){
					$elements=imap_mime_header_decode($message['from']);
					$message['from'] = '';
					for($i=0;$i<count($elements);$i++) {
					    $message['from'] .= $elements[$i]->text;
					}
				}

			}

			//Provides an option of which time stamp does the imported message use
			if ($nntp_settings['use_post_time'] == 1) {
				$date = (strtotime($message['date']) === -1)? time() : strtotime($message['date']);
			} else {
				$date = time();
			}

			//print_r($message);

			if (trim($message['user-agent']) == trim($nntp_settings['useragent'])
				AND $nntp_settings['organization_check'] == 0)
			{
                                logging("Skip, post was sent from our forum.");
			}
			else if (trim($message['user-agent']) == trim($nntp_settings['useragent'])
				AND $nntp_settings['organization_check'] == 1
				AND trim($message['organization']) == trim($nntp_settings['organization']))
			{
                                //added organization so that we don't skip fellow gateway's posts
                                logging("Skip, post was sent from our forum.");
			}
			else if (stristr(trim($message['x-no-archive']), 'yes') AND
				isset($nntp_settings['honor_no-archive']) AND
				$nntp_settings['honor_no-archive'] != 0)
			{
				logging("Skip, X-No-Archive headers is set.");
			}
			elseif ($nntp['grouptype'] == 'mail'
				AND $group['prefix']
				AND stristr($message['subject'], $group['prefix']) == false)
			{
				logging("Skip, not matching prefix: \"" . $group['prefix'] . "\"");
			}
			else
			{
			   $kf = killfile_match();
			   if ($kf)
			   {
				logging("Skip, killfile \"" . $kf . "\" match.");
			   }
			   else
			   {
				$threadid = 0;
				$attachmentid = 0;
				$parentid = 0;

				if ($nntp['grouptype'] != 'news')
				{
					$message['text'] = stripfooter($message['text']);
				}

				// get the text message
				if (!$message['text'] and $message['html']){
					$pattern = array(
						"/\r?\n/",
						"/<br([^>]*)>/siU",
						"/<[\/]*(div|p)([^>]*)>/siU",
						"/<b>(.*)<\/b>/siU",
						"/<i>(.*)<\/i>/siU",
						"/<a[^>]*href=([^ >]*)>(.*)<\/a>/siU"
					);
					$replace = array(
						" ",
						"\n",
						"\n\n",
						"[b]\\1[/b]",
						"[i]\\1[/i]",
						"[url=\\1]\\2[/url]"
					);

					$message['text'] = strip_tags(preg_replace($pattern, $replace, $message['html']));
				}

				$message['text'] = convert_url_to_bbcode($message['text']);
				//Hide real email address for mailing lists
				if ($nntp['grouptype'] == 'mail')
				{
					$message['text'] = preg_replace('/([-_.\\w]+)@([\\w]+[-\\w]+)\\./', '\\1 (AT) \\2 (DOT) ', $message['text']);
				}
				$message['text'] = preg_replace("/((\n[ ]*>[^\n]*)+)/", "[color=blue]\\1[/color]", $message['text']);
				$message['text'] = preg_replace("/((\n[ ]*>[ ]*>[^\n]*)+)/", "[color=green]\\1[/color]", $message['text']);
				$message['text'] = preg_replace("/((\n[ ]*>[ ]*>[ ]*>[^\n]*)+)/", "[color=darkred]\\1[/color]", $message['text']);

				//Separate name and email address
				$from_name = from_name($message['from']);
				$from_email = from_email($message['from']);

				//fetch forum info, index builder requires this
				$forumid = $group['forum'];
				$foruminfo = fetch_foruminfo($forumid);

				if (empty($message['subject'])) {
					//shouldn't need this according to RFC1036, but we never know.
					$subject = 'No Subject';
				} else {
	                                $subject = htmlspecialchars(trim($message['subject']));
				}

				// Find out the correct thread;
				//Base the thread on Reference instead of subject
				if ($message['references']){
				    $my_ref = trim($message['references']);

				    //first find the parentid, should be the last msgid in $my_ref
				    $my_pos = strrpos($my_ref, "<");
				    $par_msgid = substr($my_ref, $my_pos);

                                    $get_postid = $db->query_first("SELECT post.postid FROM " .
                                    TABLE_PREFIX . "thread AS thread, " . TABLE_PREFIX . "post AS post
                                    WHERE thread.threadid = post.threadid AND
                                    (post.msgid = '" . addslashes($par_msgid) . "') AND
                                    thread.forumid = " . $forumid . "
                                    ORDER BY post.dateline DESC");

                                    if ($get_postid) {
                                        $parentid = $get_postid['postid'];
                                    }

				    //find the thread
				    while ($ref_msgid_pos = strpos($my_ref, ">")) {
					$ref_msgid = substr($my_ref, 0, $ref_msgid_pos + 1);
					$my_ref = trim(substr($my_ref, $ref_msgid_pos + 1));

					$get_threadid = $db->query_first("SELECT thread.threadid FROM " .
					TABLE_PREFIX . "thread AS thread, " . TABLE_PREFIX . "post AS post
					WHERE thread.threadid = post.threadid AND
					(post.msgid = '" . addslashes($ref_msgid) . "') AND
					thread.forumid = " . $forumid . "
					ORDER BY post.dateline DESC");

					if ($get_threadid) {
						$threadid = $get_threadid['threadid'];
						logging("'$subject' from " . $from_name .
						". Thread found by References.");
						break;
					}
				    } //while
				}//has reference

				if ($threadid == 0 AND $nntp['grouptype'] == 'mail' AND $nntp_settings['thread_by_subject']
					AND eregi("^(re:|ynt:|fw:|fwd:)", $subject))
				{
					/*either no reference or no matching thread found
					  we are going to try the title match method     */
					$orig_subj = eregi_replace("^(re:|ynt:|fw:|fwd:)[ ]*", "", $subject);
					$orig_subj0 = eregi_replace("^(re:|ynt:|fw:|fwd:)[ ]*", "", $orig_subj);
					$get_ids = $db->query_first("
						SELECT " . TABLE_PREFIX . "thread.threadid, " . TABLE_PREFIX . "post.postid
						FROM " . TABLE_PREFIX . "thread
						LEFT JOIN " . TABLE_PREFIX . "post
						ON " . TABLE_PREFIX . "thread.threadid = " . TABLE_PREFIX . "post.threadid
						WHERE " . TABLE_PREFIX . "thread.forumid = " . $forumid . "
						AND (" . TABLE_PREFIX . "thread.title = '" . addslashes($orig_subj) . "'
						OR " . TABLE_PREFIX . "thread.title = '" . addslashes($subject) . "'
						OR " . TABLE_PREFIX . "thread.title = '" . addslashes($orig_subj0) . "')
						ORDER BY " . TABLE_PREFIX . "thread.dateline DESC, " . TABLE_PREFIX . "post.dateline
					");
					if ($get_ids)
					{
						$threadid = $get_ids['threadid'];
						$parentid = $get_ids['postid'];
						logging("'$subject' from " . $from_name .
							". Thread found by Subject.");
					}
					$db->free_result($get_ids);
				}
				/* TODO, there should be an option to allow a message to create a new
				   thread when the Subject is changed, even when the full References are
				   presented. It is the standard behaviour for Agent */

				// if the correct thread was found, insert it.
				if ($threadid) {

					$parentid = $parentid + 0;

					$postid = insert_post($threadid, $forumid, $foruminfo, $subject, $from_name, $from_email, $date, $parentid);

					// update thread
					$db->query("UPDATE " . TABLE_PREFIX . "thread
						SET lastpost = '" . $date . "',
						replycount = replycount + 1,
						lastposter = '" . addslashes($from_name) . "'
						WHERE threadid = $threadid
					");

					// update forum
					$db->query("
						UPDATE " . TABLE_PREFIX . "forum
						SET replycount = replycount + 1,
						lastpost = '" . $date . "',
						lastposter = '" . addslashes($from_name) . "',
						lastthreadid = $threadid,
						lastthread = '" . addslashes($subject) . "'
						WHERE forumid IN ({$foruminfo['parentlist']})
					");

					// send out email notices
					exec_send_notification($threadid, "0", $postid);

				} else {
					//can only be here if no thread is found
					//Needs to create new thread

					// Create thread
        		                if ($vbulletin->options['similarthreadsearch'])
                        		{
						require_once(MY_DIR . '/includes/functions_search.php');
	                	                $similarthreads = fetch_similar_threads($subject);
		                        }
                		        else
		                        {
                		                $similarthreads = '';
		                        }

					$db->query("INSERT INTO " . TABLE_PREFIX .
						"thread (title, lastpost, forumid, open, replycount,
						postusername, postuserid, lastposter, dateline, iconid,
						visible, views, similar)
						VALUES ('" . addslashes($subject) . "', '" . $date . "', $forumid, 1, 0,
						'" . addslashes($from_name) . "', 0,
						'" . addslashes($from_name) . "', '" . $date . "', 0, 1, 0,
						'" . $similarthreads . "')
					");

					$threadid = $db->insert_id();

					//insert_post($threadid, $forumid, $foruminfo, $subject, $from_name, $from_email, $date)
					$postid = insert_post($threadid, $forumid, $foruminfo, $subject, $from_name, $from_email, $date);

					// update the forum counts
					$db->query("UPDATE " . TABLE_PREFIX . "forum
						SET replycount = replycount + 1,
						threadcount = threadcount + 1,
						lastpost = '" . $date . "',
						lastposter = '" . addslashes($from_name) . "',
						lastthread = '" . addslashes($subject) . "',
						lastthreadid = $threadid
						WHERE forumid IN ({$foruminfo['parentlist']})");

					logging("'$subject' from ". $from_name . ". New thread.");

				} //new thread or not
			   } //killfile match
				if ($nntp['grouptype'] == 'mail')
				{
					$pop3->delete_mail($current);
				}
			} //Skip message?
		} //if ($post)

		// update the last msg
		if ($last_loaded){
			$db->query("UPDATE " . TABLE_PREFIX . "nntp_groups
			SET lastmsg = $last_loaded
			WHERE newsgroup = '" . addslashes($group['newsgroup']) . "'");
		}

		$last_loaded = 0;
		if ($nntp_settings['pause_seconds'])
		{
			sleep ($nntp_settings['pause_seconds']);
		}
	} //for each message loop

	if ($nntp['grouptype'] == 'mail') {
		$pop3->disconnect();
	} else {
		//We have finished this group, so clear the last_group
		$db->query("
			UPDATE " . TABLE_PREFIX . "nntp_settings
			SET value = 0
			WHERE varname = 'last_group'
		");

	}
	/********************************************************************
		Some posts may not be available at the time of retrieval.
		It is neccessary to fix the parentid of previously retrieved
		posts.
	*********************************************************************/

	/*TODO, need to find a way to go back to the posts to fix parentid.

	Also need to do something about a top level message retrieved after the children
	messages. A new thread should not be created.
	*/
}

// Reset 'is_running' in the nntp_settings table
$db->query("
	UPDATE " . TABLE_PREFIX . "nntp_settings
	SET value = 0 WHERE varname = 'is_running'
");

// Update last_postid, now used for waiting for moderation posts to catch up
if ($nntp['good_to_set_postid'] == 1)
{
	$db->query("UPDATE " . TABLE_PREFIX . "nntp_settings
		SET value = " . ((empty($get_max_postid))?(0):($get_max_postid['postid'])) .
		" WHERE varname = 'last_postid'
	");
}

//if called by vB3 cron tasks
if (function_exists('log_cron_action'))
{
	log_cron_action('Ran NNTP Gateway<br />' . $nntp['cron_log'], $nextitem);
}

if (!empty($technicalemail) AND $nntp_settings['do_cron_log'])
{
	@mail ($technicalemail, 'NNTP gateway debug message',
		str_replace("<br />", "\r\n", $nntp['cron_log']), "From: $technicalemail");
}

?>