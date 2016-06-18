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
* fudforum API module
*
* @package			ImpEx.fudforum
* @version			$Revision: 1.4 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/04/03 08:46:42 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class fudforum_000 extends ImpExModule
{
	/**
	* Class version
	*
	* This is the version of the source system that is supported
	*
	* @var    string
	*/
	var $_version = '2.x';


	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'FUD Forum';
	var $_homepage 	= 'http://www.fudforum.org/';


	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array (
		'action_log', 'ann_forums', 'announce', 'attach', 'avatar', 'blocked_logins', 'buddy', 'cat', 'custom_tags', 'email_block',
		'ext_block', 'fc_view', 'forum', 'forum_notify', 'forum_read', 'group_cache', 'group_members', 'group_resources', 'groups',
		'index', 'ip_block', 'level', 'mime', 'mlist', 'mod', 'mod_que', 'msg', 'msg_report', 'nntp', 'pmsg', 'poll', 'poll_opt',
		'poll_opt_track', 'read', 'replace', 'search', 'search_cache', 'ses', 'smiley', 'stats_cache', 'themes', 'thr_exchange', 'thread',
		'thread_notify', 'thread_rate_track', 'thread_view', 'title_index', 'tmp_consist', 'user_ignore', 'users'
	);


	function fudforum_000()
	{
	}


	/**
	* Parses and custom HTML for fudforum
	*
	* @param	string	mixed			The text to be parse
	*
	* @return	array
	*/
	function fudforum_html($text)
	{
		$text = preg_replace('#\<table border=(.*)class="SmallText">\[b\](.*) wrote on (.*)</td></tr><tr><td class="quote">(.*)</td></tr></table>#siU', '[quote=$2]$4[/quote]', $text);
		
		return $text;
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
	function get_fudforum_members_list(&$Db_object, &$databasetype, &$tableprefix, &$start, &$per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT id, login
			FROM " . $tableprefix . "users
			ORDER BY user_id
			LIMIT " . $start . "," . $per_page;

			$user_list = $Db_object->query($sql);

			while ($user = $Db_object->fetch_array($user_list))
			{
				$return_array["$user[id]"] = $user['login'];
			}
			return $return_array;
		}
		else
		{
			return false;
		}
	}

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
	
	function get_thread_title(&$Db_object, &$databasetype, &$tableprefix, $root_msg_id)
	{
		// Check that there is not a empty value
		if(empty($root_msg_id)) { return 'empty'; }

		if ($databasetype == 'mysql')
		{
			$sql = "SELECT subject, post_stamp FROM " . $tableprefix . "msg WHERE id={$root_msg_id}";

			$subject = $Db_object->query_first($sql);

			return $subject;
		}
		else
		{
			return false;
		}	
	}
	
	function get_poll_options(&$Db_object, &$databasetype, &$tableprefix, $poll_id)
	{
		// Check that there is not a empty value
		if(empty($poll_id)) { return false; }

		if ($databasetype == 'mysql')
		{
			$sql = "SELECT * FROM " . $tableprefix . "poll_opt WHERE poll_id=" . $poll_id;

			$poll_opts = $Db_object->query($sql);

			while ($poll_opt = $Db_object->fetch_array($poll_opts))
			{
				$return_array["$poll_opt[id]"] = $poll_opt;
			}
			
			return $return_array;
		}
		else
		{
			return false;
		}	
	}	
	
	function get_vote_voters(&$Db_object, &$databasetype, &$tableprefix, $poll_id)
	{
		// Check that there is not a empty value
		if(empty($poll_id)) { return false; }

		if ($databasetype == 'mysql')
		{
			$sql = "SELECT * FROM " . $tableprefix . "poll_opt_track WHERE poll_id=" . $poll_id;

			$poll_opts = $Db_object->query($sql);

			while ($poll_opt = $Db_object->fetch_array($poll_opts))
			{
				$return_array["$poll_opt[id]"] = $poll_opt;
			}
			
			return $return_array;
		}
		else
		{
			return false;
		}	
	}
	
	function get_threadid_for_poll(&$Db_object, &$databasetype, &$tableprefix, $poll_id)
	{
		// Check that there is not a empty value
		if(empty($poll_id)) { return false; }

		if ($databasetype == 'mysql')
		{
			$sql = "SELECT thread_id FROM " . $tableprefix . "msg WHERE poll_id=" . $poll_id;

			$thread_id = $Db_object->query_first($sql);

			return $thread_id['thread_id'];
		}
		else
		{
			return false;
		}				
	}
	
	function get_post($file, &$offset, $lenght)
	{
		if(!$lenght)
		{
			return false;
		}

		$fp = fopen($file, 'rb');
		fseek($fp, $offset);

		return fread($fp, $lenght);
	}

} // Class end
# Autogenerated on : July 5, 2005, 3:53 pm
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.4 $
|| ####################################################################
\*======================================================================*/
?>
