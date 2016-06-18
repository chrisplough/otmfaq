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
* ipb2 API module
*
* @package			ImpEx.ipb2
* @version			$Revision: 1.10 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name:  $
* @date				$Date: 2006/07/24 23:49:25 $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class ipb2_000 extends ImpExModule
{
	/**
	* Class version
	*
	* This is the version of the source system that is supported
	*
	* @var    string
	*/
	var $_version = '2.1.x';

	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'Invision Board 2';
	var $_homepage 	= 'http://www.invisionboard.com';

	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array (
			'admin_logs', 'admin_sessions', 'announcements', 'attachments', 'attachments_type', 'badwords',
			'banfilters', 'bulk_mail', 'cache_store', 'calendar_events', 'conf_settings', 'conf_settings_titles',
			'contacts', 'custom_bbcode', 'email_logs', 'emoticons', 'faq', 'forum_perms', 'forum_tracker',
			'forums', 'groups', 'languages', 'mail_error_logs', 'mail_queue', 'member_extra', 'members',
			'members_converge', 'message_text', 'message_topics', 'moderator_logs', 'moderators', 'pfields_content',
			'pfields_data', 'polls', 'posts', 'reg_antispam', 'search_results', 'sessions', 'skin_macro',
			'skin_sets', 'skin_templates', 'skin_templates_cache', 'spider_logs', 'stats', 'subscription_currency',
			'subscription_extra', 'subscription_logs', 'subscription_methods', 'subscription_trans', 'subscriptions',
			'task_logs', 'task_manager', 'titles', 'topconfig', 'topic_mmod', 'topics', 'topics_read',
			'topvotes', 'tracker', 'unrealportal', 'up_custblocks', 'up_custcontent', 'upgrade_history',
			'validating', 'voters', 'warn_logs'
	);

	function ipb2_000()
	{
	}

	function ipb2_html($text)
	{
		$text = html_entity_decode($text);
				
		// Remove the image links to the smilies and replace with the emoid
		$text = preg_replace('#<img src="style_emoticons/<\#EMO_DIR\#>/(.*) emoid=\"(.*)\"(.*)/>#isU', '$2', $text);
		
		// colour
		$text = preg_replace('#\[color=(.*)\](.*)\[/color\]#siU', '$2', $text);
		
		// <u>(.*)</u>
		$text = preg_replace('#<u>(.*)</u>#siU', '[u]$1[/u]', $text);
		
		// <span
		$text = preg_replace('#<span style=\'font-size:(.+?)pt;line-height:100%\'>(.+?)</span>#esiU', '\$this->unconvert_size("\\1", "\\2")', $text);
		$text = preg_replace('#<span style="font-size:(.+?)pt;line-height:100%">(.+?)</span>#esiU', '\$this->unconvert_size("\\1", "\\2")', $text);
		$text = preg_replace('#<span style=\'color:([^"]*)\'>([^"]*)</span>#siU', '[color=\\1]\\2[/color]', $text);
		$text = preg_replace('#<span style=\"color:([^"]*)\">([^"]*)</span>#siU', '[color=\\1]\\2[/color]', $text);
		$text = preg_replace('#<span style=\'font-family:([^"]*)\'>([^"]*)</span>#siU', '[font=\\1]\\2[/font]', $text);
		$text = preg_replace('#<span(.*)>(.*)</span>#siU', '$2', $text);
		
		// Quotes
		$text = preg_replace("#<div class='quotetop'>QUOTE\((.*) @(.*)<div class='quotemain'>(.*)</div>#isU", '[quote=$1]$3[/quote]', $text);
		$text = preg_replace("#<div class='quotetop'>QUOTE</div><div class='quotemain'>(.*)</div>#isU", '[quote]$1[/quote]', $text);
		$text = preg_replace("#<div class='quotemain'>(.*)</div>#isU", '[quote]$1[/quote]', $text);

#######################################	
#	OLD PARSING
####################################### 		

		$text = preg_replace('#<u>([^"]*)</u>#siU', '[u]\\1[/u]', $text);
		$text = preg_replace('#<b>([^"]*)</b>#siU', '[b]\\1[/b]', $text);
		$text = preg_replace('#<i>([^"]*)</i>#siU', '[i]\\1[/i]', $text);
		$text = preg_replace('#<span style=\'font-family:([^"]*)\'>([^"]*)</span>#siU', '[font=\\1]\\2[/font]', $text);
		$text = preg_replace('#<span style=\'color:([^"]*)\'>([^"]*)</span>#siU', '[color=\\1]\\2[/color]', $text);
		$text = preg_replace('#<a href=\'(http://|https://|ftp://|news://)([^"]*)\' target=\'_blank\'>([^"]*)</a>#siU', '[url=\\1\\2]\\3[/url]', $text);

		$text = preg_replace('#<img src=\'([^"]*)\' border=\'0\' alt=\'user posted image\'(\s/)?>#siU', '[img]\\1[/img]', $text);
		$text = str_replace("<img src='","[img]",$text);
		$text = preg_replace('#<a href=\'mailto:([^"]*)\'>([^"]*)</a>#siU', '[email=\\1]\\2[/email]', $text);

		$text = preg_replace('#<ul>#siU', '[list]', $text);
		$text = preg_replace('#<ol type=\'[1|i]\'>#siU', '[list=1]', $text);
		$text = preg_replace('#<ol type=\'a\'>#siU', '[list=a]', $text);
		$text = preg_replace('#<li>([^"]*)</li>#siU', "[*]\\1\n", $text);
		$text = preg_replace('#</ul>#siU', '[/list]', $text);
		$text = preg_replace('#</ol>#siU', '[/list]', $text);

		$text = preg_replace('#<!--emo&([^"]*)-->([^"]*)<!--endemo-->#siU', '\\1', $text);
		$text = preg_replace('#<!--c1-->([^"]*)<!--ec1-->#siU', '[code]', $text);
		$text = preg_replace('#<!--c2-->([^"]*)<!--ec2-->#siU', '[/code]', $text);
		$text = preg_replace('#<!--QuoteBegin-->([^"]*)<!--QuoteEBegin-->#siU', '[quote][b]', $text);
		$text = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+-->([^"]*)<!--QuoteEBegin-->#si', '[quote][i]Originally posted by \\1[/i]<br />[b]', $text);
		$text = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+([^"]*)-->([^"]*)<!--QuoteEBegin-->#si', '[quote][i]Originally posted by \\1[/i]@\\2<br />[b]', $text);
		$text = preg_replace('#<!--QuoteEnd-->([^"]*)<!--QuoteEEnd-->#siU', '[/b][/quote]', $text);
		$text = preg_replace('#<span style=\'font-size:(.+?)pt;line-height:100%\'>(.+?)</span>#e', '\$this->unconvert_size("\\1", "\\2")', $text);
		$text = preg_replace('#<!--EDIT\|([^"]*)\|([^"]*)-->#siU', 'Last edited by \\1 at \\2', $text);

		$text = preg_replace('#<a href=\'([^"]*)\' target=\'_blank\'><img src=\'([^"]*)\' alt=\'([^"]*)\' width=\'([^"]*)\' height=\'([^"]*)\' class=\'([^"]*)\' /></a>#siU', '[img]\\2[/img]', $text);

		$text = preg_replace('#<!--aimg-->#siU', '', $text);
		$text = preg_replace('#<!--/aimg-->#siU', '', $text);
		$text = preg_replace('#--Resize_Images_Alt_Text--#siU', '', $text);
		$text = preg_replace('#<!--Resize_Images_Hint_Text-->#siU', '', $text);

		$text = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+-->([^"]*)<!--QuoteEBegin-->#siU', '[quote]Originally posted by \\1<br />[b]', $text);
		$text = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+([^"]*)-->([^"]*)<!--QuoteEBegin-->#siU', '[quote]Originally posted by \\1@\\2<br />[b]', $text);

		$text = preg_replace('#\[quote(.*)\]#siU', '[quote]', $text);
		
#######################################		
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
	function get_ipb2_members_list(&$Db_object, &$databasetype, &$tableprefix, &$start, &$per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT id, name
			FROM " . $tableprefix . "members
			ORDER BY id
			LIMIT " . $start . "," . $per_page;

			$user_list = $Db_object->query($sql);

			while ($user = $Db_object->fetch_array($user_list))
			{
				$return_array["$user[id]"] = $user['name'];
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
	function get_ipb2_attachment_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."attachments
			ORDER BY attach_id
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[attach_id]"] = $detail;
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
	function get_ipb2_forum_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."forums
			WHERE parent_id > 0
			ORDER BY id
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[id]"] = $detail;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	function get_ipb2_categories_details(&$Db_object, &$databasetype, &$tableprefix)
	{
		$return_array = array();

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."forums
			WHERE parent_id = -1"
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[id]"] = $detail;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}

	/**
	* Returns the moderator_id => moderator array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb2_moderator_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."moderators
			ORDER BY mid
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[mid]"] = $detail;
			}
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
	function get_ipb2_pm_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			// This gets the pm, the pm_text is stored else where just like another database.
			$sql = "
			SELECT * FROM " .
			$tableprefix."message_topics
			ORDER BY mt_id
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[mt_id]"] = $detail;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}

	function get_ipb2_pm_text(&$Db_object, &$databasetype, &$tableprefix, $mt_msg_id)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($mt_msg_id)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			// This gets the pm, the pm_text is stored else where just like another database.
			$sql = "
			SELECT * FROM " .
			$tableprefix."message_text
			WHERE msg_id=" . $mt_msg_id
			;

			$detail = $Db_object->query_first($sql);

			return $detail['msg_post'];
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
	function get_ipb2_poll_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			// voter data is in voters.
			$sql = "
			SELECT * FROM " .
			$tableprefix."polls
			ORDER BY pid
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[pid]"] = $detail;
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
	function get_ipb2_post_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."posts
			ORDER BY pid
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[pid]"] = $detail;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}

	/**
	* Returns the smilie_id => smilie array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb2_smilie_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."emoticons
			ORDER BY id
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[id]"] = $detail;
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
	function get_ipb2_thread_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."topics
			ORDER BY tid
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[tid]"] = $detail;
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
	function get_ipb2_user_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			// Normal table
			$sql = "
			SELECT * FROM " .
			$tableprefix."members
			ORDER BY id
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				// remove select *, only take the good ones we use
				$extra_sql = "SELECT * FROM " .	$tableprefix."member_extra WHERE id=" . $detail['id'];

				$extra_array = $Db_object->query_first($extra_sql);

				#$detail = array_merge($detail, $extra_array);
				
				$return_array["$detail[id]"] 					= $detail;
				$return_array["$detail[id]"]['notes'] 			= $extra_array['notes'];
				$return_array["$detail[id]"]['links'] 			= $extra_array['links'];
				$return_array["$detail[id]"]['bio'] 			= $extra_array['bio'];
				$return_array["$detail[id]"]['ta_size'] 		= $extra_array['ta_size'];
				$return_array["$detail[id]"]['photo_type'] 		= $extra_array['photo_type'];
				$return_array["$detail[id]"]['photo_location'] 	= $extra_array['photo_location'];
				$return_array["$detail[id]"]['photo_dimensions'] = $extra_array['photo_dimensions'];
				$return_array["$detail[id]"]['aim_name'] 		= $extra_array['aim_name'];
				$return_array["$detail[id]"]['icq_number'] 		= $extra_array['icq_number'];
				$return_array["$detail[id]"]['website']			= $extra_array['website'];
				$return_array["$detail[id]"]['yahoo'] 			= $extra_array['yahoo'];
				$return_array["$detail[id]"]['interests'] 		= $extra_array['interests'];
				$return_array["$detail[id]"]['msnname'] 		= $extra_array['msnname'];
				$return_array["$detail[id]"]['vdirs'] 			= $extra_array['vdirs'];
				$return_array["$detail[id]"]['location'] 		= $extra_array['location'];
				$return_array["$detail[id]"]['signature'] 		= $extra_array['signature'];
				$return_array["$detail[id]"]['avatar_location'] = $extra_array['avatar_location'];
				$return_array["$detail[id]"]['avatar_size'] 	= $extra_array['avatar_size'];
				$return_array["$detail[id]"]['avatar_type'] 	= $extra_array['avatar_type'];
				$return_array["$detail[id]"]['p_customblock'] 	= $extra_array['p_customblock'];
				$return_array["$detail[id]"]['p_customheight'] 	= $extra_array['p_customheight'];
			}

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
	function get_ipb2_usergroup_details(&$Db_object, &$databasetype, &$tableprefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($per_page)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."groups
			ORDER BY g_id
			LIMIT " .
			$start_at .
			"," .
			$per_page
			;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[g_id]"] = $detail;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}

	function get_ipb2_vote_voters(&$Db_object, &$databasetype, &$tableprefix, $thread_id)
	{
		$return_array = array();

		// Check that there is not a empty value
		if(empty($thread_id)) { return $return_array; }

		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$tableprefix."voters
			WHERE tid=" . $thread_id;

			$details_list = $Db_object->query($sql);

			while ($detail = $Db_object->fetch_array($details_list))
			{
				$return_array["$detail[vid]"] = $detail;
			}

		}
		else
		{
			return false;
		}
		return $return_array;
	}

	function unconvert_size($size="", $text="")
	{
		switch($size)
		{
		   case '21':
			  $size=4;
			  break;
		   case '14':
			  $size=3;
			  break;
		   case '8':
			  $size=1;
			  break;
		   default:
			  $size=2;
			  break;
		}
		return '[SIZE='.$size.']'.$text.'[/SIZE]';
	}

	/**
	* HTML parser
	*
	* @param	string	mixed			The string to be parsed
	*
	* @return	string
	*/
	function ipb_html($post)
	{

		return $post;
		
		$post = preg_replace('#<u>([^"]*)</u>#siU', '[u]\\1[/u]', $post);
		$post = preg_replace('#<b>([^"]*)</b>#siU', '[b]\\1[/b]', $post);
		$post = preg_replace('#<i>([^"]*)</i>#siU', '[i]\\1[/i]', $post);
		$post = preg_replace('#<span style=\'font-family:([^"]*)\'>([^"]*)</span>#siU', '[font=\\1]\\2[/font]', $post);
		$post = preg_replace('#<span style=\'color:([^"]*)\'>([^"]*)</span>#siU', '[color=\\1]\\2[/color]', $post);
		$post = preg_replace('#<a href=\'(http://|https://|ftp://|news://)([^"]*)\' target=\'_blank\'>([^"]*)</a>#siU', '[url=\\1\\2]\\3[/url]', $post);

		$post = preg_replace('#<img src=\'([^"]*)\' border=\'0\' alt=\'user posted image\'(\s/)?>#siU', '[img]\\1[/img]', $post);
		$post = str_replace("<img src='","[img]",$post);
		$post = preg_replace('#<a href=\'mailto:([^"]*)\'>([^"]*)</a>#siU', '[email=\\1]\\2[/email]', $post);

		$post = preg_replace('#<ul>#siU', '[list]', $post);
		$post = preg_replace('#<ol type=\'[1|i]\'>#siU', '[list=1]', $post);
		$post = preg_replace('#<ol type=\'a\'>#siU', '[list=a]', $post);
		$post = preg_replace('#<li>([^"]*)</li>#siU', "[*]\\1\n", $post);
		$post = preg_replace('#</ul>#siU', '[/list]', $post);
		$post = preg_replace('#</ol>#siU', '[/list]', $post);

		$post = preg_replace('#<!--emo&([^"]*)-->([^"]*)<!--endemo-->#siU', '\\1', $post);
		$post = preg_replace('#<!--c1-->([^"]*)<!--ec1-->#siU', '[code]', $post);
		$post = preg_replace('#<!--c2-->([^"]*)<!--ec2-->#siU', '[/code]', $post);
		$post = preg_replace('#<!--QuoteBegin-->([^"]*)<!--QuoteEBegin-->#siU', '[quote][b]', $post);
		$post = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+-->([^"]*)<!--QuoteEBegin-->#si', '[quote][i]Originally posted by \\1[/i]<br />[b]', $post);
		$post = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+([^"]*)-->([^"]*)<!--QuoteEBegin-->#si', '[quote][i]Originally posted by \\1[/i]@\\2<br />[b]', $post);
		$post = preg_replace('#<!--QuoteEnd-->([^"]*)<!--QuoteEEnd-->#siU', '[/b][/quote]', $post);
		$post = preg_replace('#<span style=\'font-size:(.+?)pt;line-height:100%\'>(.+?)</span>#e', '\$this->unconvert_size("\\1", "\\2")', $post);
		$post = preg_replace('#<!--EDIT\|([^"]*)\|([^"]*)-->#siU', 'Last edited by \\1 at \\2', $post);

		$post = str_replace("<br />","\n",$post);
		$post = str_replace("<br>","\n",$post);
		$post = str_replace("&amp;","&",$post);
		$post = str_replace("&lt;","<",$post);
		$post = str_replace("&gt;",">",$post);
		$post = str_replace("&quot;","\"",$post);
		$post = str_replace("&#039;","'",$post);
		$post = str_replace("&#033;","!",$post);
		$post = str_replace("&#124;","|",$post);

		$post = preg_replace('#<a href=\'([^"]*)\' target=\'_blank\'><img src=\'([^"]*)\' alt=\'([^"]*)\' width=\'([^"]*)\' height=\'([^"]*)\' class=\'([^"]*)\' /></a>#siU', '[img]\\2[/img]', $post);

		$post = preg_replace('#<!--aimg-->#siU', '', $post);
		$post = preg_replace('#<!--/aimg-->#siU', '', $post);
		$post = preg_replace('#--Resize_Images_Alt_Text--#siU', '', $post);
		$post = preg_replace('#<!--Resize_Images_Hint_Text-->#siU', '', $post);

		$post = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+-->([^"]*)<!--QuoteEBegin-->#siU', '[quote]Originally posted by \\1<br />[b]', $post);
		$post = preg_replace('#<!--QuoteBegin-{1,2}([^"]*)\+([^"]*)-->([^"]*)<!--QuoteEBegin-->#siU', '[quote]Originally posted by \\1@\\2<br />[b]', $post);

		$post = preg_replace('#\[quote(.*)\]#siU', '[quote]', $post);
		
	   return trim(stripslashes($post));
	}

} // Class end
# Autogenerated on : August 20, 2004, 2:31 pm
# By ImpEx-generator 1.0.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.10 $
|| ####################################################################
\*======================================================================*/
?>
