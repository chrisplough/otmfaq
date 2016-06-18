<?php
/*======================================================================*\
|| ######################################################################## ||
|| # vBulletin  - Licence Number VBF98A5CB5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is ©2000-2006 Jelsoft Enterprises Ltd. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| ######################################################################## ||
\*======================================================================*/

// For auth
define('CUSTOMER_NUMBER', trim(strtoupper('J529F7D25D67')));
define('IDIR', (($getcwd = getcwd()) ? $getcwd : '.'));

if (function_exists('set_time_limit') AND get_cfg_var('safe_mode')==0)
{
	@set_time_limit(0);
}

ignore_user_abort(true);
error_reporting(E_ALL  & ~E_NOTICE);



// If there is no global file in the same dir, then we aren't in vB
// we are standalone, else assume we are.

if (file_exists('../includes/config.php'))
{
	if (file_exists('../admincp/language.php'))
	{
		// admin CP directory wasn't renamed
		// this bit allows people on 3.0.0 to do an import (if they haven't renamed their admin CP dir)
		chdir('../admincp/');
	}
	else
	{
		require('../includes/config.php');
		chdir("../$admincpdir/");
	}
	require_once('./global.php');

	$usewrapper = true;
}
else
{
	chdir('../'); // make sure our includes use the same paths
	$usewrapper = false;
}

require_once (IDIR . '/ImpExConfig.php'); 
require_once (IDIR . '/db_mysql.php');
require_once (IDIR . $impexconfig['system']['language']);

// #############################################################################
// Auth
// #############################################################################

$auth_redirect = 'help.php';
require_once (IDIR . '/impex_auth.php');

// #############################################################################
// Main page and dB connection
// #############################################################################

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en">
<head>
	<title>Import / Export - Help Page</title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<link rel="stylesheet" href="../cpstyles/vBulletin_3_Default/controlpanel.css" />
	<script type="text/javascript">var SESSIONHASH = "";</script>
	<script type="text/javascript" src="../clientscript/vbulletin_global.js"></script>

</head>
<body style="margin:0px" onload="set_cp_title();">
<div class="pagetitle">Import / Export - Help page</div>
<div style="margin:10px">

<h3>Impex Help page</h3>
<?php



if (is_file(IDIR . '/ImpExConfig.php'))
{
	include (IDIR . '/ImpExConfig.php');
	$impex_config = false;
	
	// They haven't set the target details 
	if ($impexconfig['target']['password'] != 'password')
	{
		$using_local_config = '<p>' . $impex_phrases['using_impex_config'] . '</p>';
	
		$targetdatabasetype = $impexconfig['target']['databasetype'];
		$targetserver 		= $impexconfig['target']['server'];
		$targetuser 		= $impexconfig['target']['user'];
		$targetpassword 	= $impexconfig['target']['password'];
		$targetdatabase 	= $impexconfig['target']['database'];
		$targettableprefix 	= $impexconfig['target']['tableprefix'];
		$impex_config = true;
	}
	else if (file_exists(IDIR . 'includes/config.php') AND !$impex_config)
	{
		include (IDIR . 'includes/config.php');
		
		$using_local_config = '<p>' . $impex_phrases['using_local_config'] . '</p>';
		
		$targetdatabasetype = $config['Database']['dbtype'] ? $config['Database']['dbtype'] : 'mysql';
		$targetserver 		= $config['MasterServer']['servername'];
		$targetuser 		= $config['MasterServer']['username'];
		$targetpassword 	= $config['MasterServer']['password'];
		$targetdatabase 	= $config['Database']['dbname'];
		$targettableprefix 	= $config['Database']['tableprefix'];
	}   
	else
	{
		die($impex_phrases['cant_read_config']);
	}
}
else
{
	// No config
	die("Can't find ImpExConfig.php");
}

$Db_target = new DB_Sql_vb_impex();
$Db_target->appname 		= 'vBulletin:ImpEx Target';
$Db_target->appshortname 	= 'vBulletin:ImpEx Target';
$Db_target->database 		= $targetdatabase;
$Db_target->type 			= $targetdatabasetype;
$Db_target->connect($targetserver, $targetuser, $targetpassword, 0);

$Db_target->select_db($targetdatabase);


if(empty($_GET['action']))
{

	echo $impex_phrases['action_1'];

	if ($using_local_config) { echo '<p>' . $impex_phrases['using_local_config'] . '</p>'; }
	
	echo $impex_phrases['action_2'];
	echo $impex_phrases['action_3'];
	echo $impex_phrases['action_4'];
	echo $impex_phrases['action_5'];
	echo $impex_phrases['action_6'];
}

if($_GET['action'] == 'delsess')
{
	echo $impex_phrases['dell_session_1'];
	echo $impex_phrases['dell_session_2'];
	echo $impex_phrases['dell_session_3'];
	echo $impex_phrases['dell_session_4'];
	
	$Db_target->query("DELETE FROM {$targettableprefix}datastore WHERE title='ImpExSession';");

	echo $impex_phrases['dell_session_5'];
	echo $impex_phrases['dell_session_6'];
}

