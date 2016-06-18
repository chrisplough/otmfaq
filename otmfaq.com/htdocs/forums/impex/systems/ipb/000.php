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
* ipb_000
*
* @package 		ImpEx.ipb
* @version		$Revision: 1.23 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/04/03 08:54:07 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
class ipb_000 extends ImpExModule
{
	/**
	* Supported version
	*
	* @var    string
	*/
	var $_version = '1.3';


	/**
	* Module string
	*
	* Class string for phpUnit header
	*
	* @var    array
	*/
	var $_modulestring 	= 'Invision Power Board';
	var $_homepage 	= 'http://www.invisionboard.com/';


	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array (
				'admin_logs', 'admin_sessions', 'badwords', 'cache_store', 'calendar_events', 'categories', 'contacts', 'css',
				'email_logs', 'emoticons', 'faq', 'forum_perms', 'forum_tracker', 'forums', 'groups', 'languages', 'macro',
				'macro_name', 'member_extra', 'members', 'messages', 'moderator_logs', 'moderators', 'pfields_content', 'pfields_data',
				'polls', 'posts', 'reg_antispam', 'search_results', 'sessions', 'skin_templates', 'skins', 'spider_logs', 'stats',
				'subscription_currency', 'subscription_extra', 'subscription_logs', 'subscription_methods', 'subscription_trans', 'subscriptions',
				'templates', 'titles', 'tmpl_names', 'topic_mmod', 'topics', 'tracker', 'validating', 'voters', 'warn_logs'
				);


	function ipb_000()
	{
	}


	function get_ipb_members_list(&$Db_object, &$databasetype, &$tableprefix, &$start, &$per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($per_page)) { return $return_array; }


