<?php
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

if (!defined('IDIR')) { die; }

/* simply contains a function that defines how to populate target_db.vbfields */

function &retrieve_vbfields_queries($tableprefix = '')
{
	
	$queries = array();
	
	$queries[] = "DROP TABLE IF EXISTS {$tableprefix}vbfields";
	
	$queries[] = "CREATE TABLE `{$tableprefix}vbfields` (
	  `fieldname` varchar(50) NOT NULL default '',
	  `tablename` varchar(20) NOT NULL default '',
	  `vbmandatory` enum('Y','N','A') NOT NULL default 'N',
	  `defaultvalue` varchar(200) default '!##NULL##!',
	  `dictionary` mediumtext NOT NULL,
	  `product` varchar(25) default ''
	)
	";
	
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'administrator', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('adminpermissions', 'administrator', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('navprefs', 'administrator', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('cssprefs', 'administrator', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('attachmentid', 'attachment', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filename', 'attachment', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filedata', 'attachment', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('visible', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('counter', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filesize', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('postid', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filehash', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('posthash', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('thumbnail', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('thumbnail_dateline', 'attachment', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarid', 'avatar', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('minimumposts', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarpath', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('imagecategoryid', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displayorder', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filedata', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filename', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('visible', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumid', 'forum', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('styleid', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'forum', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('description', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('options', 'forum', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displayorder', 'forum', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('replycount', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastpost', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastposter', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastthread', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastthreadid', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lasticonid', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('threadcount', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('daysprune', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('newpostemail', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('newthreademail', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('parentid', 'forum', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('parentlist', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('password', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('link', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('childlist', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumpermissionid', 'forumpermission', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumid', 'forumpermission', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usergroupid', 'forumpermission', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumpermissions', 'forumpermission', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'forumread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumid', 'forumread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('readtime', 'forumread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('moderatorid', 'moderator', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'moderator', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumid', 'moderator', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('permissions', 'moderator', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmid', 'pm', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmtextid', 'pm', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'pm', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('folderid', 'pm', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('messageread', 'pm', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmtextid', 'pmtext', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('fromuserid', 'pmtext', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('fromusername', 'pmtext', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'pmtext', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('message', 'pmtext', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('touserarray', 'pmtext', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('iconid', 'pmtext', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'pmtext', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('showsignature', 'pmtext', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('allowsmilie', 'pmtext', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pollid', 'poll', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('question', 'poll', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'poll', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('options', 'poll', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('votes', 'poll', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('active', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('numberoptions', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('timeout', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('multiple', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('voters', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('public', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pollvoteid', 'pollvote', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pollid', 'pollvote', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'pollvote', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('votedate', 'pollvote', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('voteoption', 'pollvote', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('postid', 'post', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('threadid', 'post', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('parentid', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('username', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'post', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pagetext', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('allowsmilie', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('showsignature', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('ipaddress', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('iconid', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('visible', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('attach', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('profilefieldid', 'profilefield', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('description', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('required', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('hidden', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('maxlength', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('size', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displayorder', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('editable', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('type', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('data', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('height', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('def', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('optional', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('searchable', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('memberlist', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('regex', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('form', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('html', 'profilefield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('rankid', 'ranks', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('minposts', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('ranklevel', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('rankimg', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usergroupid', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('type', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('smilieid', 'smilie', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'smilie', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('smilietext', 'smilie', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('smiliepath', 'smilie', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('imagecategoryid', 'smilie', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displayorder', 'smilie', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('threadid', 'thread', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'thread', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('firstpostid', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastpost', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumid', 'thread', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pollid', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('open', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('replycount', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('postusername', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('postuserid', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastposter', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('views', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('iconid', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('notes', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('visible', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sticky', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('votenum', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('votetotal', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('attach', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('similar', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'user', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usergroupid', 'user', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('membergroupids', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displaygroupid', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('username', 'user', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('password', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('passworddate', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('email', 'user', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('styleid', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('parentemail', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('homepage', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('icq', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('aim', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('yahoo', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('showvbcode', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usertitle', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('customtitle', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('joindate', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('daysprune', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastvisit', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastactivity', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastpost', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('posts', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('reputation', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('reputationlevelid', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('timezoneoffset', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmpopup', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarid', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarrevision', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('options', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('birthday', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('maxposts', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('startofweek', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('ipaddress', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('referrerid', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('languageid', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('msn', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('emailstamp', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('threadedmode', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmtotal', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmunread', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('salt', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('autosubscribe', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usergroupid', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displaygroupid', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('adminid', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('bandate', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('liftdate', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('customtitle', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usertitle', 'userban', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('temp', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field1', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field2', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field3', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field4', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field8', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field9', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field10', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field11', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field12', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field13', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field46', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field49', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field50', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field51', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field57', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field58', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field59', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field60', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field61', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field62', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field63', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field64', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('field65', 'userfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usergroupid', 'usergroup', 'A', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('description', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usertitle', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('passwordexpires', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('passwordhistory', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmquota', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmsendmax', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmforwardmax', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('opentag', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('closetag', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('canoverride', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('ispublicgroup', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forumpermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmpermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('calendarpermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('wolpermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('adminpermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('genericpermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('genericoptions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmpermissions_bak', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('attachlimit', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarmaxwidth', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarmaxheight', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatarmaxsize', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('profilepicmaxwidth', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('profilepicmaxheight', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('profilepicmaxsize', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastvote', 'poll', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('display', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('stack', 'ranks', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('hiddencount', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('height', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('width', 'avatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('subfolders', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pmfolders', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('buddylist', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('ignorelist', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('signature', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('searchprefs', 'usertextfield', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('profilepicrevision', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('height', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('width', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importforumid', 'forum', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importcategoryid', 'forum', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('avatar', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importuserid', 'user', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importthreadid', 'thread', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importforumid', 'thread', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importthreadid', 'post', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importpollid', 'poll', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importsmilieid', 'smilie', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importrankid', 'ranks', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importusergroupid', 'usergroup', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('birthday_search', 'user', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importpmid', 'pmtext', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importavatarid', 'avatar', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filesize', 'customavatar', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importcustomavatarid', 'customavatar', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importmoderatorid', 'moderator', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importpostid', 'post', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importattachmentid', 'attachment', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importpmid', 'pm', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";	
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importcustomprofilepicid', 'customprofilepic', 'Y', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'customprofilepic', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filedata', 'customprofilepic', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'customprofilepic', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filename', 'customprofilepic', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('visible', 'customprofilepic', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('filesize', 'customprofilepic', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('title', 'imagecategory', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('imagetype', 'imagecategory', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displayorder', 'imagecategory', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('extension', 'attachment', 'N', '!##NULL##!','return true;', 'vbulletin')";
	
	// vB 3.6.0 additions
	# usergroup
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('signaturepermissions', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')"; 
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigpicmaxwidth', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigpicmaxheight', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigpicmaxsize', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigmaximages', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigmaxsizebbcode', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigmaxchars', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigmaxrawchars', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigmaxlines', 'usergroup', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	# user
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('usernote', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('sigpicrevision', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('ipoints', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('infractions', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('warnings', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('infractiongroupids', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('infractiongroupid', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('adminoptions', 'user', 'N', '!##NULL##!','return true;', 'vbulletin')";
	# forum
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('showprivate', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('lastpostid', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('defaultsortfield', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('defaultsortorder', 'forum', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	# thread
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('deletedcount', 'thread', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	# phrase
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importphraseid', 'phrase', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('varname', 'phrase', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('fieldname', 'phrase', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('text', 'phrase', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('product', 'phrase', 'N', 'vbulletin', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('languageid', 'phrase', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('username', 'phrase', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('dateline', 'phrase', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('version', 'phrase', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	# subscription
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importsubscriptionid', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('cost', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('membergroupids', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('active', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('options', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('varname', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('adminoptions', 'subscription', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('displayorder', 'subscription', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('forums', 'subscription', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('nusergroupid', 'subscription', 'N', '!##NULL##!', 'return true;', 'vbulletin')";
	# subscriptionlog
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('importsubscriptionlogid', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('subscriptionid', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('userid', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('pusergroupid', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";	
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('status', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('regdate', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	$queries[] = "INSERT INTO {$tableprefix}vbfields VALUES ('expirydate', 'subscriptionlog', 'Y', '!##NULL##!', 'return true;', 'vbulletin')";
	 
	
	return $queries;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: vbfields.php,v $ - $Revision: 1.25 $
|| ####################################################################
\*======================================================================*/
?>
