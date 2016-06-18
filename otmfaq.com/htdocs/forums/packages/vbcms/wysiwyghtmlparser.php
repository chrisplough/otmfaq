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

require_once DIR . '/includes/class_wysiwygparser.php';

/**
* Extension of the WYSIWYG HTML parser class to parse CMS-specific tags,
* such as tables and headings.
*
* @package	vBulletin
* @copyright vBulletin Solutions Inc.
*/
class vBCms_WysiwygHtmlParser extends vB_WysiwygHtmlParser
{
	/**
	* Returns the rule set for parsing matched tags. Array key is name of
	* HTML tag to match. Value is either a simple callback or an array with
	* keys 'callback' and 'param' (an optional extra value to pass in to the
	* parsing callback function). Callbacks may refer to the string $this
	* to refer to the current class instance.
	*
	* @return	array
	*/
	public function load_tag_rules()
	{
		$base_tags = parent::load_tag_rules();

		$new_tags = array(
			'sub' => array(
				'callback' => array('$this', 'parse_tag_basic'),
				'param' => 'sub'
			),
			'sup' => array(
				'callback' => array('$this', 'parse_tag_basic'),
				'param' => 'sup'
			),

			'table' => array('$this', 'parse_tag_table'),
			'tr'    => array('$this', 'parse_tag_tr'),
			'td'    => array('$this', 'parse_tag_td'),
		);

		return array_merge($base_tags, $new_tags);
	}

	/**
	* Parses special unmatched HTML tags like <img> and <br>.
	*
	* @param	string	Text pre-parsed
	*
	* @return	string	Parsed text
	*/
	protected function parse_unmatched_tags($text)
	{
		$text = parent::parse_unmatched_tags($text);

		return preg_replace(
			array(
				'#(?:<p>\s*)?<hr\s*class=(\'|"|)previewbreak\s*(\\1)[^>]*>#si',
				'#(?:<p>\s*)?<hr.*>#siU'
			),
			array(
				'[PRBREAK][/PRBREAK]',
				'[HR][/HR]',
			),
			$text
		);
	}

	/**
	* Parses <h1> through <h6> tags.
	* Also parses PAGE BB codes (which are H3s with a specific class).
	*
	* @param	string	String containing tag attributes
	* @param	string	Text within tag
	* @param	string	Name of HTML tag. Used if one function parses multiple tags
	* @param	mixed	Extra arguments passed in to parsing call or tag rules
	*/
	protected function parse_tag_heading($attributes, $text, $tag_name, $args)
	{
		$tag_name = strtoupper($tag_name);

		$text = trim($text);

		if ($tag_name == 'H3')
		{
			$class = $this->parse_wysiwyg_tag_attribute('class=', $attributes);
			if ($class == 'wysiwyg_pagebreak')
			{
				return "[PAGE]{$text}[/PAGE]\n";
			}
		}

		if (preg_match('#^h(\d)$#i', $tag_name, $match))
		{
			$level = $match[1];
			return "[h=$level]{$text}[/h]\n";
		}
		else
		{
			return "$text\n\n";
		}
	}

	/**
	* Builds the key-value parameter format for table (and tr/td) BB codes.
	*
	* @param	array	Key-value array of params to specify
	*
	* @return	string	If there are options, the full BB code param (including the leading "=").
	*/
	protected function build_table_bbcode_param(array $options)
	{
		$output = array();

		foreach ($options AS $name => $value)
		{
			if ($value !== '')
			{
				$output[] = "$name: $value";
			}
		}

		$output = implode(', ', $output);

		return ($output ? "=\"$output\"" : '');
	}

	/**
	* Gets the effective class list for a BB code. A specific suffix is
	* stripped off and a prefix of 'cms_table_' is removed. The class 'wysiwyg_dashes'
	* is always ignored. For any remaining classes that aren't in the parent
	* list are returned in a space-delimited string.
	*
	* @param	string	List of classes applied to this tag
	* @param	string	List of classes applied to any parent tags
	* @param	string	Optional suffix to strip off from each class applied to this tag
	*
	* @return	string	Space-delimited list of remaining classes
	*/
	protected function get_effective_class_list($classes, $parent_classes = '', $suffix = '')
	{
		if ($classes === '')
		{
			return '';
		}

		$classes = array_unique(explode(' ', $classes));

		if ($parent_classes === '')
		{
			$parent_classes = array();
		}
		else
		{
			$parent_classes = array_unique(explode(' ', $parent_classes));
		}

		$output = array();
		foreach ($classes AS $class)
		{
			$class = trim($class);
			if (!$class)
			{
				continue;
			}

			$class = preg_replace(
				array(
					'#' . preg_quote($suffix, '#') . '$#',
					'#^wysiwyg_cms_table_#'
				), '', $class
			);

			if ($class == 'wysiwyg_dashes')
			{
				continue;
			}

			if (!in_array($class, $parent_classes))
			{
				$output[] = $class;
			}
		}

		return implode(' ', $output);
	}