		if ($databasetype == 'mysql')
		{
			$user_list = $Db_object->query("SELECT id, name FROM " . $tableprefix . "members ORDER BY id LIMIT " . $start . "," . $per_page);

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
	* Returns a usergroup details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_user_group_details(&$DB_object, &$database_type, &$table_prefix)
	{
		$return_array = array();
		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$table_prefix."groups
			ORDER BY g_id
			"
			;

			$user_groups = $DB_object->query($sql);

			while ($user_group = $DB_object->fetch_array($user_groups))
			{

				$return_array["$user_group[g_id]"] = $user_group;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	/**
	* Returns the userid to user name array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_user_details(&$DB_object, &$database_type, &$table_prefix, &$user_start_at, &$user_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($user_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$table_prefix."members
			ORDER BY id
			LIMIT " .
			$user_start_at .
			"," .
			$user_per_page
			;

			$users = $DB_object->query($sql);

			while ($user = $DB_object->fetch_array($users))
			{
				$return_array["$user[id]"] = $user;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}



	/**
	* Returns the cssid to css details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array
	*/
	function get_ipb_css(&$DB_object, &$database_type, &$table_prefix)
	{
		$return_array = array();
		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " . $table_prefix . "css"
			;

			$styles = $DB_object->query($sql);

			while ($style = $DB_object->fetch_array($styles))
			{
				$return_array["$style[cssid]"] = $style;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}



	/**
	* Returns the category details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array
	*/
	function get_ipb_category_details(&$DB_object, &$database_type, &$table_prefix)
	{
		$return_array = array();
		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$table_prefix."categories
			ORDER BY id"
			;
			$categories = $DB_object->query($sql);

			while ($category = $DB_object->fetch_array($categories))
			{
				$return_array["$category[id]"] = $category;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	/**
	* Returns the forum details
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_forum_details(&$DB_object, $database_type, $table_prefix, $forum_start_at, $forum_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($forum_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$table_prefix."forums
			ORDER BY id
			LIMIT " .
			$forum_start_at .",".
			$forum_per_page
			;

			$forums = $DB_object->query($sql);

			while ($forum = $DB_object->fetch_array($forums))
			{
				$return_array["$forum[id]"] = $forum;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	/**
	* Returns the styleid to style details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_imported_ipb_style_ids(&$DB_object, $database_type, $table_prefix)
	{
		$return_array = array();

		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT importstyleid,styleid FROM " .
			$table_prefix."style
			";

			$styles = $DB_object->query($sql);

			while ($style = $DB_object->fetch_array($styles))
			{
				$return_array[$style['importstyleid']] = $style['styleid'];
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	/**
	* Returns the parent id of a forum
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			The import forum id
	*
	* @return	int
	*/
	function get_ipb_parent_forum_id(&$DB_object, $database_type, $table_prefix,$import_forum_id)
	{
		$return_array = array();

		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT forumid
			FROM
			" . $table_prefix . "forums
			WHERE
			importcategoryid = '". $import_forum_id . "';
			";

			$forumid = $DB_object->query_first($sql);

			return $forumid[0];
		}
		else
		{
			return false;
		}

		return $return_array;
	}



	/**
	* Returns the thread details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_thread_details(&$DB_object, &$database_type, &$table_prefix, &$thread_start_at, &$threads_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($threads_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT *
			FROM " . $table_prefix . "topics
			ORDER BY tid
			LIMIT " .
			$thread_start_at .",".
			$threads_per_page
			;

			$forums = $DB_object->query($sql);

			while ($forum = $DB_object->fetch_array($forums))
			{
				$return_array["$forum[tid]"] = $forum;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the post details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_posts_details(&$DB_object, &$database_type, &$table_prefix, &$post_start_at, &$posts_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($posts_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "SELECT * FROM
			" . $table_prefix . "posts
			ORDER BY pid
			LIMIT " .
			$post_start_at .",".
			$posts_per_page . "
			";

			$posts = $DB_object->query($sql);

			while ($post = $DB_object->fetch_array($posts))
			{
				$return_array["$post[pid]"] = $post;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	/**
	* Regex call back
	*
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	string
	*/
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

	   return trim(stripslashes($post));;
	}


	/**
	* Returns the IPB poll details
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	int
	*/
	function get_ipb_polls_details(&$DB_object, &$database_type, &$table_prefix, &$poll_start_at, &$poll_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($poll_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$poll_start_at = $this->iif($poll_start_at == '','0',$poll_start_at);

			$sql = "
			SELECT * FROM " .
			$table_prefix."polls
			ORDER BY pid
			LIMIT " .
			$poll_start_at .",".
			$poll_per_page
			;

			$polls = $DB_object->query($sql);

			while ($poll = $DB_object->fetch_array($polls))
			{
				$return_array[$poll['pid']] = $poll;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the IPB PM details array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_pms(&$DB_object, &$database_type, &$table_prefix, &$pm_start_at, &$pm_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($pm_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "SELECT * FROM
			" . $table_prefix . "messages
			ORDER BY msg_id
			LIMIT " .
			$pm_start_at .",".
			$pm_per_page . "
			";

			$pms = $DB_object->query($sql);

			while ($pm = $DB_object->fetch_array($pms))
			{
				$return_array["$pm[msg_id]"] = $pm;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the IPB poll details
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_buddy_ignore_lists(&$DB_object, &$database_type, &$table_prefix, &$list_start_at, &$list_per_page)
	{
		// TODO: This is called buddy ignore, its missleading with the variable names
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($list_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$table_prefix."contacts
			ORDER BY id
			LIMIT " .
			$list_start_at .",".
			$list_per_page
			;

			$list = $DB_object->query($sql);

			while ($buddy = $DB_object->fetch_array($list))
			{
				$return_array["$buddy[id]"] = $buddy;
			}
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	/**
	* Returns the IPB moderators details
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_ipb_moderators_details($DB_object, $database_type, $table_prefix, $moderators_start_at, $moderators_per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($moderators_per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT * FROM " .
			$table_prefix."moderators
			LIMIT " .
			$moderators_start_at .",".
			$moderators_per_page
			;

			$moderators = $DB_object->query($sql);

			while ($mod = $DB_object->fetch_array($moderators))
			{
				$return_array[$mod['mid']] = $mod;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	function get_ipb_attachment_details($DB_object, $database_type, $table_prefix, $start_at, $per_page)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($per_page)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			$sql = "
			SELECT pid, post_date, attach_id, attach_hits, attach_type, attach_file FROM " .
			$table_prefix."posts
			WHERE attach_file != ''
			AND attach_file != '|'
			LIMIT " .
			$start_at .",".
			$per_page
			;

			$attachments = $DB_object->query($sql);

			while ($attachment = $DB_object->fetch_array($attachments))
			{
				$return_array[] = $attachment;
			}
		}
		else
		{
			return false;
		}

		return $return_array;
	}


	function get_ipb_attachment($path, $file_name)
	{
		$file_address = $path . "/" . $file_name;

		if($file_name == '' OR !is_file($file_address))
		{
			return false;
		}


		$the_file = array();
		$file = fopen($file_address,'rb');

		if($file AND filesize($file_address) > 0)
		{
			$the_file['data']		= fread($file, filesize($file_address));
			$the_file['filesize']	= filesize($file_address);
			$the_file['filehash']	= md5($the_file['data']);
		}

		return $the_file;
	}

	function get_ipb_poll_voters($DB_object, $database_type, $table_prefix, $poll_id)
	{
		$return_array = array();

		// Check that there isn't a empty value
		if(empty($poll_id)) { return $return_array; }


		if ($database_type == 'mysql')
		{
			if($this->check_table($Db_object, $databasetype, $tableprefix, $table_prefix."forum_poll_voters"))
			{
				$sql = "
				SELECT MEMBER_ID " .
				$table_prefix."forum_poll_voters
				WHERE POLL_ID = " .
				$poll_id
				;

				$poll_voters = $DB_object->query($sql);

				while ($voter = $DB_object->fetch_array($poll_voters))
				{
					$return_array[] = $voter;
				}
			}
			// Have to return an empty array for it to count fail and search again with voters and the thread id not the poll id
			return $return_array;
			
			if($this->check_table($Db_object, $databasetype, $tableprefix, $table_prefix."voters"))
			{
				$sql = "
				SELECT member_id " .
				$table_prefix."voters
				WHERE tid = " .
				$poll_id
				;

				$poll_voters = $DB_object->query($sql);

				while ($voter = $DB_object->fetch_array($poll_voters))
				{
					$return_array[] = $voter;
				}
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
|| # CVS: $RCSfile: 000.php,v $ - $Revision: 1.23 $
|| ####################################################################
\*======================================================================*/
?>