if($_GET['action'] == 'delall')
{
	echo $impex_phrases['deleting_session'];
	$Db_target->query("DELETE FROM {$targettableprefix}datastore WHERE title='ImpExSession';");
	echo $impex_phrases['session_deleted'];


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

	foreach($fields as $array)
	{
		foreach($array as $tablename => $colname)
		{
			$is_it_there = $Db_target->query_first("DESCRIBE {$targettableprefix}{$tablename} {$colname}");

			if($is_it_there)
			{
				echo $impex_phrases['deleting_from'] . " {$targettableprefix}{$tablename} ....";
				flush();
				$Db_target->query("DELETE FROM {$targettableprefix}{$tablename} WHERE {$colname} <> 0");
				echo "...<b>{$impex_phrases['completed']}</b></p>";
				flush();
			}
		}
	}


	echo $impex_phrases['click_to_return'];
}


if($_GET['action'] == 'delids')
{
	echo $impex_phrases['deleting_session'];
	$Db_target->query("DELETE FROM {$targettableprefix}datastore WHERE title='ImpExSession';");
	echo $impex_phrases['session_deleted'];

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

	foreach($fields as $array)
	{
		foreach($array as $tablename => $colname)
		{
			$is_it_there = $Db_target->query_first("DESCRIBE {$targettableprefix}{$tablename} {$colname}");
			if($is_it_there)
			{
				echo "<p>{$impex_phrases['del_ids_1']} {$colname} {$impex_phrases['del_ids_2']} {$tablename} {$impex_phrases['del_ids_3']}";
				flush();
				$Db_target->query("UPDATE {$targettableprefix}{$tablename} SET {$colname}= 0 WHERE {$colname} <> 0");
				echo "...<b>{$impex_phrases['completed']}</b></p>";
				flush();
			}
		}
	}


	echo $impex_phrases['click_to_return'];
}

if($_GET['action'] == 'deldupe')
{
	echo $impex_phrases['deleting_duplicates'];
	/*
	// Users
	$user_sql = "SELECT MAX(userid) AS userid, COUNT(*) AS count FROM {$targettableprefix}user WHERE importuserid > 0 GROUP BY importuserid HAVING count > 1";
	$dupe_users = $Db_target->query($user_sql);

	while ($user = $Db_target->fetch_array($dupe_users))
	{
		$user_to_delete[] = $user['userid'];
	}
	
	$users_found = count($user_to_delete);
	if ($users_found)
	{
		$Db_target->query("DELETE FROM {$targettableprefix}user WHERE userid IN(" . implode(',', $user_to_delete) . ")");
	}
	*/
	// Forums
	$forum_sql = "SELECT MAX(forumid) AS forumid, COUNT(*) AS count FROM {$targettableprefix}forum WHERE importforumid > 0 GROUP BY importforumid HAVING count > 1";
	$dupe_forums = $Db_target->query($forum_sql);

	while ($forum = $Db_target->fetch_array($dupe_forums))
	{
		$forum_to_delete[] = $forum['forumid'];
	}
	
	$forums_found = count($forum_to_delete);
	if ($forums_found)
	{
		$Db_target->query("DELETE FROM {$targettableprefix}forum WHERE forumid IN(" . implode(',', $forum_to_delete) . ")");
	}
	
	// Threads
	$thread_sql = "SELECT MAX(threadid) AS threadid, COUNT(*) AS count FROM {$targettableprefix}thread WHERE importthreadid > 0 GROUP BY importthreadid HAVING count > 1";
	$dupe_threads = $Db_target->query($thread_sql);

	while ($thread = $Db_target->fetch_array($dupe_threads))
	{
		$thread_to_delete[] = $thread['threadid'];
	}
	
	$threads_found = count($thread_to_delete);
	if ($threads_found)
	{
		$Db_target->query("DELETE FROM {$targettableprefix}thread WHERE threadid IN(" . implode(',', $thread_to_delete) . ")");
	}		
	
	// Posts
	$post_sql = "SELECT MAX(postid) AS postid, COUNT(*) AS count FROM {$targettableprefix}post WHERE importpostid > 0 GROUP BY importpostid HAVING count > 1";
	$dupe_posts = $Db_target->query($post_sql);

	while ($post = $Db_target->fetch_array($dupe_posts))
	{
		$post_to_delete[] = $post['postid'];
	}
	
	$posts_found = count($post_to_delete);
	if ($posts_found)
	{
		$Db_target->query("DELETE FROM {$targettableprefix}post WHERE postid IN(" . implode(',', $post_to_delete) . ")");
	}		
	
	//echo "<br>{$impex_phrases['users']} :: {$users_found}";
	echo "<br>{$impex_phrases['forums']} :: {$forums_found}";
	echo "<br>{$impex_phrases['threads']} :: {$threads_found}";
	echo "<br>{$impex_phrases['posts']} :: {$posts_found}";
	
	echo "<br><br>...<b>{$impex_phrases['completed']}</b></p>";
	echo $impex_phrases['click_to_return'];
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: help.php,v $ - $Revision: 1.19 $
|| ####################################################################
\*======================================================================*/
?>
