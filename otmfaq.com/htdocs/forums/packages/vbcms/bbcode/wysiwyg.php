<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.1 Patch Level 1 - Licence Number VBS309B87F
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

require_once DIR . '/includes/class_bbcode_alt.php' ;

class vBCms_BBCode_Wysiwyg extends vB_BbCodeParser_Wysiwyg
{
	/**
	* Object to provide the implementation of the table helper to use.
	* See setTableHelper and getTableHelper.
	*
	* @var	vBCms_BBCodeHelper_Table
	*/
	protected $table_helper = null;

	/**
	* External method to set/change the table helper implementation if necessary.
	* Generally won't be used.
	*
	* @param	vBCms_BBCodeHelper_Table	Alternative helper
	*/
	public function setTableHelper(vBCms_BBCodeHelper_Table $helper)
	{
		$this->table_helper = $helper;
	}

	/**
	* Fetches the table helper in use. It also acts as a lazy initializer.
	* If no table helper has been explicitly set, it will instantiate
	* the class's default.
	*
	* @return	vBCms_BBCodeHelper_Table	Table helper object
	*/
	public function getTableHelper()
	{
		if (!$this->table_helper)
		{
			require_once DIR . '/packages/vbcms/bbcodehelper/table/wysiwyg.php' ;
			$this->table_helper = new vBCms_BBCodeHelper_Table_Wysiwyg($this);
		}

		return $this->table_helper;
	}

	/**
	* Parse an input string with BB code to a final output string of HTML
	*
	* @param	string	Input Text (BB code)
	* @param	bool	Whether to parse smilies
	* @param	bool	Whether to parse img (for the video bbcodes)
	* @param	bool	Whether to allow HTML (for smilies)
	*
	* @return	string	Ouput Text (HTML)
	*/
	function parse_bbcode($input_text, $do_smilies, $do_imgcode, $do_html = false)
	{
		$text = parent::parse_bbcode($input_text, $do_smilies, $do_imgcode, $do_html);

		if (substr($text, -8) == '</table>')
		{
			// must add a trailing line break to a table that ends the text
			if ($this->is_wysiwyg('ie'))
			{
				$text .= "<p></p>";
			}
			else
			{
				$text .= "<br />";
			}
		}

		return $text;
	}

	/**
	* Parses out specific white space before or after cetain tags, rematches
	* tags where necessary, and processes line breaks.
	*
	* @param	string	Text to process
	* @param	bool	Whether to translate newlines to HTML breaks (unused)
	*
	* @return	string	Processed text
	*/
	function parse_whitespace_newlines($text, $do_nl2br = true)
	{
		$text = parent::parse_whitespace_newlines($text, $do_nl2br);

		if ($this->is_wysiwyg('ie'))
		{
			// heading tags that span multiple lines shouldn't have p tags within
			// and they can't be split into multiple tags
			$text = preg_replace_callback(
				array(
					'#\[((h)=.*)\](.*)\[/\\2\]#siU',
					'#\[((page))\](.*)\[/\\2\]#siU',
				),
				array($this, 'rematchIELinebreaks'),
				$text
			);

			// close any open p tags that come up to heading tags
			$text = preg_replace(
				array(
					'#(\[h=[1-6]\].*\[/h\])#siU',
					'#(\[page\].*\[/page\])#siU',
				),
				'</p>\\1<p>',
				$text
			);

			$text = str_replace('<p></p>', '', $text);
		}

		return $text;
	}

	protected function rematchIELinebreaks($match)
	{
		$text = $match[3];
		$open = $match[1];
		$close = $match[2];

		if (strpos($text, "\n") !== false)
		{
			$text = str_replace("</p>\n<p>", "<br>\n", $text);
			return '[' . $open . ']' . $text . '[/' . $close . ']';
		}
		else
		{
			return $match[0];
		}
	}

	protected function parsePageTag($page_title)
	{
		return '<h3 class="wysiwyg_pagebreak">' . $page_title . '</h3>';
	}

	protected function parseTableTag($content, $params = '')
	{
		$helper = $this->getTableHelper();
		return $helper->parseTableTag($content, $params);
	}

	/**
	* Fetches the tags that are available for this CMS BB code parser.
	* Includes the default vB tags (if enabled).
	*
	* @return	array
	*/
	public static function fetchCmsTags()
	{
		$tag_list = fetch_tag_list();

		$tag_list['option']['h'] = array(
			'html' => '<h%2$s>%1$s</h%2$s>',
			'option_regex' => '#^[1-6]$#',
			'strip_space_after' => 2,
			'strip_empty' => true
		);

		$tag_list['no_option']['page'] = array(
			'callback' => 'parsePageTag',
			'strip_space_after' => 2,
			'stop_parse' => true,
			'disable_smilies' => true,
			'strip_empty' => true
		);

		$tag_list['no_option']['table'] = array(
			'callback' => 'parseTableTag',
			'strip_space_after' => 1,
			'strip_empty' => true
		);

		$tag_list['option']['table'] = array(
			'callback' => 'parseTableTag',
			'strip_space_after' => 1,
			'strip_empty' => true
		);

		$tag_list['no_option']['hr'] = array(
			'html' => '<hr />%1$s',
			'strip_empty' => false
		);

		$tag_list['no_option']['prbreak'] = array(
			'html' => '<hr class="previewbreak" />%1$s',
			'strip_empty' => false
		);

		$tag_list['no_option']['sub'] = array(
			'html' => '<sub>%1$s</sub>',
			'strip_empty' => true
		);

		$tag_list['no_option']['sup'] = array(
			'html' => '<sup>%1$s</sup>',
			'strip_empty' => true
		);

		return $tag_list;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 29533 $
|| ####################################################################
\*======================================================================*/