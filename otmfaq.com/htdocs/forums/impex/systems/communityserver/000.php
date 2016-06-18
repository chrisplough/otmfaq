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
/**
* communityserver API module
*
* @package			ImpEx.communityserver
* @version			$Revision: 1.1 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/07/19 23:01:31 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class communityserver_000 extends ImpExModule
{
	/**
	* Class version
	*
	* This is the version of the source system that is supported
	*
	* @var    string
	*/
	var $_version = '2.0';


	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'Community Server';
	var $_homepage 	= 'http://communityserver.org/';


	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array (
		'AnonymousUsers' , 'Emails' , 'ForumGroups' , 'Forums' , 'ForumsRead' , 'Messages' , 'ModerationAction' , 'ModerationAudit' , 'Moderators' , 'Post_Archive' , 
		'Posts' , 'PostsRead' , 'PrivateForums' , 'ThreadTrackings' , 'UserGTSRequest' , 'UserRoles' , 'Users' , 'UsersInRoles' , 'Vote'
	);

	function communityserver_000()
	{
	}


	/**
	* Parses and custom HTML for communityserver
	*
	* @param	string	mixed			The text to be parse
	*
	* @return	array
	*/
	function communityserver_html($text)
	{
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
	function get_communityserver_members_list(&$Db_object, &$databasetype, &$tableprefix, &$start_at, &$per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Users");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	UserId,
							UserName
					FROM {$tableprefix}Users WHERE UserId
						IN(SELECT TOP {$per_page} UserId
							FROM (SELECT TOP {$internal} UserId FROM {$tableprefix}Users ORDER BY UserId)
						A ORDER BY UserId DESC)
					ORDER BY UserId";

			$user_list = $Db_object->query($sql);

			while ($user = $Db_object->fetch_array($user_list))
			{
					$return_array["$user[UserId]"] = $user['UserName'];
			}

			return $return_array;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	* Returns the forum_id => forum array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_communityserver_forum_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Forums");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}
			
			$sql = "SELECT 	ForumID,
							ParentID,
							Name,
							Description,
							DateCreated,
							SortOrder,
							TotalPosts,
							TotalThreads
					FROM {$tableprefix}Forums WHERE ForumID
						IN(SELECT TOP {$per_page} ForumID
							FROM (SELECT TOP {$internal} ForumID FROM {$tableprefix}Forums ORDER BY ForumID)
						A ORDER BY ForumID DESC)
					ORDER BY ForumID";
			
			$forum_list = $Db_object->query($sql);

			while ($forum = $Db_object->fetch_array($forum_list))
			{
				$return_array["$forum[ForumID]"] = $forum;
			}

			return $return_array;
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the post_id => post array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_communityserver_post_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Posts");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}
			
			$sql = "SELECT 	PostID,
							ThreadID,
							ParentID,
							SortOrder,
							Subject,
							PostDate,
							ForumID,
							UserName,
							ThreadDate,
							CAST([Body] as TEXT) as Body
					FROM {$tableprefix}Posts WHERE PostID
						IN(SELECT TOP {$per_page} PostID
							FROM (SELECT TOP {$internal} PostID FROM {$tableprefix}Posts ORDER BY PostID)
						A ORDER BY PostID DESC)
					ORDER BY PostID";
			
			$post_list = $Db_object->query($sql);
			
			while ($post = $Db_object->fetch_array($post_list))
			{
				
				$return_array["$post[PostID]"] = $post;
			}

			return $return_array;
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the thread_id => thread array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_communityserver_thread_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) AS count FROM {$tableprefix}Posts WHERE (PostLevel = 1)");

			$internal = $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}
			
			$sql = "SELECT 	PostID,
							ThreadID,
							ParentID,
							Subject,
							ForumID,
							TotalViews,
							ThreadDate,
							IsLocked,
							IsPinned
					FROM {$tableprefix}Posts WHERE PostLevel = 1
					AND PostID
						IN(SELECT TOP {$per_page} PostID
							FROM (SELECT TOP {$internal} PostID FROM {$tableprefix}Posts WHERE PostLevel = 1 ORDER BY PostID) 
						A ORDER BY PostID DESC)
					ORDER BY PostID";
			
			$thread_list = $Db_object->query($sql);

			while ($thread = $Db_object->fetch_array($thread_list))
			{
				$return_array["$thread[PostID]"] = $thread;
			}

			return $return_array;
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the user_id => user array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_communityserver_user_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Users");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}
			
			$sql = "SELECT 	UserName,
							UserId,
							Password,
							Email,
							URL,
							Signature,
							DateCreated,
							LastLogin,
							LastActivity,
							Location,
							Occupation,
							Interests,
							MSN,
							Yahoo,
							AIM,
							ICQ,
							TotalPosts
					FROM {$tableprefix}Users WHERE UserId
						IN(SELECT TOP {$per_page} UserId
							FROM (SELECT TOP {$internal} UserId FROM {$tableprefix}Users ORDER BY UserId)
						A ORDER BY UserId DESC)
					ORDER BY UserId";
			
			$user_list = $Db_object->query($sql);

			while ($user = $Db_object->fetch_array($user_list))
			{
				$return_array["$user[UserId]"] = $user;
			}

			return $return_array;
		}
		else
		{
			return false;
		}
		return $return_array;
	}
} // Class end
# Autogenerated on : July 19, 2006, 11:33 am
# By ImpEx-generator 2.1.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.1 $
|| ####################################################################
\*======================================================================*/
?>
