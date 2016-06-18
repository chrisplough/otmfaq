<?php /* $Id: functions_nntp.php,v 1.2.2.3 2005/07/21 02:18:11 ns Exp $ */


/*======================================================================*\
|| #################################################################### ||
|| # This file was part of gateway.php file. Please consult current   # ||
|| # gateway.php and readme.txt file for usage and conditions.        # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);

function format_text($body)
{
	$array = preg_split("/\[(\/)*(list|quote|code|php)\s*=?\s*([^\]]*)\]/i",$body,-1,PREG_SPLIT_DELIM_CAPTURE);
	$level = 0;
	$type[0] = 'normal';
	$parsed_body = '';

	foreach ($array as $section){
		if (strtolower($section) == 'list' and $last != '/'){
			$level++;
			$type[$level] = 'list';
		} else if (strtolower($section) == 'list' and $last == '/'){
			$level--;
		} else if (strtolower($section) == 'quote' and $last != '/'){
			$level++;
			$type[$level] = 'quote';
			$is_quote = 1;
		} else if (strtolower($section) == 'quote' and $last == '/'){
			$level--;
		} else if (strtolower($section) == 'php' and $last != '/'){
			$level++;
			$type[$level] = 'php';

			$parsed_body .= "\nPHP code:\n--------------------\n";
		} else if (strtolower($section) == 'php' and $last == '/'){
			$level--;
			$parsed_body .= "\n--------------------\n";
		} else if (strtolower($section) == 'code' and $last != '/'){
			$level++;
			$type[$level] = 'code';
			$parsed_body .= "\nCode:\n--------------------\n";
		} else if (strtolower($section) == 'code' and $last == '/'){
			$level--;
			$parsed_body .= "\n--------------------\n";
		} else if ($section == 'A' or $section == '1' or ($section == '/'
			AND $is_quote == 0)) {
			// put stuff for numbering it ///// UNFINISHED
		} else if ($is_quote == 1) {
				//could be the user name or quoted text
				$temp = $section;
				$is_quote = 2;
		} else if ($section == '/' AND $is_quote == 2) {
			//commit $temp to the $parsed_body
				// parse vb code
				$temp = parsevb($temp);
				// word wrap it
				//$temp = wordwrap($temp, 72 - $level*2);
                                $temp = wordwrap($temp, 72);

				// indent it
				$pad = padding($type, $level);
				$temp = ereg_replace("\n *","\n$pad",$pad.$temp);
				$parsed_body .= $temp;
				$parsed_body .= "\n\n";
				$temp = '';
				$is_quote = 0;
		} else if ($is_quote == 2) {
                                // parse vb code
                                $section = parsevb($section);
                                // word wrap it
                                //$section = wordwrap($section, 72 - $level*2);
                                $section = wordwrap($section, 72);
                                // indent it
                                $pad = padding($type, $level);
                                $section = ereg_replace("\n *","\n$pad",$pad.$section);
                                //commit $temp to the quote from user
				if ($temp != '' ) {
	                                $section = $temp . " Wrote: \n" . $section;
				}
				$parsed_body .= $section;
                                $temp = '';
				$is_quote = 0;
		} else {
			if ($type[$level] == 'list'){
				// parse vb code
				$section = parsevb($section);
				// create bullets
				$section = ereg_replace("\n*\[\*\]","\n- ",$section);
				// word wrap it
				$section = wordwrap($section, 72 - $level*2);
				// indent it
				$pad = padding($type, $level);
				$section = ereg_replace("\n *","\n$pad",$pad.$section);
				// fix bulletted ones
				$section = ereg_replace("\n$pad- ","\n".substr($pad,0,$level*2-2)."- ","\n".$section."\n");
				$section = substr($section,1,strlen($section)-2);

			} else if ($type[$level] == 'php' or $type[$level] == 'code'){
				// parse vb code
				$section = parsevb($section);
				// indent it
				$pad = padding($type, $level);
				$section = ereg_replace("\n *","\n$pad",$pad.$section);
			} else {
				// parse vb code
				$section = parsevb($section);
				// word wrap it
				$section = wordwrap($section, 72 - $level*2);
				// indent it
				$pad = padding($type, $level);
				$section = ereg_replace("\n *","\n$pad",$pad.$section);
			}
			// add to body string

			$parsed_body .= $section;
		}

		$last = $section;
	}

	return strip_bbcode($parsed_body);
}
function parsevb($string)
{
	$string = "\n".$string."\n";

    for ($i = 1; $i <= 10; $i++) {
	$pattern = array(
	"/[\n\r]\[b\](.*)\[\/b\][\n\r]\n/esiU",
	"/\[b\](.*)\[\/b\]/siU",
	"/\[u\](.*)\[\/u\]/esiU",
	"/\[i\](.*)\[\/i\]/siU",
	"/\[img\](.*)\[\/img\]/siU",
	"/\[url\](.*)\[\/url\]/esiU",
	"/\[url=['\"]*([^\]\"']*)['\"]*\](.*)\[\/url\]/esiU",
	"/\[email\](.*)\[\/email\]/siU",
	"/\[email=['\"]*([^\]\"']*)['\"]*\](.*)\[\/email\]/esiU",
	"/\[color=['\"]*([^\]\"']*)['\"]*\](.*)\[\/color\]/siU",
	"/\[size=['\"]*([^\]\"']*)['\"]*\](.*)\[\/size\]/esiU",
	"/\[font=['\"]*([^\]\"']*)['\"]*\](.*)\[\/font\]/siU"
	);
	$replace = array(
	"'\n'.strtoupper('\\1').'\n'",
	"*\\1*",
	"'_'.ereg_replace(' ','_','\\1').'_'",
	"-\\1-",
	"[image: \\1]",
	"tinyurl('\\1')",
	"checkurls('\\1','\\2')",
	"\\1",
	"checkurls('\\1','\\2')",
	"\\2",
	"sizeit('\\1','\\2')",
	"\\2"
	);
        $org_len = strlen($string);

	$string = preg_replace($pattern,$replace,$string);

	if ( strlen($string) == $org_len ) {
		break;
	}
    }

	$string = substr($string,1,strlen($string)-2);

	return $string;
}

function padding($type, $level)
{
	$string = '';
	$i = $level;
	while ($i>0){
		if ($type[$i] == 'quote'){
			$string .= "> ";
		} else {
			$string .= "  ";
		}
		$i--;
	}
	return $string;
}

function checkurls($a,$b)
{

	if ($a == $b)
	{
		return tinyurl($a);
	}
	else
	{
		return "'$b' (".tinyurl($a).")";
	}
}

// create a tiny url from TinyURL.com
function tinyurl($url)
{
	global $nntp;
	$nntp_settings = $nntp['settings'];

	// check for tinyurl length setting
	if (!$nntp_settings['tinyurl']){
		$length = 70;
	} else {
		$length = $nntp_settings['tinyurl'];
	}

	if (strlen($url) >= $length){
		$tinyurl = @file ("http://tinyurl.com/api-create.php?url=$url");
		if (is_array($tinyurl)){
			$tinyurl = join ('', $tinyurl);
		} else {
			$tinyurl = $url;
		}
	} else { $tinyurl = $url; }

	return $tinyurl;
}

function sizeit($size, $content)
{
	if ($size == 1) {
		$content = strtolower($content);
	} else if ($size == 3){
		$content = "::".$content."::";
	} else if ($size == 4){
		$content = strtoupper($content);
	} else if ($size == 5){
		$content = " ".chunk_split($content, 1, ' ');
	} else if ($size == 6){
		$content = chunk_split($content, 1, '-');
	} else if ($size >= 7){
		$content = " ".strtoupper(chunk_split($content, 1, ' '));
	}
	return $content;
}

function logging($text)
{
	global $nntp;

	if (isset($nntp['debug']) AND $nntp['debug'] == 1)
	{
		if (!headers_sent())
		{
			header("Content-Type: text/plain");
		}

		echo $text . "\r\n";
		flush();
		ob_flush();
	}

	if ($nntp['settings']['do_cron_log'])
	{
		$GLOBALS['nntp']['cron_log'] .= $text . "<br />";
	}
}


    /**
     * Returns only the sender's name from the "From" header
     */

