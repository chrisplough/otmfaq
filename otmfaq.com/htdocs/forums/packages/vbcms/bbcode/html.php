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

require_once DIR . '/includes/class_bbcode.php' ;

/**
* Extension of the general BB Code parser that parses CMS-specific tags, such
* as tables and pages. This will generally be used for article-like content.
*
* @package vBulletin
* @copyright vBulletin Solutions Inc.
*/
class vBCms_BBCode_HTML extends vB_BbCodeParser
{
	/**
	* The page we wish to be outputted when parsing. Otherwise, all pages will be outputted.
	* If specified, a list of page titles will be collected and can be accessed by calling
	* getPageTitles(). Note that page 1 never has a title.
	*
	* @var	integer
	*/
	protected $output_page = 0;

	/**
	* Tracks the current page during parsing.
	*
	* @var	integer
	*/
	protected $current_page = 0;

	/**
	* Details about the pages. Only contains text if we are trying to retrieve
	* the information for the non-last page. Note that the first page never has
	* a title.
	*
	* @var	array
	*/
	protected $pages = array();

	/**
	* Logs whether the page that was requested was valid. Is always true if
	* no page is specified.
	*
	* @var	bool
	*/
	protected $fetched_valid_page = true;

	/**
	* Object to provide the implementation of the table helper to use.
	* See setTableHelper and getTableHelper.
	*
	* @var	vBCms_BBCodeHelper_Table
	*/
	protected $table_helper = null;

	/**
	*	Display full size image attachment if an image is missing a thumbnail, otherwise display a link
	*
	*/
	protected $displayimage = true;

	/**
	* Whether this parser unsets attachment info in $this->attachments when an inline attachment is found
	* Base class has this set as public so must be here as well
	*
	* @var	bool
	*/
	public $unsetattach = true;

