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
* vb3_000
*
* @package 		ImpEx.vb3
* @version		$Revision: 1.13 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/04/03 09:58:48 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
class vb3_000 extends ImpExModule
{
	/**
	* Class version
	*
	* This will allow the checking for interoprability of class version in diffrent
	* versions of ImpEx
	*
	* @var    string
	*/
	var $_version = '3.0.x -3.5.x';

	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'vBulletin';
	var $_homepage 	= 'http://www.vbulletin.com/';

	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array (
						'access', 'adminhelp', 'administrator', 'adminlog', 'adminutil', 'announcement', 'attachment',
						'attachmenttype', 'attachmentviews', 'avatar', 'bbcode', 'calendar', 'calendarcustomfield',
						'calendarmoderator', 'calendarpermission', 'cron', 'cronlog', 'customavatar', 'customprofilepic',
						'datastore', 'deletionlog', 'editlog', 'event', 'faq', 'forum', 'forumpermission', 'holiday', 'icon',
						'imagecategory', 'imagecategorypermission', 'language', 'mailqueue', 'moderation', 'moderator',
						'moderatorlog', 'passwordhistory', 'phrase', 'phrasetype', 'pm', 'pmreceipt', 'pmtext', 'poll',
						'pollvote', 'post', 'post_parsed', 'posthash', 'postindex', 'profilefield', 'ranks', 'regimage',
						'reminder', 'reputation', 'reputationlevel', 'search', 'session', 'setting', 'settinggroup',
						'smilie', 'stats', 'strikes', 'style', 'subscribeevent', 'subscribeforum', 'subscribethread',
						'subscription', 'subscriptionlog', 'template', 'thread', 'threadrate', 'threadviews', 'upgradelog',
						'user', 'useractivation', 'userban', 'userfield', 'usergroup', 'usergroupleader', 'usergrouprequest',
						'usernote', 'userpromotion', 'usertextfield', 'usertitle', 'vbfields', 'word', 'cpsession'
					);


	function vb3_000()
	{
	}



	/**
	* Returns the user_id => username array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_details(&$Db_object, &$databasetype, &$tableprefix, $start, $per_page, $type, $orderby = false)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mysql')
		{
			if(!$orderby)
			{
				$sql = "SELECT * FROM " . $tableprefix . $type;
			}
			else
			{
				$sql = "SELECT * FROM " . $tableprefix . $type . " ORDER BY " . $orderby;
			}

			if($per_page != -1)
			{
				$sql .= " LIMIT " . $start . "," . $per_page;
			}

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				if($orderby)
				{
					$return_array["$detail[$orderby]"] = $detail;
				}
				else
				{
					$return_array[] = $detail;
				}
			}
			return $return_array;
		}
		else
		{
			return false;
		}
	}

	function get_post_parent_id(&$Db_object, &$databasetype, &$tableprefix, $import_post_id)
	{
		if ($databasetype == 'mysql')
		{
			$sql = "SELECT postid FROM " . $tableprefix . "post WHERE importpostid =" . $import_post_id;

			$post_id = $Db_object->query_first($sql);

			return $post_id[0];
		}
		else
		{
			return false;
		}
	}

	function get_thread_id_from_poll_id(&$Db_object, &$databasetype, &$tableprefix, $poll_id)
	{
		if ($databasetype == 'mysql')
		{
			$sql = "SELECT importthreadid FROM " . $tableprefix . "thread WHERE pollid =" . $poll_id;

			$thread_id = $Db_object->query_first($sql);

			return $thread_id[0];
		}
		else
		{
			return false;
		}
	}

	function update_poll_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		if ($databasetype == 'mysql')
		{
			$result = $Db_object->query("SELECT pollid, threadid, importthreadid FROM " . $tableprefix . "thread WHERE open=10 AND pollid <> 0 AND importthreadid <> 0");

			while ($thread = $Db_object->fetch_array($result))
			{
				$new_thread_id = $Db_object->query_first("SELECT threadid FROM " . $tableprefix . "thread where importthreadid = ".$thread['pollid']);

				if($new_thread_id['threadid'])
				{
					// Got it
					$Db_object->query("UPDATE " . $tableprefix . "thread SET pollid =" . $new_thread_id['threadid'] . " WHERE threadid=".$thread['threadid']);
				}
				else
				{
					// Why does it miss some ????
				}
			}
		}
		else
		{
			return false;
		}
	}

	function get_vb3_pms(&$Db_object, &$databasetype, &$tableprefix, &$pm_text_id)
	{
		$return_array = array();

		if ($databasetype == 'mysql')
		{
			$result = $Db_object->query("SELECT * FROM " . $tableprefix . "pm WHERE pmtextid=". $pm_text_id);

			while ($pm = $Db_object->fetch_array($result))
			{
				$return_array["$pm[pmid]"] = $pm;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}

}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.13 $
|| ####################################################################
\*======================================================================*/
?>
