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
* InstantForum API module
*
* @package			ImpEx.InstantForum
* @version			$Revision: 1.4 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/07/15 03:00:21 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class InstantForum_000 extends ImpExModule
{
	/**
	* Class version
	*
	* This is the version of the source system that is supported
	*
	* @var    string
	*/
	var $_version = '4.1';


	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'InstantForum ';
	var $_homepage 	= 'http://www.instantasp.co.uk/products/instantforum/default.aspx';


	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array (
		'Buddies', 'ForumGroups', 'ForumGroupsMemberGroups', 'ForumMemberGroups', 'Forums', 'ForumSubscriptions', 'HoldEmailReplies',
		'MemberActivity', 'MemberGroups', 'Members', 'MessageAttachments', 'Messages', 'Moderators', 'PrivateMessages', 'TimeZones',
		'TopicRatings'
	);


	function InstantForum_000()
	{
	}


	/**
	* Parses and custom HTML for InstantForum
	*
	* @param	string	mixed			The text to be parse
	*
	* @return	array
	*/
	function InstantForum_html($text)
	{
		// Font
		$text = preg_replace('#\<font(.*)\>#siU', '', $text);
		
		$text = str_replace('</P>', '', $text);
		$text = str_replace('</FONT>', '', $text);
		
		
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
	function get_InstantForum_members_list(&$Db_object, &$databasetype, &$tableprefix, &$start, &$per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Members");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	MemberID,
							Username
					FROM {$tableprefix}Members WHERE MemberID
						IN(SELECT TOP {$per_page} MemberID
							FROM (SELECT TOP {$internal} MemberID FROM {$tableprefix}Members ORDER BY MemberID)
						A ORDER BY MemberID DESC)
					ORDER BY MemberID";

			$user_list = $Db_object->query($sql);


			while ($user = $Db_object->fetch_array($user_list))
			{
				$return_array["$user[MemberID]"] = $user['Username'];
			}

			return $return_array;
		}
		else
		{
			return false;
		}
	}
	/**
	* Returns the attachment_id => attachment array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_InstantForum_attachment_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}MessageAttachments");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	AttachmentID,
							MessageID,
							FileName,
							AttachmentBLOB,
							ContentLength,
							MimeType
					FROM {$tableprefix}MessageAttachments WHERE AttachmentID
						IN(SELECT TOP {$per_page} AttachmentID
							FROM (SELECT TOP {$internal} AttachmentID FROM {$tableprefix}MessageAttachments ORDER BY AttachmentID)
						A ORDER BY AttachmentID DESC)
					ORDER BY AttachmentID";

			$details_list = $Db_object->query($sql);


			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[AttachmentID]"] = $detail;
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
	function get_InstantForum_forum_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
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
							GroupID,
							ForumName,
							ForumDescription,
							CreatedDate,
							ForumSortOrder,
							DefaultSortBy,
							DefaultSortOrder,
							DefaultDateRange,
							AllowPosts,
							AllowReplies,
							PreviewPosts,
							AutoApproveNum,
							NumberOfTopics,
							NumberOfReplies
					FROM {$tableprefix}Forums WHERE ForumID
						IN(SELECT TOP {$per_page} ForumID
							FROM (SELECT TOP {$internal} ForumID FROM {$tableprefix}Forums ORDER BY ForumID)
						A ORDER BY ForumID DESC)
					ORDER BY ForumID";

			$details_list = $Db_object->query($sql);


			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[ForumID]"] = $detail;
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
	* Returns the pm_id => pm array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_InstantForum_pm_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}PrivateMessages");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	PrivateMessageID,
							SenderMemberID,
							RecipientMemberID,
							Subject,
							CAST([Message] as TEXT) as Message,
							PostedDate,
							ReadFlag,
							MessageIcon
					FROM {$tableprefix}PrivateMessages WHERE PrivateMessageID
						IN(SELECT TOP {$per_page} PrivateMessageID
							FROM (SELECT TOP {$internal} PrivateMessageID FROM {$tableprefix}PrivateMessages ORDER BY PrivateMessageID)
						A ORDER BY PrivateMessageID DESC)
					ORDER BY PrivateMessageID";

			$details_list = $Db_object->query($sql);


			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[PrivateMessageID]"] = $detail;
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
	function get_InstantForum_post_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Messages");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	MessageID,
							ForumId,
							Subject,
							CAST([Message] as TEXT) as Message,
							PostedDate,
							LastPost,
							NoOfReplies,
							Views,
							UserID,
							LastUserID,
							TopicID,
							IsTopic,
							Approved,
							PinnedPost,
							MovedPost,
							IPAddress,
							MessageIcon
					FROM {$tableprefix}Messages WHERE MessageID
						IN(SELECT TOP {$per_page} MessageID
							FROM (SELECT TOP {$internal} MessageID FROM {$tableprefix}Messages ORDER BY MessageID)
						A ORDER BY MessageID DESC)
					ORDER BY MessageID";

			$details_list = $Db_object->query($sql);


			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[MessageID]"] = $detail;
			}

			return $return_array;
		}
		else
		{
			return false;
		}
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
	function get_InstantForum_thread_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Messages");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	MessageID,
							ForumID,
							Subject,
							CAST([Message] as TEXT) as Message,
							PostedDate,
							LastPost,
							NoOfReplies,
							Views,
							UserID,
							LastUserID,
							TopicID,
							IsTopic,
							Approved,
							PinnedPost,
							MovedPost,
							IPAddress,
							MessageIcon
					FROM {$tableprefix}Messages WHERE IsTopic = 1
					ORDER BY MessageID";

			$details_list = $Db_object->query($sql);
					
			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[MessageID]"] = $detail;
			}

			return $return_array;
		}
		else
		{
			return false;
		}
		return $return_array;
	}

	function get_InstantForum_categories_details(&$Db_object, &$databasetype, &$tableprefix)
	{
		$return_array = array();


		if ($databasetype == 'mssql')
		{
			$sql = "SELECT 	GroupID,
							GroupName,
							GroupOrder
					FROM {$tableprefix}ForumGroups
				";

			$details_list = $Db_object->query($sql);


			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[GroupID]"] = $detail;
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
	function get_InstantForum_user_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}Members");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	MemberID,
							Administrator,
							Moderator,
							FullName,
							Username,
							Password,
							URL,
							Location,
							Occupation,
							Interests,
							EmailAddress,
							MSNIM
							YahooIM,
							ICQ,
							AIM,
							NoOfPosts,
							JoinDate,
							Signature,
							Biography,
							LastLoginDate,
							AvatarURL,
							PhotoURL,
							UserStatus,
							IPAddress,
							Banned,
							IPBanned,
							GroupID,
							ForumSkin,
							TimeZoneOffSet,
							RTBType,
							ActiveAccount
							ConfirmationCode
					FROM {$tableprefix}Members WHERE MemberID
						IN(SELECT TOP {$per_page} MemberID
							FROM (SELECT TOP {$internal} MemberID FROM {$tableprefix}Members ORDER BY MemberID)
						A ORDER BY MemberID DESC)
					ORDER BY MemberID";

			$user_list = $Db_object->query($sql);


			while ($user = $Db_object->fetch_array($user_list))
			{
				
				$return_array["$user[MemberID]"] = $user;
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
	* Returns the usergroup_id => usergroup array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_InstantForum_usergroup_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mssql')
		{
			$count = $Db_object->query_first("SELECT count(*) FROM {$tableprefix}MemberGroups");

			$internal 	= $start_at + $per_page;

			if($internal > intval($count[0]))
			{
				$per_page = abs($start_at - intval($count[0]));
				$internal = intval($count[0]);
			}

			$sql = "SELECT 	MemberGroupID,
							MemberGroupName
					FROM {$tableprefix}MemberGroups WHERE MemberGroupID
						IN(SELECT TOP {$per_page} MemberGroupID
							FROM (SELECT TOP {$internal} MemberGroupID FROM {$tableprefix}MemberGroups ORDER BY MemberGroupID)
						A ORDER BY MemberGroupID DESC)
					ORDER BY MemberGroupID";

			$user_list = $Db_object->query($sql);


			while ($user = $Db_object->fetch_array($user_list))
			{
				$return_array["$user[MemberGroupID]"] = $user;
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
# Autogenerated on : February 12, 2006, 4:05 pm
# By ImpEx-generator 2.1.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.4 $
|| ####################################################################
\*======================================================================*/
?>