function from_name($from_raw)
{

	if (ereg("<", $from_raw))
	{
	    $from_name_find1 = explode("<", $from_raw);
	    $from_name_find = $from_name_find1['0'];
	}
	elseif (ereg("\(", $from_raw))
	{
	    $from_name_find2 = explode("(", $from_raw);
	    $from_name_find1 = explode(")", $from_name_find2['1']);
	    $from_name_find = $from_name_find1['0'];
	}
	else
	{
	    $from_name_find = $from_raw;
	}

	$from_name = trim(ereg_replace('"','',$from_name_find));

	return $from_name;
}

    /**
     * Returns only the sender's email adress from the "From" header
     */

function from_email($from_raw)
{

	if (ereg("@", $from_raw))
	{
		if (ereg("\(", $from_raw))
		{
			$from_email_find1 = explode("(", $from_raw);
			$from_email_find = trim($from_email_find1['0']);
		}
		elseif (ereg("<", $from_raw))
		{
			$from_email_find2 = explode("<", $from_raw);
			$from_email_find1 = explode(">", $from_email_find2['1']);
			$from_email_find = trim($from_email_find1['0']);
		}
		else
		{
			$from_email_find = trim($from_raw);
		}
	}
	return $from_email_find;
}

function sendnews($newthread, $isreply=false)
{
	global $nntp, $db;

	$nntp_settings = $nntp['settings'];
	$group = $nntp['group'];
	$grouptype = $nntp['grouptype'];
	$news =& $nntp['news'];
	$good_to_set_postid = $nntp['good_to_set_postid'];
	$msgid_date = $nntp['msgid_date'];

	$subject = unhtmlspecialchars($newthread['threadtitle']);

	if ($newthread['postvisible'] == 0 OR $newthread['threadvisible'] == 0)
	{
		$good_to_set_postid = 0;
                logging("Not sending '$subject' from $newthread[username], it is in moderating queue.");

		return;
	}

	//TODO we should first check if the thread is too old
	//  logging("Thread is too old (" . $subject . " by " . $newthread['username'] .")");


	// get user's signature
	if ($newthread['userid'])
	{
		$signature = "\n-- \n";
		$signature .= $newthread['username'];

			//if ($userinfo[customtitle]){ $signature .= " - $userinfo[usertitle]"; }

		$userinfo = $db->query_first("
			SELECT email, signature, usertitle, customtitle
			FROM " . TABLE_PREFIX . "user AS user
			LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield
			ON user.userid = usertextfield.userid
			WHERE user.userid = $newthread[userid]
		");

		if ($nntp_settings['nosignature'] == 0)
		{
			//Only show signature if showsignature is true for this post
			if ($newthread['showsignature'])
			{
				  $signature .= "\n\n";
				  $signature .= format_text($userinfo['signature']);
			}
		}

		if ($nntp_settings['nofooter'] == 0)
		{
			$signature .= "\n------------------------------------------------------------------------\n";
			$signature .= "{$newthread['username']}'s Profile: {$nntp_settings['profileurl']}{$newthread['userid']}\n";
			$signature .= "View this thread: {$nntp_settings['threadurl']}{$newthread['threadid']}\n";
		}
	}
	else
	{
		$signature = "\n-- \n";
		if ($nntp_settings['nofooter'] == 0)
		{
			$userinfo = false;
			$signature .= "{$newthread['username']} - Unregistered User";
			$signature .= "\n------------------------------------------------------------------------\n";
			$signature .= "View this thread: {$nntp_settings['threadurl']}{$newthread['threadid']}\n";
		}
	}

	if ($nntp_settings['default_footer'])
	{
		//Add the default footer
		$signature .= "\n" . $nntp_settings['default_footer'] . "\n";
	}

	// get poll info
	if ($newthread['pollid'])
	{
		$get_pollinfo=$db->query("SELECT question,options FROM " .
		TABLE_PREFIX . "poll WHERE pollid={$newthread['pollid']} LIMIT 1");

		$pollinfo=$db->fetch_array($get_pollinfo);
		$polltext = "\n------------------------------------------------------------------------\n";
		$polltext .= "A poll associated with this post was created, to vote and see the\n";
		$polltext .= "results, please visit {$nntp_settings['threadurl']}{$newthread['threadid']}\n";
		$polltext .= "------------------------------------------------------------------------\n";
		$polltext .= format_text("Question: {$pollinfo['question']}\n[list][*]".ereg_replace("\|\|\|","[*]",$pollinfo['options'])."[/list]");
		$polltext .= "\n------------------------------------------------------------------------\n";
	}
	else
	{
		$polltext = false;
	}

	// get attachment info
	if ($newthread['attach'])
	{
		$get_attachinfo=$db->query("
			SELECT filename,attachmentid
			FROM " . TABLE_PREFIX . "attachment
			WHERE postid= $newthread[postid]
		");

		while ($attachinfo=$db->fetch_array($get_attachinfo))
		{
			$attachtext = "\n+-------------------------------------------------------------------+\n";
			$attachtext .= "|".str_pad("Filename: {$attachinfo['filename']}", 67)."|\n";
			$attachtext .= "|".str_pad("Download: {$nntp_settings['attachmenturl']}{$attachinfo['attachmentid']}", 67)."|";
			$attachtext .= "\n+-------------------------------------------------------------------+\n";
		}
	}
	else
	{
		$attachtext = false;
	}

	// Create message-ID
	if ($newthread['userid']){
		// Take out weird chars
		$u = ereg_replace("[^a-zA-Z0-9._ \-]","",$newthread['username']);
		$u = ereg_replace("^[^a-zA-Z0-9]+","",$u);
		$u = ereg_replace("[ .]+",".",$u);
	}
	else
	{
		$u = "Guest";
	}

	$msgid = $u . '.' . base_convert ($msgid_date, 10, 36) . '@'.$nntp_settings['email'];
	$msgid_date++;

	// Piece it together
	$message = $polltext . "\n" . format_text($newthread['pagetext']) ."\n\n". $attachtext . $signature;

	if ($isreply)
	{
		if (!preg_match("/re:\s?.*/i", $subject))
		{
                        $subject = "Re: " . $subject;
		}
	}

	// Get references
	if ($isreply)
	{
		if (empty($newthread['parentid']))
		{
			$ref = '';
		}
		else
		{
			$get_references=$db->query_first("
				SELECT msgid,ref
				FROM " . TABLE_PREFIX . "post
				WHERE postid = $newthread[parentid]
			");

			$references = $get_references['ref'] . " " . $get_references['msgid'];
			$ref = "\r\nReferences: ". stripslashes($references);
		}
	}
	else
	{
		$ref = '';
	}

	// Determine 'From: ' header
	if ($group['sender'] == 'use_real_email')
	{
		if (isset($userinfo))
		{
			$from_header = "$newthread[username] <$userinfo[email]>";
		}
		else
		{
			$from_header = "$newthread[username] <$msgid>";
		}
	}
	elseif (!$group['sender'])
	{
		$from_header = "$newthread[username] <$msgid>";
	}
	else
	{
		$from_header = $group['sender'];
	}

	// post it
	if ($grouptype=='news'){
		$response = $news->post($subject,
		$group['newsgroup'],
		$from_header,
		$message,
		"Date: ".date("D, j M Y H:i:s O",
		$newthread['postdateline']).
		"\r\nMessage-ID: <$msgid>\r\nOrganization: " .
		$nntp_settings['organization'] . "\r\nUser-Agent: " .
		$nntp_settings['useragent'] . "\r\nX-Newsreader: " .
		$nntp_settings['useragent'] . "\r\nX-Originating-IP: " .
		$newthread['ipaddress'] . $ref);
	}
	else
	{
		mail($group['newsgroup'],
		$subject,
		$message,
		"From: $from_header\r\nDate: " .
		date("D, j M Y H:i:s O",$newthread['postdateline']) .
		"\r\nMessage-ID: <$msgid>\r\nOrganization: " .
		$nntp_settings['organization'] ."\r\nUser-Agent: " .
		$nntp_settings['useragent'] . "\r\nX-Originating-IP: " .
		$newthread['ipaddress'] . $ref);

		$response = '240 Message emailed';
	}

	$response_code = substr($response,0,3);
//	if (!$response_code == 240 AND $good_to_set_postid = 1) {
//		$good_to_set_postid = 0;
//	}
	if ($response_code == 240){
		// mark as sent and specify message id
		$db->query("
			UPDATE " . TABLE_PREFIX . "post SET isusenetpost =1,
			msgid ='<".addslashes($msgid).">' , ref = '".$references."'
			WHERE postid={$newthread['postid']}
		");
	}
	elseif ($response_code == 440)
	{
		//440 Not allowed to post
		echo "Your NNTP server says posting is prohibited!";
	}
	elseif ($response_code == 437 OR $response_code == 435)
	{
		//437 is article rejected - do not try again
		//435 is article not wanted - do not send it
		$db->query("
			UPDATE " . TABLE_PREFIX . "post SET isusenetpost =1
			WHERE postid = $newthread[postid]
		");
	}

	logging("Posting Message '" . $subject . "' from {$newthread['username']}. Result: " . $response);
}

//Insert post and follow up

function insert_post($threadid, $forumid, $foruminfo, $subject, $from_name, $from_email, $date, $parentid = 0)
{
	global $db, $nntp;

	$message =& $nntp['message'];

	$db->query("INSERT INTO " . TABLE_PREFIX . "post
		(postid, threadid, title, username, userid, dateline, pagetext,
		allowsmilie, showsignature, ipaddress, iconid, visible,
		isusenetpost, msgid, ref, parentid) VALUES
		(NULL, $threadid, '". addslashes($subject) . "',
		'" . addslashes($from_name) . "', 0, '" . $date . "',
		'" . addslashes($message['text']) . "', 1, 0,
		'" . addslashes($from_email) . "', 0, 1, 1,
		'" . addslashes($message['message-id']) . "',
		'" . addslashes($message['references']) . "', "
		. $parentid . ")");

	$postid=$db->insert_id();

	//So that thread preview works
        $db->query("
		UPDATE " . TABLE_PREFIX . "thread
                SET firstpostid = $postid
		WHERE threadid = $threadid
	");


	//save attachments if any
	if ($message['attachments'])
	{
		process_attachments($date, $postid, $threadid, $forumid);
	}

	// Index post for searching
	build_post_index($postid, $foruminfo);

	return $postid;
}

/* Loop through all the attachments and insert into tables or to files */
function process_attachments ($date, $postid, $threadid, $forumid) //routine originally written by KevinM
{
	global $vbulletin, $nntp;

	$nntp_settings =& $nntp['settings'];
	$message =& $nntp['message'];

    if (!$nntp_settings['no_attach'])
    {
	// Are the forum permissions set to allow attachments?
	$forumpermsok = checkforumperms($forumid);
	if ($forumpermsok)
	{
		//Loop through each attachment to the same post
		$attachcount = 0;
		$attaches = 0;  // this is used as the number of actual attachments inserted into vb, as we may reject some on the way.

		for ($i = 1; $i <= $message['attachments']; $i++)
		{
			//Check the file extension is OK
			$extensionok = checkattachtypes($message['attachment' . $i]['headers']['filename']);
			if ($extensionok)
			{

				if ($vbulletin->options['attachthumbs'])
				{
					$thumbnail = makemeathumbnail($i);
				}

				$filesize = getmefilesize($i);

				if ($vbulletin->options['attachfile'] == 0) // attachments are in the db
				{
					saveintodb($i, $thumbnail, $filesize, $date, $postid);
				}
				else // save as files
				{
					saveasfile($i, $thumbnail, $filesize, $date, $postid);
				}

				$attaches++;
			}
			$attachcount++;
		}
		if ($attaches > 0)
		{
			finishattachments($postid, $threadid, $attaches);
		}
	}
    }//skip attach
}

function saveintodb($i, $thumbnail, $filesize, $date, $postid)
{
	global $vbulletin, $db, $nntp;
	$message =& $nntp['message'];

   // save with thumbs into the db
   $filesize = $filesize + 0;
   if ($vbulletin->options['attachthumbs'])
   {
	$db->query("INSERT INTO " . TABLE_PREFIX . "attachment SET dateline = '$date',
		filename = '" . addslashes($message['attachment' . $i]['headers']['filename']) . "',
		filedata = '" . $db->escape_string($message['attachment' . $i]['body']) . "', visible = 1,
		thumbnail = '" . $db->escape_string($thumbnail['filedata']) . "',
		thumbnail_dateline = " . $thumbnail['dateline'] . ",
                thumbnail_filesize = " . $thumbnail['filesize'] . ",
		filesize = $filesize,
		postid = $postid");
	//logging("inserted with thumb");
   }
   else  //and without
   {
	$db->query("INSERT INTO " . TABLE_PREFIX . "attachment SET dateline = '$date',
		filename = '" . addslashes($message['attachment' . $i]['headers']['filename']) . "',
		filedata = '" . $db->escape_string($message['attachment' . $i]['body']) . "', visible = 1,
		filesize = $filesize,
		postid = $postid");
	//logging("inserted without thumb");
   }
	//logging("insertion complete");
}

function saveasfile ($i, $thumbnail, $filesize, $date, $postid)
{
	global $vbulletin, $db, $nntp;
	$message =& $nntp['message'];

	//fetch_attachment_path()
	require_once('./includes/functions_file.php');
   $filesize = $filesize + 0;
   if(ini_get('safe_mode') == 1 OR strtolower(ini_get('safe_mode')) == 'on')
   {
	logging("your server has safe_mode enabled. Attachments cannot be saved as files");
   }
   else
   {
	if ($vbulletin->options['attachthumbs']) // save with thumbs into the db
	{
		$db->query("
			INSERT INTO " . TABLE_PREFIX . "attachment SET dateline = '$date',
			filename = '" . addslashes($message['attachment' . $i]['headers']['filename']) . "',
			filedata = '', visible = 1,
			thumbnail_dateline = " . $thumbnail['dateline'] . ",
			thumbnail_filesize = " . $thumbnail['filesize'] . ",
			filesize = $filesize,
			postid = $postid
		");
		//logging("saved with thumb");
	}
	else
	{
		$db->query("
			INSERT INTO " . TABLE_PREFIX . "attachment SET dateline = '$date',
			filename = '" . addslashes($message['attachment' . $i]['headers']['filename']) . "',
			filedata = '', visible = 1,
			filesize = $filesize,
			postid = $postid
		");
		//logging("saved without thumb");
	}
	$attach_id = $db->insert_id();

	//logging("saved the parts in the db, now to save the file.");

	$path = fetch_attachment_path("0", $attach_id);
	if ($fp = fopen($path, 'wb'))
	{
		fwrite($fp, $message['attachment' . $i]['body']);
		fclose($fp);
		logging("inserted attachment as file");

                $path_thumb = fetch_attachment_path("0", $attach_id, true);
                if ($fp = fopen($path_thumb, 'wb'))
		{
	                fwrite($fp, $thumbnail['filedata']);
        	        fclose($fp);
			logging("inserted thumb as file");
		}
                unset($thumbnail);
	}
	else
	{
		logging("could not insert attachment as file");
	}
	//logging("insertion as file completed ");
   }
}

function makemeathumbnail($i)
{
	global $vbulletin, $nntp;

	$message =& $nntp['message'];

	require_once("includes/functions_image.php");

	if ($vbulletin->options['safeupload'])
	{
		$filename = $vbulletin->options['tmppath'] . '/' . md5(uniqid(microtime()));
	}
	else
	{
		$filename = tempnam(ini_get('upload_tmp_dir'), 'vbthumb');
	}

	$filenum = fopen($filename, 'wb');
	fwrite($filenum, $message['attachment' . $i]['body']);
	fclose($filenum);

	$fileinfo = array(
			'name' => $message['attachment' . $i]['headers']['filename'],
			'tmp_name' => $filename
		);
	$imageerror = '';
	$thumbnail = fetch_thumbnail_from_image($fileinfo, $imageerror);
	unlink($filename);
	//logging("thumbnail created - I think ");
	return $thumbnail;
}

function getmefilesize($i)
{
	global $nntp, $vbulletin;

	$message = $nntp['message'];

	if ($vbulletin->options['safeupload'])
	{
		$filename = $vbulletin->options['tmppath'] . '/' . md5(uniqid(microtime()));
	}
	else
	{
		$filename = tempnam(ini_get('upload_tmp_dir'), 'vbthumb');
	}
	$filenum = fopen($filename, 'wb');
	fwrite($filenum, $message['attachment' . $i]['body']);
	fclose($filenum);
	$filesize = @filesize($filename);
	unlink($filename);
	//logging("filsize = " . $filesize);
	return $filesize;
}

function checkforumperms($forumid)
{

	$forumperms2 = false;
	$forumperms = fetch_permissions($forumid);
	if (!$forumperms)
	{
		logging("This forum does not allow attachments, therefore attachment ignored.");
	}
	else
	{
		$forumperms2 = true;
	}
	return $forumperms2;
}

function checkattachtypes($checkthis)
{
	global $db;

	$datastore = $db->query_first("
		SELECT data FROM " . TABLE_PREFIX . "datastore
		WHERE title = 'attachmentcache'
	");

	$attachtypes = unserialize($datastore['data']);

	$attachment_name2 = strtolower($checkthis);
	$extension = file_extension($attachment_name2);
	$extensionok = false;
	if (!$attachtypes[$extension] OR !$attachtypes[$extension]['enabled'])
	{
		logging($extension . " extensions are not accepted, as currently set up in the control panel.");
	}
	else
	{
		$extensionok = true;
	}
	return $extensionok;
}

function finishattachments($postid, $threadid, $attaches)
{
	global $db;

	$db->query("UPDATE " . TABLE_PREFIX . "post
	SET attach = attach + $attaches WHERE postid = $postid");

	$db->query("UPDATE " . TABLE_PREFIX . "thread
	SET attach = attach + $attaches WHERE threadid = $threadid");

	logging("inserted " . $attaches . " attachment(s).");
}

//strips footer
function stripfooter($checkthis) //function written originally by KevinM
{
	global $nntp;
	static $stripthis;

	$nntp_settings = $nntp['settings'];

	$checkthis = trim($checkthis);

	if ($nntp_settings['strip_footer'])
	{
		if (empty($stripthis))
		{
			preg_match_all ( "/\{(.*?)\}/", $nntp_settings['strip_footer'], $stripthis);
			array_splice ($stripthis, 0, 1);
		}
		foreach ($stripthis[0] AS $stripphrase)
		{
			if ($stripphrase)
			{
				if (stristr($checkthis, $stripphrase))
				{
					$finish = strpos($checkthis, $stripphrase);
					$checkthis = substr($checkthis, 0, $finish);
					logging("Email footer match found, footer stripped.");
				}
			}
		}
	}
	return $checkthis;
}

function killfile_match()
{
	global $nntp;
	static $killfile;
	$killfile_match ='';

	$nntp_settings = $nntp['settings'];

	if ($nntp_settings['killfile_setting'])
	{
		if (empty($killfile)) //for the first time
		{
			preg_match_all ("/\{(.*?)\}/", $nntp_settings['killfile_setting'], $match);

			if (empty($match))
			{
				logging("Wrong format killfile");
				return $killfile_match;
			}
			$killfile = $match[1];
		}
		foreach ($killfile AS $eachkillfile)
		{
			//eachkillfile format is: "[H:text] AND [B:text1]" or "[H:text]"
			 preg_match_all ("/\[([BH]):(.*?)\]/", $eachkillfile, $killfile_array);

			$pattern_no = count($killfile_array[0]);
			//contains AND or only a single pattern.
			if ($pattern_no ==1)
			{
				if (findmatch($killfile_array))
				{
					$killfile_match = $killfile_array[0][0];
					return $killfile_match;
				}
			}
			elseif ($pattern_no > 1) //AND present
			{
				//If $match_no == $pattern_no, it means match found
				$match_no = 0;
				$killfile_match = '';
				for ($i = 0; $i < $pattern_no; $i++)
				{
					if (findmatch($killfile_array, $i))
					{
						$killfile_match .= $killfile_array[0][$i];
						$match_no++;
					}
				}
	                        if ($match_no == $pattern_no)
	                        {
	                               return $killfile_match;
	                        }

			}

		}
	}
	return $killfile_match;
}

function findmatch($killfile_array, $pos = 0)
{
	global $nntp;
	$findmatch = false;

	$message =& $nntp['message'];

	if ($killfile_array[1][$pos] == 'H')
	{
		foreach ($message AS $key => $value)
		{
			if ($key != 'text')
			{
				if (strstr($key, 'attchment') == false)
				{
					if (stristr($value, $killfile_array[2][$pos]))
					{
			                        $findmatch = true;
			                        return $findmatch;
					}
				}
			}
		}
	}
	elseif ($killfile_array[1][$pos] == 'B')
	{
		if (stristr($message['text'], $killfile_array[2][$pos]))
		{
			$findmatch = true;
			return $findmatch;
		}
	}
}


?>