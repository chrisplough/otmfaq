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

require_once DIR . '/includes/class_editor_override.php' ;

class vBCms_Editor_Override extends vB_Editor_Override
{
	//public $template_toolbar_on = 'editor_toolbar_on';
	public $template_toolbar_on = 'vbcms_editor_toolbar_on';

	/*** type-specific parse function
	* @param string 	the text to be parsed
	* @param array		options
	* ***/
	public function parse_for_wysiwyg($text, array $options = null)
	{
		//this line was copied from parse_wysiwyg_html.  Without it, if we html encode the text
		//we get multiple hmtl encodings.  If we *don't* html encode the text then we get unencoded
		//html in the basic (textarea) editor which causes all kinds of problems.
		$text = unhtmlspecialchars($text, 0);
		
		require_once DIR . '/packages/vbcms/bbcode/wysiwyg.php' ;
		require_once DIR . '/packages/vbcms/bbcode/html.php' ;
		$wysiwyg_parser = new vBCms_BBCode_Wysiwyg($this->registry, vBCms_BBCode_Wysiwyg::fetchCmsTags());

		// todo: options
		return $wysiwyg_parser->do_parse($text, false, true, true, true, true);
	}

	/**** returns the type of parse
	*
	* @return string 
	*
	****/
	public function get_parse_type()
	{
		return 'cms';
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 29998 $
|| ####################################################################
\*======================================================================*/