	/**
	* Parses <table> tags. Supports various options. Automatically parses TRs within.
	*
	* @param	string	String containing tag attributes
	* @param	string	Text within tag
	* @param	string	Name of HTML tag. Used if one function parses multiple tags
	* @param	mixed	Extra arguments passed in to parsing call or tag rules
	*/
	protected function parse_tag_table($attributes, $text, $tag_name, $args)
	{
		$options = array();

		if ($class = $this->parse_wysiwyg_tag_attribute('class=', $attributes))
		{
			$options['class'] = $this->get_effective_class_list($class);
		}

		if ($width = $this->parse_wysiwyg_tag_attribute('width=', $attributes))
		{
			$options['width'] = $width;
		}

		if ($align = $this->parse_wysiwyg_tag_attribute('align=', $attributes))
		{
			switch($align)
			{
				case 'center':
				case 'right':
				case 'left':
					$options['align'] = $align;
			}
		}

		$bbcode_param = $this->build_table_bbcode_param($options);

		$text = $this->parse_tag_by_name('table', $text);
		$text = $this->parse_tag_by_name('tr', $text, array('table_options' => $options));

		return "[TABLE{$bbcode_param}]\n" . $text . "[/TABLE]\n";
	}

	/**
	* Parses <tr> tags. Supports various options. Automatically parses TDs within.
	* Arguments passed in are usually the options applied to the parent table tag.
	*
	* @param	string	String containing tag attributes
	* @param	string	Text within tag
	* @param	string	Name of HTML tag. Used if one function parses multiple tags
	* @param	mixed	Extra arguments passed in to parsing call or tag rules
	*/
	protected function parse_tag_tr($attributes, $text, $tag_name, $args)
	{
		$options = array();

		$style = $this->parse_wysiwyg_tag_attribute('style=', $attributes);
		$style = preg_replace(
			'#color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)(;?)#ie',
			'sprintf("color: #%02X%02X%02X$4", $1, $2, $3)',
			$style
		);

		if ($class = $this->parse_wysiwyg_tag_attribute('class=', $attributes))
		{
			if (!empty($args['table_options']) AND !empty($args['table_options']['class']))
			{
				$parent_classes = $args['table_options']['class'];
			}
			else
			{
				$parent_classes = '';
			}

			$options['class'] = $this->get_effective_class_list($class, $parent_classes, '_tr');
		}

		if (preg_match('#background-color:\s*([^;]+);?#i', $style, $match))
		{
			$bgcolor = $match[1];
		}
		else
		{
			$bgcolor = $this->parse_wysiwyg_tag_attribute('bgcolor=', $attributes);
		}

		if ($bgcolor)
		{
			$options['bgcolor'] = $bgcolor;
		}

		$bbcode_param = $this->build_table_bbcode_param($options);

		if (!is_array($args))
		{
			$args = array();
		}
		$args['tr_options'] = $options;

		$text = $this->parse_tag_by_name('td', $text, $args);

		return "[TR{$bbcode_param}]\n" . $text . "[/TR]\n";
	}

	/**
	* Parses <tr> tags. Supports various options. Arguments passed in are
	* usually the options applied to the parent table and tr tags.
	*
	* @param	string	String containing tag attributes
	* @param	string	Text within tag
	* @param	string	Name of HTML tag. Used if one function parses multiple tags
	* @param	mixed	Extra arguments passed in to parsing call or tag rules
	*/
	protected function parse_tag_td($attributes, $text, $tag_name, $args)
	{
		$options = array();

		$style = $this->parse_wysiwyg_tag_attribute('style=', $attributes);
		$style = preg_replace(
			'#color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)(;?)#ie',
			'sprintf("color: #%02X%02X%02X$4", $1, $2, $3)',
			$style
		);

		if ($class = $this->parse_wysiwyg_tag_attribute('class=', $attributes))
		{
			$parent_classes = '';

			if (!empty($args['table_options']) AND !empty($args['table_options']['class']))
			{
				$parent_classes .= ' ' . $args['table_options']['class'];
			}

			if (!empty($args['tr_options']) AND !empty($args['tr_options']['class']))
			{
				$parent_classes .= ' ' . $args['tr_options']['class'];
			}

			$options['class'] = $this->get_effective_class_list($class, trim($parent_classes), '_td');
		}

		if ($width = $this->parse_wysiwyg_tag_attribute('width=', $attributes))
		{
			$options['width'] = $width;
		}

		if (preg_match('#background-color:\s*([^;]+);?#i', $style, $match))
		{
			$bgcolor = $match[1];
		}
		else
		{
			$bgcolor = $this->parse_wysiwyg_tag_attribute('bgcolor=', $attributes);
		}

		if ($bgcolor)
		{
			$options['bgcolor'] = $bgcolor;
		}

		if ($colspan = $this->parse_wysiwyg_tag_attribute('colspan=', $attributes))
		{
			$options['colspan'] = $colspan;
		}

		if ($align = $this->parse_wysiwyg_tag_attribute('align=', $attributes))
		{
			switch($align)
			{
				case 'center':
				case 'right':
				case 'left':
					$options['align'] = $align;
			}
		}

		$bbcode_param = $this->build_table_bbcode_param($options);

		if ($text == "\n")
		{
			$text = '';
		}

		return "[TD{$bbcode_param}]" . $text . "[/TD]\n";
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 29533 $
|| ####################################################################
\*======================================================================*/