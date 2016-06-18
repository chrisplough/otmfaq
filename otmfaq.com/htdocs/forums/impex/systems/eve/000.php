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
* eve API module
*
* @package			ImpEx.eve
* @version			$Revision: 1.42 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/08/28 19:31:55 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class eve_000 extends ImpExModule
{
	/**
	* Class version
	*
	* This is the version of the source system that is supported
	*
	* @var    string
	*/
	var $_version = '1.2.6 - 4.0.3';


	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'Infopop Groupee 1.2.6 (a.k.a Eve) (Forum UBB.x 4.0.3)';
	var $_homepage 	= 'http://www.infopop.com/eve_platform/';


	/**
	* Valid Database Tables
	*                                                                                                         
	* @var    array
	*/
	var $_valid_tables = array (
		'CACHE', 'CONTENT_ISLANDS', 'CURRENT_DB_VERSION', 'C_CATEGORIES', 'C_ITEMS', 'C_REVISIONS', 'C_REVISION_LOGS', 'C_STAGING',
		'C_TAGS', 'C_TAG_TO_REV_JOIN', 'DATABASE_INFO', 'FORUMS', 'GROUP_TOPICS', 'GROUP_TOPIC_EVENTS', 'GROUP_TOPIC_PARTICIPANTS',
		'IP_ALBUMS', 'IP_ALBUM_IMAGES', 'IP_ARCHIVED_TOPICS', 'IP_A_ALBUM', 'IP_A_ALBUM_IMAGE', 'IP_BANNED_USERS', 'IP_CAT_CATEGORY',
		'IP_CAT_RESOURCE_CATEGORY', 'IP_CHAT_TOPICS', 'IP_CHAT_TOPIC_MESSAGES', 'IP_CHAT_TOPIC_UNMOD_QUEUE', 'IP_CLONE_SOURCE_AND_DEST',
		'IP_CONTENT_SUBSCRIPTIONS', 'IP_CUSTOM_CODES', 'IP_CUSTOM_PROFILE_FIELDS', 'IP_C_CONTENT', 'IP_C_CONTENT_ISLAND',
		'IP_C_CONTENT_LOOKUP', 'IP_C_CONTENT_REPORT', 'IP_C_CONTENT_TYPE', 'IP_C_EXTENDED_DATA', 'IP_C_GUEST_USER', 'IP_C_MODERATED_CONTENT',
		'IP_C_RATED_CONTENT', 'IP_C_UPLOAD', 'IP_C_USER_FAVORITE', 'IP_C_USER_INTEREST_LOG', 'IP_DISPLAY_OPTIONS', 'IP_DISPLAY_RESOURCES',
		'IP_DISPLAY_RES_ASSOCIATIONS', 'IP_EVENT_LOG', 'IP_F_FORUM', 'IP_F_FORUM_ACCEPTED_POST_TYPE', 'IP_F_FORUM_ATTACHMENT_RULE',
		'IP_F_FORUM_MOD_CONTENT_TYPE', 'IP_F_FORUM_STATS', 'IP_F_FORUM_TOPIC', 'IP_GROUPS', 'IP_GROUP_USERS', 'IP_HIGHLIGHTED_TOPICS',
		'IP_IGNORED_USERS', 'IP_IM_ACTIVE_MENUS', 'IP_IM_PARTICIPANTS', 'IP_KARMA_LEVELS', 'IP_MESSAGE_ALERTS',
		'IP_PERMISSIONS', 'IP_PREMIUM_GROUP_SETTINGS', 'IP_PRIVATE_WEB_IDIRECTORIES', 'IP_PROFILES', 'IP_PT_PRIVATE_TOPIC',
		'IP_PT_PRIVATE_TOPIC_PARTICIPANT', 'IP_P_POLL', 'IP_P_POLL_ANSWER', 'IP_P_POLL_QUESTION', 'IP_P_POLL_RESPONSE', 'IP_RESOURCES',
		'IP_RIGHTS', 'IP_RW_LOCKS2', 'IP_SESSION_ACTIVITY', 'IP_SETTINGS', 'IP_SS_IMAGE_DIMENSIONS', 'IP_STATISTICS', 'IP_STREET_ADDRESSES',
		'IP_STYLE_SETS', 'IP_STYLE_SETTINGS', 'IP_TEMPLATE_FILES', 'IP_TEMPLATE_PAGE_TYPES', 'IP_TEMPLATE_SETS', 'IP_TEMPLATE_SET_USAGE',
		'IP_TEMPLATE_SUPPORT_INFO', 'IP_TOPICS', 'IP_T_ARCHIVED_MESSAGE', 'IP_T_ARCHIVED_TOPIC', 'IP_T_MESSAGE', 'IP_T_TOPIC', 'IP_T_TOPIC_STATS',
		'IP_UNCONFIRMED_USERS', 'IP_USERS', 'IP_USER_CONTACT_INFO', 'IP_USER_FAVORITES', 'IP_USER_RESOURCE_INFO', 'IP_WORDLETS',
		'IP_WORDLET_SETS', 'POLLS',   'UNMOD_MESSAGE_QUEUE', 'UPLOADS', 'URL_LOOKUP', 'USER_INFO', 
		'IP_C_SEARCH_EVENT','IP_T_MESSAGE_TO_DELETE', 'IP_USER_STATS'
	);



	function eve_000()
	{
	}


	/**
	* Parses and custom HTML for eve
	*
	* @param	string	mixed			The text to be parse
	*
	* @return	array
	*/
	function eve_html($text)
	{
		$text = preg_replace('#<BLOCKQUOTE(.*)-content">(.*)</div></BLOCKQUOTE>#siU', '[quote]$2[/quote]', $text);
		
		$text = str_replace('&gt;', '>', $text);
		$text = str_replace('&lt;', '<', $text);
		$text = str_replace('&quot;', '"', $text);
		$text = str_replace('&amp;', '&', $text);
		
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
	function get_eve_members_list(&$Db_object, &$databasetype, &$tableprefix, &$start, &$per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT USER_OID, LOGIN
			FROM " . $tableprefix . "IP_USERS
			ORDER BY USER_OID
			LIMIT " . $start . "," . $per_page;

			$user_list = $Db_object->query($sql);

			while ($user = $Db_object->fetch_array($user_list))
			{
					$return_array["$user[USER_OID]"] = $user['LOGIN'];
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
	function get_eve_attachment_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "SELECT * FROM " . $tableprefix . "IP_C_UPLOAD
					ORDER BY UPLOAD_OID 
					LIMIT " .
					$start_at .
					"," .
					$per_page
					;
					
			$attachments = $Db_object->query($sql);
			
			while ($attachment = $Db_object->fetch_array($attachments))
			{
				$return_array["$attachment[UPLOAD_OID]"] = $attachment;
				$extra = $Db_object->query_first("SELECT RELATED_CONTENT_OID FROM " . $tableprefix . "IP_C_CONTENT WHERE CONTENT_OID=" . $attachment['UPLOAD_OID']);
				$return_array["$attachment[UPLOAD_OID]"]['importpostid'] = $extra['RELATED_CONTENT_OID']; 
			}
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
	function get_eve_forum_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$forums = $Db_object->query("SELECT * FROM {$table_prefix}IP_F_FORUM");			

			while ($forum = $Db_object->fetch_array($forums))
			{
				$return_array[] = $forum;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the pmtext_id => pmtext array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_eve_pmtext_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mysql')
		{
			#Get the forum id
			$sql = "SELECT * FROM " . $table_prefix . "FORUMS
					WHERE FORUM_NAME = 'Private Messaging Forum'";

			$pm_forum_id = $Db_object->query_first($sql);

			$sql = "SELECT * FROM " . $table_prefix . "IP_TOPICS
					WHERE FORUM_OID = '" . $pm_forum_id['FORUM_OID'] . "'
					ORDER BY TOPIC_OID 
					LIMIT " .
					$start_at . "," . $per_page
					;

			$pms = $Db_object->query($sql);

			while ($pm = $Db_object->fetch_array($pms))
			{
				$extra = $Db_object->query_first("SELECT BODY FROM " . $tableprefix . "IP_T_MESSAGE WHERE MESSAGE_OID=" . $pm['TOPIC_OID']);
				$extra2 = $Db_object->query_first("SELECT * FROM " . $tableprefix . "IP_C_CONTENT WHERE CONTENT_OID=" . $pm['TOPIC_OID']);
				
				$return_array["$pm[TOPIC_OID]"] 				= $pm;
				$return_array["$pm[TOPIC_OID]"]['BODY'] 		= $extra['BODY'];
				$return_array["$pm[TOPIC_OID]"]['AUTHOR_OID'] 	= $extra2['AUTHOR_OID'];
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the poll_id => poll array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_eve_poll_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($per_page)) { return $return_array; }
	
		if ($databasetype == 'mysql')
		{
			$sql = "SELECT * FROM {$tableprefix}IP_P_POLL_QUESTION ORDER BY POLL_QUESTION_OID LIMIT {$start_at}, {$per_page}";

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$choices = array();
				$votes = array();
				$poll_voters = array();
				
				// Options
				$options = $Db_object->query("SELECT * FROM " . $tableprefix . "IP_P_POLL_ANSWER WHERE POLL_QUESTION_OID=" . $detail['POLL_QUESTION_OID'] . " ORDER BY ANSWER_POSITION");
				
				while ($row = $Db_object->fetch_array($options))
				{
					$choices[]	= $row['ANSWER_TEXT'];
					$votes[]	= $row['MEMBER_VOTE_COUNT']; // Not counting guest votes GUEST_VOTE_COUNT
					$answers[] 	= $row['POLL_ANSWER_OID'];
				}
				
				// Voters
				$voters_choice = $Db_object->query("SELECT USER_OID FROM " . $tableprefix . "IP_P_POLL_RESPONSE WHERE POLL_ANSWER_OID IN (" .  implode(',', $answers) . ")");

				while ($choice = $Db_object->fetch_array($voters_choice))
				{
					$poll_voters["$choice[USER_OID]"] = '0'; // Not worth the effort to record their actual choice and a reverse lookup 
				}					
				
				// Thread ID
				$threadid = $Db_object->query_first("SELECT TOPIC_OID AS threadid FROM " . $tableprefix . "IP_T_TOPIC WHERE PRIMARY_TOPIC_CONTENT_OID=" . $detail['POLL_OID']);
				
				if ($threadid)
				{
					$return_array["$detail[POLL_QUESTION_OID]"]['threadid'] = $threadid['threadid'];
				}
				else
				{
					$threadid['threadid'] = NULL; // Let's hope we aren't here any time soon
				}
		
				$return_array["$detail[POLL_QUESTION_OID]"]['numberoptions'] = count($choices);
				$return_array["$detail[POLL_QUESTION_OID]"]['voters'] = array_sum($votes);
				$return_array["$detail[POLL_QUESTION_OID]"]['options'] = implode('|||', $choices);
				$return_array["$detail[POLL_QUESTION_OID]"]['votes'] = implode('|||', $votes);
				$return_array["$detail[POLL_QUESTION_OID]"]['poll_voters'] = $poll_voters;
				$return_array["$detail[POLL_QUESTION_OID]"]['question'] = substr($this->html_2_bb($detail['QUESTION']), 0, 99);				
			}
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
	function get_eve_post_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."IP_T_MESSAGE
			ORDER BY MESSAGE_OID
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$i++;
				
				$extra = $Db_object->query_first("SELECT * FROM " . $tableprefix . "IP_C_CONTENT WHERE CONTENT_OID=" . $detail['MESSAGE_OID']);
				
				if (count($extra))
				{
					$return_array[$i] 						= $detail;
					$return_array[$i]['MESSAGE_OID']		= $detail['MESSAGE_OID'];
					$return_array[$i]['AUTHOR_OID']			= $extra['AUTHOR_OID'];
					$return_array[$i]['HAS_UPLOAD']			= $extra['HAS_UPLOAD'];
					$return_array[$i]['DATETIME_CREATED']	= $extra['DATETIME_CREATED'];
					$return_array[$i]['POSTER_IP']			= $extra['POSTER_IP'];
				}
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}

	
	
	function get_eve_archive_post_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."IP_T_ARCHIVED_MESSAGE
			ORDER BY MESSAGE_OID
			LIMIT " . $start_at . "," . $per_page;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$i++;
				
				$return_array["$detail[MESSAGE_OID]"] 	= $detail;
				/*
				$return_array[$i]['MESSAGE_OID']		= $detail['MESSAGE_OID'];
				$return_array[$i]['AUTHOR_OID']			= $extra['AUTHOR_OID'];
				$return_array[$i]['HAS_UPLOAD']			= $extra['HAS_UPLOAD'];
				$return_array[$i]['DATETIME_CREATED']	= $extra['DATETIME_CREATED'];
				$return_array[$i]['POSTER_IP']			= $extra['POSTER_IP'];
				*/
			}
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
	function get_eve_thread_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "SELECT * FROM " . $tableprefix . "IP_F_FORUM_TOPIC
			ORDER BY TOPIC_OID LIMIT " . $start_at . "," .	$per_page;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$i++;
				
				$extra = $Db_object->query_first("SELECT * FROM " . $tableprefix . "IP_T_TOPIC WHERE TOPIC_OID=" . $detail['TOPIC_OID']);

				$return_array[$i]								= $detail;
				$return_array[$i]['TOPIC_OID']					= $detail['TOPIC_OID'];
				$return_array[$i]['SUBJECT'] 					= $extra['SUBJECT'];
				$return_array[$i]['IS_TOPIC_CLOSED'] 			= $extra['IS_TOPIC_CLOSED'];
				$return_array[$i]['IS_TOPIC_ARCHIVED'] 			= $extra['IS_TOPIC_ARCHIVED'];
				$return_array[$i]['TOPIC_LEAD'] 				= $extra['TOPIC_LEAD'];
				$return_array[$i]['PRIMARY_TOPIC_CONTENT_OID'] 	= $extra['PRIMARY_TOPIC_CONTENT_OID'];
			}
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
	function get_eve_user_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();


		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mysql')
		{
			$users = $Db_object->query("
				SELECT *
				FROM {$table_prefix}IP_USERS
				ORDER BY USER_OID
				LIMIT $start_at, $per_page
			");

			while ($user = $Db_object->fetch_array($users))
			{
				/*
				$contact_details = $Db_object->query_first("
					SELECT CONTACT_INFO_ADDRESS
					FROM {$table_prefix}IP_USER_CONTACT_INFO
					WHERE CONTACT_INFO_TYPE = 'EMAIL'
					AND `USER_OID`
					LIKE '".$user['USER_OID']."'
				");
				*/
				
				$profile_details = $Db_object->query("
					SELECT * FROM {$table_prefix}IP_PROFILES
					WHERE USER_OID LIKE '".$user['USER_OID']."'
				");

				while ($line = $Db_object->fetch_array($profile_details))
				{
					$user_d['FIRST_NAME'] 					= $line['FIRST_NAME'];
					$user_d['LAST_NAME'] 					= $line['LAST_NAME'];
					$user_d['EMAIL'] 						= $line['EMAIL'];
					$user_d['USER_TITLE'] 					= $line['USER_TITLE'];
					$user_d['REGISTRATION_DATE'] 			= $line['REGISTRATION_DATE'];
					$user_d['DOB'] 							= $line['DOB'];
					$user_d['GENDER'] 						= $line['GENDER'];
					$user_d['PICTURE_URL'] 					= $line['PICTURE_URL'];
					$user_d['AVATAR_URL'] 					= $line['AVATAR_URL'];
					$user_d['HOME_PAGE_URL'] 				= $line['HOME_PAGE_URL'];
					$user_d['LOCATION'] 					= $line['LOCATION'];
					$user_d['SIGNATURE'] 					= $line['SIGNATURE'];
					$user_d['OCCUPATION'] 					= $line['OCCUPATION'];
					$user_d['INTERESTS']					= $line['INTERESTS'];
					$user_d['BIO'] 							= $line['BIO'];
					$user_d['HAS_OPTED_OUT_OF_EMAIL'] 		= $line['HAS_OPTED_OUT_OF_EMAIL'];
					$user_d['PARENT_PERMISSION_RECEIVED'] 	= $line['PARENT_PERMISSION_RECEIVED'];
					$user_d['USER_IP'] 						= $line['USER_IP'];
					$user_d['LAST_LOGIN_DATETIME'] 			= $line['LAST_LOGIN_DATETIME'];
					$user_d['TOS_DATE'] 					= $line['TOS_DATE'];
				}

				$user_stats = $Db_object->query_first("SELECT * FROM {$table_prefix}IP_USER_STATS WHERE USER_OID =".$user['USER_OID']);
				
				$user_d['CUMULATIVE_USER_POST_COUNT']	= $user_stats['CUMULATIVE_USER_POST_COUNT'];
				$user_d['KARMA_POINTS'] 				= $user_stats['KARMA_POINTS'];
				
				#$user_d['EMAIL'] 			= $contact_details[0];
				$user_d['USERNAME'] 		= $user['LOGIN']; // Email now too
				$user_d['DISPLAY_NAME'] 	= $user['DISPLAY_NAME'];

				$return_array["$user[USER_OID]"] = $user_d;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


} // Class end
# Autogenerated on : March 8, 2005, 12:17 am
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.42 $
|| ####################################################################
\*======================================================================*/
?>

