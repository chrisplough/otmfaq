<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 2.0.2 - Licence Number VBB906673F
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2009 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

define('BLOG_SEARCHGEN_CRITERIA_ADDED', 1);
define('BLOG_SEARCHGEN_CRITERIA_FAILED', 2);
define('BLOG_SEARCHGEN_CRITERIA_UNNECESSARY', 3);

class vB_Blog_Search
{
	/**
	* @var	vB_Registry
	*/
	var $registry = null;

	/**
	* Object that will be used to generate the search query
	*
	* @var	vB_Blog_SearchGenerator
	*/
	var $generator = null;

	var $sort = 'blog.lastcomment';
	var $sort_raw = 'lastcomment';
	var $sortorder = 'desc';

	var $criteria_raw = array();

	/**
	* Constructor.
	*
	* @param	vB_Registry
	*/
	function vB_Blog_Search(&$registry)
	{
		$this->registry =& $registry;
		$this->generator =& new vB_Blog_SearchGenerator($registry);
	}

	/**
	* Adds a search criteria
	*
	* @param	string	Name of criteria
	* @param	mixed	How to restrict the criteria
	*
	* @return	boolean	True on success
	*/
	function add($name, $value)
	{
		$raw = $value;
		$genval = $this->generator->add($name, $value);
		if ($genval == BLOG_SEARCHGEN_CRITERIA_ADDED)
		{
			$this->criteria_raw["$name"] = $raw;
			return true;
		}
		else if ($genval == BLOG_SEARCHGEN_CRITERIA_UNNECESSARY)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function set_sort($sort, $sortorder)
	{
		if ($this->generator->verify_sort($sort, $sortorder, $sort_raw))
		{
			$this->sort = $sort;
			$this->sort_raw = $sort_raw;
			$this->sortorder = $sortorder;
		}
	}

	function has_criteria()
	{
		return (sizeof($this->criteria_raw) > 0);
	}

	/**
	* Determines whether the current search has errors
	*
	* @return	boolean
	*/
	function has_errors()
	{
		return $this->generator->has_errors();
	}

	/**
	* Executes the current search.
	*
	* @return	false|integer	False on failure to execute, integer on success of the issuesearchid that was inserted/used
	*/
	function execute($search_perms)
	{
		if ($this->has_errors())
		{
			return false;
		}

		$db =& $this->registry->db;

		// generate and execute search query
		$criteria = $this->generator->generate();
		if (!$criteria['where'])
		{
			$criteria['where'] = '1=1';
		}

		// this is to prevent flooding
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "blog_search
				(userid, ipaddress, criteria, sortby, sortorder, searchuserid, searchtime, dateline, resultcount, completed)
			VALUES
				(" . $this->registry->userinfo['userid'] . ",
				'" . $db->escape_string(IPADDRESS) . "',
				'" . $db->escape_string(serialize($this->criteria_raw)) . "',
				'" . $db->escape_string($this->sort_raw) . "',
				'" . $db->escape_string($this->sortorder) . "',
				" . intval($this->criteria_raw['searchuserid']) . ",
				0,
				" . TIMENOW . ",
				0,
				0)
		");
		$blogsearchid = $db->insert_id();

		$search_results = $db->query_read_slave("
			SELECT blog.blogid AS id
			FROM " . TABLE_PREFIX . "$criteria[from]
			$criteria[joins]
			" . (!empty($search_perms['join']) ? "$search_perms[join]" : '') . "
			WHERE $criteria[where]
				" . (!empty($search_perms['where']) ? "AND $search_perms[where]" : '') . "
			ORDER BY " . $this->sort . ' ' . $this->sortorder . "
		");

		// prepare results
		$ids = array();
		while ($result = $db->fetch_array($search_results))
		{
			$ids["$result[id]"] = $result;
		}
		$db->free_result($search_results);

		$db->query_write("
			UPDATE " . TABLE_PREFIX . "blog_search SET
				resultcount = " . sizeof($ids) . ",
				completed = 1
			WHERE blogsearchid = $blogsearchid
		");

		if (!$ids)
		{
			$this->generator->error('searchnoresults', '');
			return false;
		}

		$results = array();
		$offset = 0;
		foreach ($ids AS $id)
		{
			$results[] = "($blogsearchid, $id[id], $offset)";
			$offset++;
		}

		$db->query_write("
			INSERT INTO " . TABLE_PREFIX  ."blog_searchresult
				(blogsearchid, id, offset)
			VALUES
				" . implode(',', $results)
		);

		return $blogsearchid;
	}
}

/**
* Generates issue search criteria. Atom is issue.issueid. That table must be available in the final query.
*
* @package 		vBulletin Project Tools
* @copyright 	http://www.vbulletin.com/license.html
*/
class vB_Blog_SearchGenerator
{
	/**
	* List of valid criteria names. Key: criteria name, value: add method
	*
	* @var	array
	*/
	var $valid_fields = array(
		'searchuserid'          => 'add_userid',                  // int
		'title'                 => 'add_title',                   // string - fulltext title search
		'comments_title'        => 'add_comments_title',          // string - fulltext title search
		'textandtitle'          => 'add_text_and_title',
		'comments_textandtitle' => 'add_comments_text_and_title',
		'username'              => 'add_username',                // string
		'tag'                   => 'add_tag',                     //  string
	);

	var $valid_sort = array(
		'lastcomment' => 'blog.lastcomment',
		'posttime'    => 'blog.dateline',
	);

	/**
	* @var	vB_Registry
	*/
	var $registry = null;

	/**
	* List of errors (DM style)
	*
	* @var	array
	*/
	var $errors = array();

	/**
	* From table
	*
	* @var	array
	*/
	var $from = 'blog';

	/**
	* Where clause pieces. Will be ANDed together
	*
	* @var	array
	*/
	var $where = array();

	/**
	* List of joins necessary
	*
	* @var	array
	*/
	var $joins = array();

	/**
	* List of options
	*
	* @var	array
	*/
	var $options = array();

	/**
	* Tachy users separated by commas
	*
	* @var	string
	*/
	var $tachy = '';

	/**
	* Constructor.
	*
	* @param	vB_Registry
	*/
	function vB_Blog_SearchGenerator(&$registry)
	{
		$this->registry = $registry;
		$this->tachy = fetch_coventry('string');
	}

	/**
	* Determines whether the current search has errors
	*
	* @return	boolean
	*/
	function has_errors()
	{
		return !empty($this->errors);
	}

	function verify_sort(&$sort, &$sortorder, &$sort_raw)
	{
		$sort_raw = $sort;
		if (!isset($this->valid_sort["$sort"]))
		{
			$sort = 'blog.lastcomment';
			$sort_raw = 'lastcomment';
		}
		else
		{
			$sort = $this->valid_sort["$sort"];
		}

		switch (strtolower($sortorder))
		{
			case 'asc':
			case 'desc':
				break;
			default:
				$sortorder = 'desc';
		}

		return true;
	}

	/**
	* Adds a search criteria
	*
	* @param	string	Name of criteria
	* @param	mixed	How to restrict the criteria
	*
	* @return	boolean	True on success
	*/
	function add($name, $value)
	{
		if (!isset($this->valid_fields["$name"]))
		{
			$this->error('blog_search_field_x_unknown', htmlspecialchars_uni($name));
			return BLOG_SEARCHGEN_CRITERIA_FAILED;
		}

		$raw = $value;
		$add_method = $this->valid_fields["$name"];
		return $this->$add_method($name, $value);
	}

	/**
	* Adds an error to the list, phrased for the current user. 1 or more arguments
	*
	* @param	string	Error phrase name
	*/
	function error($errorphrase)
	{
		$args = func_get_args();

		if (is_array($errorphrase))
		{
			$error = fetch_error($errorphrase);
		}
		else
		{
			$error = call_user_func_array('fetch_error', $args);
		}

		$this->errors[] = $error;
	}

	/**
	* Generates the search query bits
	*
	* @return	array|false	False if error, array consisting of joins and where clause otherwise
	*/
	function generate()
	{
		if (!$this->has_errors())
		{
			if (can_moderate_blog())
			{
				unset($this->where['blog_tachy'], $this->where['blog_text_tachy']);
			}
			foreach ($this->where AS $key => $value)
			{
				if (empty($value))
				{
					unset($this->where["$key"]);
				}
			}
			return array(
				'joins' => implode("\n", $this->joins),
				'where' => implode("\nAND ", $this->where),
				'from'  => "{$this->from} AS {$this->from}",
			);
		}
		else
		{
			return false;
		}
	}

	/**
	* Prepares a criteria that may either be a scalar or an array
	*
	* @param	mixed		Value to process
	* @param	callback	Callback function to call on each value
	* @param	string		Text to implode the array with
	*
	* @return	mixed		Returns true if the array is empty, otherwise the processed values
	*/
	function prepare_scalar_array($value, $callback = '', $array_splitter = ',')
	{
		if (is_array($value))
		{
			if ($callback)
			{
				$value = array_map($callback, $value);
			}

			if (count($value) == 0 OR (count($value) == 1 AND empty($value[0])))
			{
				return true;
			}
			else
			{
				return implode($array_splitter, $value);
			}
		}
		else if ($callback)
		{
			return call_user_func($callback, $value);
		}
		else
		{
			return $value;
		}
	}

	/**
	* Adds userid ID criteria
	*
	* @param	string
	* @param	integer|array
	*
	* @return	boolean	True on success
	*/
	function add_userid($name, $value)
	{
		$id = $this->prepare_scalar_array($value, 'intval', ',');
		if (!$id)
		{
			return BLOG_SEARCHGEN_CRITERIA_UNNECESSARY;
		}

		$this->where['userid'] = "blog.userid IN ($id)";
		$this->where['blog_tachy'] = (!empty($this->tachy) ? "blog.userid NOT IN ({$this->tachy})" : '');

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

	/**
	* Adds userid ID criteria
	*
	* @param	string
	* @param	integer|array
	*
	* @return	boolean	True on success
	*/
	function add_username($name, $value)
	{
		if (!($userinfo = $this->registry->db->query_first("
			SELECT userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $this->registry->db->escape_string(htmlspecialchars_uni($value)) . "'"
		)))
		{
			$this->error('invalid_user_specified');
			return BLOG_SEARCHGEN_CRITERIA_FAILED;
		}

		$this->where['userid'] = "blog.userid IN ($userinfo[userid])";
		$this->where['blog_tachy'] = (!empty($this->tachy) ? "blog.userid NOT IN ({$this->tachy})" : '');

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

	/**
	* Adds userid ID criteria
	*
	* @param	string
	* @param	integer|array
	*
	* @return	boolean	True on success
	*/
	function add_tag($tag, $value)
	{
		if (!$this->registry->options['vbblog_tagging'])
		{
			return;
		}

		if (!($taginfo = $this->registry->db->query_first("
				SELECT tagid, tagtext
				FROM " . TABLE_PREFIX . "blog_tag
				WHERE tagtext = '" . $this->registry->db->escape_string(htmlspecialchars_uni($value)) . "'
		")))
		{
			$this->error('invalid_tag_specified');
			return BLOG_SEARCHGEN_CRITERIA_FAILED;
		}

		$this->registry->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "blog_tagsearch
				(tagid, dateline)
			VALUES (" . $taginfo['tagid'] . ", " . TIMENOW . ")
		");

		$this->joins['inner_blog_tag'] = trim("
			INNER JOIN " . TABLE_PREFIX . "blog_tagentry AS tagentry ON (tagentry.tagid = $taginfo[tagid] AND tagentry.blogid = blog.blogid)
		");

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

	function prepare_search_text($query_text, &$errors)
	{
		// look for entire words that consist of "&#1234;". MySQL boolean
		// search will tokenize them seperately. Wrap them in quotes if they're
		// not already to emulate search for exactly that word.
		$query = explode('"', $query_text);
		$query_part_count = count($query);

		$query_text = '';
		for ($i = 0; $i < $query_part_count; $i++)
		{
			// exploding by " means the 0th, 2nd, 4th... entries in the array
			// are outside of quotes
			if ($i % 2 == 1)
			{
				// 1st, 3rd.. entry = in quotes
				$vbulletin->GPC['query'] .= '"' . $query["$i"] . '"';
			}
			else
			{
				// look for words that are entirely &#1234;
				$query_text .= preg_replace(
					'/(?<=^|\s)((&#[0-9]+;)+)(?=\s|$)/',
					'"$1"',
					$query["$i"]
				);
			}
		}

		$query_text = preg_replace(
			'#"([^"]+)"#sie',
			"stripslashes(str_replace(' ' , '*', '\\0'))",
			$query_text
		);

		require_once(DIR . '/includes/functions_search.php');
		$query_text = sanitize_search_query($query_text, $errors);

		if (!$errors)
		{
			// a tokenizing based approach to building a search query
			preg_match_all('#("[^"]*"|[^\s]+)#', $query_text, $matches, PREG_SET_ORDER);
			$new_query_text = '';
			$token_joiner = null;
			foreach ($matches AS $match)
			{
				if ($match[1][0] == '-')
				{
					// NOT has already been converted
					$new_query_text = "($new_query_text) $match[1]";
					continue;
				}

				switch (strtoupper($match[1]))
				{
					case 'OR':
					case 'AND':
					case 'NOT':
						// this isn't a searchable word, but a joiner
						$token_joiner = strtoupper($match[1]);
						break;

					default:
						if ($new_query_text !== '')
						{
							switch ($token_joiner)
							{
								case 'OR':
									// OR is no operator
									$new_query_text .= " $match[1]";
									break;

								case 'NOT':
									// NOT this, but everything before it
									$new_query_text = "($new_query_text) -$match[1]";
									break;

								case 'AND':
								default:
									// if we didn't have a joiner, default to and
									$new_query_text = "+($new_query_text) +$match[1]";
									break;
							}
						}
						else
						{
							$new_query_text = $match[1];
						}

						$token_joiner = null;
				}
			}

			$query_text = $new_query_text;

		}

		return trim($query_text);
	}

	/**
	* Adds full blog text search criteria limited to entries
	*
	* @param	string
	* @param	string
	*
	* @return	boolean	True on success
	*/
	function add_title($name, $value)
	{
		$value = strval($value);
		if (!$value)
		{
			return BLOG_SEARCHGEN_CRITERIA_UNNECESSARY;
		}

		// verify search text
		$value = $this->prepare_search_text($value, $errors);
		if ($errors)
		{
			foreach ($errors AS $error)
			{
				$this->error($error);
			}
			return BLOG_SEARCHGEN_CRITERIA_FAILED;
		}

		$this->joins['inner_blog'] = trim("
			INNER JOIN " . TABLE_PREFIX . "blog AS blog ON (blog.blogid = blog_text.blogid)
		");

		$value = $this->registry->db->escape_string($value);

		$this->where['title'] = trim("
			MATCH(blog_text.title) AGAINST ('$value' IN BOOLEAN MODE)
		");
		$this->where['nocomments'] = trim("
			blog_text.blogtextid = blog.firstblogtextid
		");
		$this->where['blog_tachy'] = (!empty($this->tachy) ? "blog.userid NOT IN ({$this->tachy})" : '');
		$this->from = "blog_text";

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

	/**
	* Adds full blog text search criteria (title)
	*
	* @param	string
	* @param	string
	*
	* @return	boolean	True on success
	*/
	function add_comments_title($name, $value)
	{
		$result = $this->add_title($name, $value);
		if ($result != BLOG_SEARCHGEN_CRITERIA_ADDED)
		{
			return $result;
		}

		$this->where['nocomments'] = '';

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

	/**
	* Adds full blog text search criteria (title OR text), limited to entries
	*
	* @param	string
	* @param	string
	*
	* @return	boolean	True on success
	*/
	function add_text_and_title($name, $value)
	{
		$value = strval($value);
		if (!$value)
		{
			return BLOG_SEARCHGEN_CRITERIA_UNNECESSARY;
		}

		// verify search text
		$value = $this->prepare_search_text($value, $errors);
		if ($errors)
		{
			foreach ($errors AS $error)
			{
				$this->error($error);
			}
			return BLOG_SEARCHGEN_CRITERIA_FAILED;
		}

		$this->joins['inner_blog'] = trim("
			INNER JOIN " . TABLE_PREFIX . "blog AS blog ON (blog.blogid = blog_text.blogid)
		");

		$value = $this->registry->db->escape_string($value);

		$this->where['text'] = trim("
			MATCH(blog_text.title, blog_text.pagetext) AGAINST ('$value' IN BOOLEAN MODE)
		");
		$this->where['nocomments'] = trim("
			blog_text.blogtextid = blog.firstblogtextid
		");
		$this->where['blog_tachy'] = (!empty($this->tachy) ? "blog.userid NOT IN ({$this->tachy})" : '');
		$this->where['blog_text_tachy'] = (!empty($this->tachy) ? "blog_text.userid NOT IN ({$this->tachy})" : '');
		$this->from = "blog_text";

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

	/**
	* Adds full blog text search criteria (title OR text)
	*
	* @param	string
	* @param	string
	*
	* @return	boolean	True on success
	*/
	function add_comments_text_and_title($name, $value)
	{
		$result = $this->add_text_and_title($name, $value);
		if ($result != BLOG_SEARCHGEN_CRITERIA_ADDED)
		{
			return $result;
		}

		$this->where['nocomments'] = '';

		return BLOG_SEARCHGEN_CRITERIA_ADDED;
	}

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 09:18, Thu Jul 23rd 2009
|| # SVN: $Revision: 29467 $
|| ####################################################################
\*======================================================================*/
?>