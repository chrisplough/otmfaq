<?php
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

/**
* Base class for overriding the behavior of the editor creation function.
* This class allows overriding of templates, settings, editor type,
* and WYSIWYG parsing.
*
* An instance of (a child of) this class should be passed to construct_editor_toolbar().
*
* @package	vBulletin
*/
abstract class vB_Editor_Override
{
	/**
	* Registry object
	*
	* @var	vB_Registry
	*/
	protected $registry;

	/**
	* Template to use when there's an editor toolbar.
	*
	* @var	string
	*/
	public $template_toolbar_on = 'editor_toolbar_on';

	/**
	* Template to use when there's no editor toolbar.
	*
	* @var	string
	*/
	public $template_toolbar_off = 'editor_toolbar_off';

	/**
	* Default editor type options. See get_default_type_options().
	*
	* @var	array
	*/
	protected $default_type_options = array();

	/**
	* Constructor. Initializes default options as well.
	*
	* @param	vB_Registry
	*/
	public function __construct(vB_Registry $registry)
	{
		$this->registry = $registry;
		$this->default_type_options = $this->get_default_type_options();
	}

	/**
	* Get the settings that control this editor settings (on/of). Returned array
	* has keys of can_toolbar and allow_custom_bbcode. Globalize and manipulate
	* $show here directly if needed.
	*
	* @return	array
	*/
	public function get_editor_settings()
	{
		return array(
			'can_toolbar' => true,
			'allow_custom_bbcode' => true
		);
	}

	/**
	* Get settings that control the editor's "type". These are non-setting-like
	* items that need to be configured.
	*
	* @param	array	Set of options. Keys: force_editorid, editor_count, editor_type, toolbar_type
	*
	* @return	array	Editor type options to use. Keys: editor_id, editor_template_name, editor_height
	*/
	public function get_editor_type(array $options = null)
	{
		$options = (is_array($options)
			? array_merge($this->default_type_options, $options)
			: $this->default_type_options
		);

		return array(
			'editor_id' => $this->get_editor_id($options['force_editorid'], $options['editor_count']),
			'editor_template_name' => $this->get_editor_template($options['toolbar_type']),
			'editor_height' => $this->get_editor_height()
		);
	}

	/**
	* Method to control how to parse the text for the WYSIWYG editor.
	*
	* @param	string	Text
	* @param	array	Options
	*
	* @return	string	Text parsed for WYSIWYG editor
	*/
	abstract public function parse_for_wysiwyg($text, array $options = null);

	/**
	* Gets the string that's used for the parsing (editor) type. Examples: nonforum, calendar. # of forum ID.
	* With this object being used, this likely isn't a significant value.
	*
	* @return	string
	*/
	abstract public function get_parse_type();

	/**
	* The defaults for editor type options.
	*
	* @return	array
	*/
	protected function get_default_type_options()
	{
		return array(
			'force_editorid' => '',
			'editor_count' => 1,
			'editor_type' => 'fe',
			'toolbartype' => 1
		);
	}

	/**
	* Gets the editor ID to be used. Some implementations may ignore the input
	* and use a hardcoded value.
	*
	* @param	string	Editor ID to force
	* @param	integer	A count of editors instantiated
	*
	* @return	string	Editor ID to use
	*/
	protected function get_editor_id($force_editorid, $editor_count)
	{
		if ($force_editorid == '')
		{
			return 'vB_Editor_' . str_pad($editor_count, 3, 0, STR_PAD_LEFT);
		}
		else
		{
			return $force_editorid;
		}
	}

	/**
	* Gets the editor template name based on toolbar type.
	*
	* @param	boolean	If true, template with toolbar, else template without toolbar.
	*
	* @return	string	Template name
	*/
	protected function get_editor_template($toolbar_type)
	{
		return ($toolbar_type ? $this->template_toolbar_on : $this->template_toolbar_off);
	}

	/**
	* Gets the height for the editor. By default, reads from a cookie and
	* adjusts based on that. Alternative implementations may have a hardcoded height.
	*
	* @return	integer
	*/
	protected function get_editor_height()
	{
		$editor_height = $this->registry->input->clean_gpc('c', 'editor_height', TYPE_UINT);
		return ($editor_height > 100) ? $editor_height : 250;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 29998 $
|| ####################################################################
\*======================================================================*/