	/**Whether this user has rights to view attachments **/
	private $candownload = true;

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
			require_once DIR . '/packages/vbcms/bbcodehelper/table.php';
			$this->table_helper = new vBCms_BBCodeHelper_Table($this);
		}

		return $this->table_helper;
	}

	/**
	* Setter for the output page handler. Set the page we wish to be outputted when parsing.
	* If set to 0 (or never called), all pages will be outputted.
	* If specified, a list of page titles will be collected and can be accessed by calling
	* getPageTitles(). Note that page 1 never has a title.
	*
	* @param	integer	The page that you wish to be returned by parsing
	*/
	public function setOutputPage($page)
	{
		$this->output_page = intval($page);
	}

	/**
	* Gets the page titles. This function only works if a specific page is outputted.
	*
	* @return	array	Key: page num, value: title
	*/
	public function getPageTitles()
	{
		$titles = array();

		foreach ($this->pages AS $page_num => $info)
		{
			$titles[$page_num] = $info['title'];
		}

		return $titles;
	}

	/**
	* Returns whether the page that was requested was valid. Is always true if
	* no page is specified.
	*
	* @return	bool
	*/
	public function fetchedValidPage()
	{
		return $this->fetched_valid_page;
	}

	/**
	 * set the appropriate user rights based on the section candownload permission
	 *
	 * @return	bool
	 */
	public function setCanDownload($candownload = true)
	{
		$this->candownload = $candownload;
		$this->unsetattach = ! $candownload;
	}


	/**
	* Version of the parse function that simply prevents you from running it.
	* There's too much stuff for other content types that doesn't apply.
	*/
	public function parse($text, $forumid = 0, $allowsmilie = true, $isimgcheck = false, $parsedtext = '', $parsedhasimages = 3, $cachable = false)
	{
		trigger_error('You need to call do_parse() directly.', E_USER_ERROR);
	}

	/**
	* Parse the string with the selected options
	*
	* @param	string	Unparsed text
	* @param	bool	Whether to allow HTML (true) or not (false)
	* @param	string	HTML State
	*
	* @return	string	Parsed text
	*/
	public function do_parse($pagetext, $do_html = false, $htmlstate = null)
	{
		return parent::do_parse($pagetext, $do_html, true, true, $this->candownload, true, false, $htmlstate);
	}

	/** the default amount of preview text **/
	protected $default_previewlen = 120;

	/**
	* Parse an input string with BB code to a final output string of HTML
	*
	* @param	string	Input Text (BB code)
	* @param	bool	Whether to parse smilies
	* @param	bool	Whether to parse img (for the video bbcodes)
	* @param	bool	Whether to allow HTML (for smilies)
	*
	* @return	string|false	String output Text (HTML) if a valid page, false if invalid page
	*/
	function parse_bbcode($input_text, $do_smilies, $do_imgcode, $do_html = false)
	{
		if ($this->output_page)
		{
			$this->current_page = 1;
			$this->pages = array(1 => array('title' => ''));
		}
		else
		{
			$this->current_page = 0;
			$this->pages = array();
		}

		if (!$this->candownload)
		{
			$do_imgcode = false;
		}

		$last_page_text = parent::parse_bbcode($input_text, $do_smilies, $do_imgcode, $do_html);

		$this->parse_output = '';
		$this->fetched_valid_page = true;

		if ($this->output_page)
		{
			if ($this->output_page == $this->current_page)
			{
				return $last_page_text;
			}
			else if (isset($this->pages[$this->output_page]))
			{
				return $this->pages[$this->output_page]['text'];
			}
			else
			{
				$this->fetched_valid_page = false;
				return '';
			}
		}
		else
		{
			return $last_page_text;
		}
	}



	/**
	* Parses the [page] tag. If we're not looking for the output of a specific
	* page, the page tag is rendered as a header. If we're looking for a particular
	* page, only that text will be returned and the page titles will be stored.
	*
	* Note that if the page tag is not at the root, it will always be ignored.
	* This is because of differing behavior with single- and multi-page views.
	*
	* @param	string	Page title
	*
	* @return	string	Output of the page header in multi page views, nothing in single page views
	*/
	protected function parsePageTag($page_title)
	{
		if (sizeof($this->stack) != 1)
		{
			// put a page tag at the non-root level. Ignore it. (Note: the page tag is what's in the stack).
			// This approach works, but users might not even realize they've done this
			// and be confused when the tag is ignored.
			return "<div>$page_title</div>";
		}

		if (!$this->output_page)
		{
			return '<h3 style="border: 1px dashed #cccccc; border-top: 3px double black; padding: 4px;">' . $page_title . '</h3>';
		}

		// page text applies to current page, title to the upcoming page
		if ($this->output_page == $this->current_page)
		{
			$this->pages[$this->current_page]['text'] = $this->parse_output;
		}

		$this->pages[$this->current_page + 1] = array('title' => $page_title);

		$this->current_page++;
		$this->parse_output = '';

		return '';
	}

	/**
	* Parses the [table] tag and returns the necessary HTML representation.
	* TRs and TDs are parsed by this function (they are not real BB codes).
	* Classes are pushed down to inner tags (TRs and TDs) and TRs are automatically
	* valigned top.
	*
	* @param	string	Content within the table tag
	* @param	string	Optional set of parameters in an unparsed format. Parses "param: value, param: value" form.
	*
	* @return	string	HTML representation of the table and its contents.
	*/
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
			'html' => '%1$s',
			'strip_empty' => true
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

	/**
	* Handles an [img] tag. Overrides base definition
	*
	* @param	string	The text to search for an image in.
	* @param	string	Whether to parse matching images into pictures or just links.
	*
	* @return	string	Text representation of the tag.
	*/
	// Currently using the base handle_bbcode_img
	function ______handle_bbcode_img($bbcode, $do_imgcode, $has_img_code = false)
	{
		global $vbphrase, $vbulletin;

	
		//if this user doesn't have authorization, we're done.
		if (! $this->candownload)
		{
			return '';
		}

		if (($has_img_code == 2 OR $has_img_code == 3) AND preg_match_all('#\[attach(?:=(right|left|config))?\](\d+)\[/attach\]#i', $bbcode, $matches))
		{
			$search = $replace = $configids = $alignids = array();

			foreach($matches[1] AS $key => $type)
			{
				if (strtolower($type) == 'config')
				{
					$configids[$matches[2][$key]] = true;
				}
				else
				{
					$otherids[$matches[2][$key]] = true;
				}
			}

			if (!is_array($this->attachments))
			{
				$this->attachments = array();
				// query all the attachments associated with this article at once
				$attachments = $vbulletin->db->query_read("
					SELECT a.attachmentid, a.settings, a.dateline, a.filename, a.counter, a.contentid,
						fd.filesize
					FROM " . TABLE_PREFIX . "attachment AS a
					LEFT JOIN " . TABLE_PREFIX . "filedata AS fd ON (a.filedataid = fd.filedataid)
					WHERE attachmentid IN (" . implode(", ", $matches[2]) . ")
				");
				while ($attachment = vB::$vbulletin->db->fetch_array($attachments))
				{
					$this->attachments[] = $attachment;
				}
			}

			// loop through each attachment to apply appropriate classes when bbcode replace happens
			foreach($this->attachments AS $attachment)
			{
				$attachmentid = $attachment['attachmentid'];
				$contentid = $attachment['contentid'];
	
				if ($configids[$attachmentid])
				{
					$settings = unserialize($attachment['settings']);

					// get the proper css classes for the alignment setting
					if (isset($settings['alignment']))
					{
						switch ($settings['alignment'])
						{
							case 'left':
								$align_class = 'align_left';
								break;
							case 'center':
								$align_class = 'align_center';
								break;
							case 'right':
								$align_class = 'align_right';
								break;
							case '0':
							default:
								// no special css class for none
								$align_class = '';
								break;
						}
					}

					// get the proper css classes for the size setting
					if (isset($settings['size']))
					{
						switch ($settings['size'])
						{
							case 'thumbnail':
								$size_class = 'size_thumbnail';
								break;
							case 'medium':
								$size_class = 'size_medium';
								break;
							case 'large':
								$size_class = 'size_large';
								break;
							case 'fullsize':
								$size_class = 'size_fullsize';
								break;
							default:
								// no special css class for none
								$size_class = '';
								break;
						}
					}

					// get the image caption if there is one
					if ( isset($settings['caption']) AND $settings['caption']!='' )
					{
						$caption_tag = '<p class="caption '.$size_class.'">'.$settings['caption'].'</p>';
					}

					// get the title, which we will use for the alt attribute (this may change)
					if (isset($settings['title']))
					{
						$title_text = $settings['title'];
					}

					// get the description, which we will use for the title attribute (this may change)
					if (isset($settings['description']))
					{
						$description_text = $settings['description'];
					}

					// get the inline styles
					if (isset($settings['styles']))
					{
						$styles = $settings['styles'];
					}

					$search[] = '#\[attach=config\](' . $attachmentid . ')\[/attach\]#i';

					// TODO, uncomment this line, when we want to add the captions back in
					// we still need to prevent the caption text from being saved as part of the article
					//$replace[] = "<span><img class=\"previewthumb $align_class $size_class\" style=\"$styles\" src=\"{$vbulletin->options['bburl']}/attachment.php?attachmentid=$attachmentid&amp;stc=1\" class=\"previewthumb\" alt=\"$description_text\" title=\"$title_text\" />$caption_tag</span>";
					$img_url = "{$vbulletin->options['bburl']}/attachment.php?attachmentid=$attachmentid&amp;stc=1";
					$replace[] = "<a id=\"attachment$attachmentid\" rel=\"Lightbox_$contentid\" href=\"$img_url\"><img class=\"previewthumb $align_class $size_class\" style=\"$styles\" src=\"$img_url\" alt=\"$description_text\" title=\"$title_text\" /></a>";
				}
				else
				{
					$align = $matches[1][$key];
					$search[] = '#\[attach' . (!empty($align) ? '=' . $align : '') . '\](' . $attachmentid . ')\[/attach\]#i';
					$replace[] = "<img src=\"{$this->registry->options['bburl']}/attachment.php?{$this->registry->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline]\" border=\"0\" alt=\""
						. construct_phrase($vbphrase['image_x_y_z'], $attachment['filename'], $attachment['counter'], $attachment['filesize'])
						. "\" " . (!empty($align) ? " style=\"float: $align\"" : '') . " />";
				}
			}
			$bbcode = preg_replace($search, $replace, $bbcode);
		}

		if ($has_img_code == 1 OR $has_img_code == 3)
		{
			if ($do_imgcode AND ($this->registry->userinfo['userid'] == 0 OR $this->registry->userinfo['showimages']))
			{
				// do [img]xxx[/img]
				$bbcode = preg_replace('#\[img\]\s*(https?://([^*\r\n]+|[a-z0-9/\\._\- !]+))\[/img\]#iUe', "\$this->handle_bbcode_img_match('\\1')", $bbcode);
			}
		}

		return $bbcode;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 29533 $
|| ####################################################################
\*======================================================================*/