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

// #############################################################################
/**
* Builds a Javascript line to add a new attachment to the vB_Attachments object
*
* Assumes that all data is cleaned and htmlspecialchars'd
*
* @param	integer	Attachment ID
* @param	string	File name (myattachment.gif etc.)
* @param	string	Filesize (124 KB etc.)
* @param	string	Extension type (gif, jpg etc.)
* @param	string	(Optional) Javascript prefix, such as 'window.opener.'
*
* @return	string
*/
function construct_attachment_add_js($attachmentid, $filename, $filesize, $extension, $prefix = '')
{
	return $prefix . "vB_Attachments.add($attachmentid, '" . addslashes_js($filename) . "', '" . addslashes_js($filesize) . "', '$stylevar[imgdir_attach]/$extension.gif');\n";
}

// #############################################################################
/**
* Returns the maximum compatible editor mode depending on permissions, options and browser
*
* @param	integer	The requested editor mode (-1 = user default, 0 = simple textarea, 1 = standard editor controls, 2 = wysiwyg controls)
* @param	string	Editor type (full = 'fe', quick reply = 'qr')
*
* @return	integer	The maximum possible mode (0, 1, 2)
*/
function is_wysiwyg_compatible($userchoice = -1, $editormode = 'fe')
{
	global $vbulletin;

	// Netscape 4... don't even bother to check user choice as the toolbars won't work
	if (is_browser('netscape') OR is_browser('webtv'))
	{
		return 0;
	}

	// check for a standard setting
	if ($userchoice == -1)
	{
		$userchoice = $vbulletin->userinfo['showvbcode'];
	}

	// unserialize the option if we need to
	if (!is_array($vbulletin->options['editormodes_array']))
	{
		$vbulletin->options['editormodes_array'] = unserialize($vbulletin->options['editormodes']);
	}

	// make sure we have a valid editor mode to check
	switch ($editormode)
	{
		case 'fe':
		case 'qr':
		case 'qe':
			break;
		default:
			$editormode = 'fe';
	}

	// check board options for toolbar permissions
	if ($userchoice > $vbulletin->options['editormodes_array']["$editormode"])
	{
		$choice = $vbulletin->options['editormodes_array']["$editormode"];
	}
	else
	{
		$choice = $userchoice;
	}

	$hook_return = null;
	($hook = vBulletinHook::fetch_hook('editor_wysiwyg_compatible')) ? eval($hook) : false;
	if ($hook_return !== null)
	{
		return $hook_return;
	}

	if ($choice == 2) // attempting to use WYSIWYG, check that we really can
	{
		if (!is_browser('opera') OR is_browser('opera', '9.0'))
		{
			// Check Mozilla Browsers
			if (is_browser('firebird', '0.6.1') OR is_browser('camino', '0.9') OR (is_browser('mozilla', '20030312') AND !is_browser('firebird') AND !is_browser('camino')))
			{
				return 2;
			}
			else if (is_browser('ie', '5.5') AND !is_browser('mac'))
			{
				return 2;
			}
			else if (false AND is_browser('opera', '9.0'))
			{
				return 2;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			// browser is incompatible - return standard toolbar
			return 1;
		}
	}
	else
	{
		// return standard or no toolbar
		return $choice;
	}
}

// #############################################################################
/**
* Prepares the templates for a message editor
*
* @param	string	The text to be initially loaded into the editor
* @param	boolean	Is the initial text HTML (rather than plain text or bbcode)?
* @param	mixed	Forum ID of the forum into which we are posting. Special rules apply for values of 'privatemessage', 'usernote', 'calendar', 'announcement' and 'nonforum'. Can be an object of vB_Editor_Override as well.
* @param	boolean	Allow smilies?
* @param	boolean	Parse smilies in the text of the message?
* @param	boolean	Allow attachments?
* @param	string	Editor type - either 'fe' for full editor or 'qr' for quick reply
* @param	string	Force the editor to use the specified value as its editorid, rather than making one up
* @param	array		Information for the image popup
* @param	array		Content type handled by this editor, used to set specific CSS
*
* @return	string	Editor ID
*/
function construct_edit_toolbar($text = '', $ishtml = false, $forumid = 0, $allowsmilie = true, $parsesmilie = true, $can_attach = false, $editor_type = 'fe', $force_editorid = '', $attachinfo = array(), $content = 'content')
{
	// standard stuff
	global $vbulletin, $vbphrase, $show;
	// templates generated by this function
	global $messagearea, $smiliebox, $disablesmiliesoption, $checked, $vBeditTemplate;
	// misc stuff built by this function
	global $istyles;

	// counter for editorid
	static $editorcount = 0;

	if (is_object($forumid) AND $forumid instanceof vB_Editor_Override)
	{
		$editor_override = $forumid;
	}
	else
	{
		$editor_override = null;
	}

	// determine what we can use
	// this was moved up here as I need the switch to determine if bbcode is enabled
	// to determine if a toolbar is usable
	if ($forumid == 'signature')
	{
		$sig_perms =& $vbulletin->userinfo['permissions']['signaturepermissions'];
		$sig_perms_bits =& $vbulletin->bf_ugp_signaturepermissions;

		$can_toolbar = ($sig_perms & $sig_perms_bits['canbbcode']) ? true : false;

		$show['img_bbcode']   = ($sig_perms & $sig_perms_bits['allowimg']) ? true : false;
		$show['font_bbcode']  = ($sig_perms & $sig_perms_bits['canbbcodefont'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_FONT) ? true : false;
		$show['size_bbcode']  = ($sig_perms & $sig_perms_bits['canbbcodesize'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_SIZE) ? true : false;
		$show['color_bbcode'] = ($sig_perms & $sig_perms_bits['canbbcodecolor'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_COLOR) ? true : false;
		$show['basic_bbcode'] = ($sig_perms & $sig_perms_bits['canbbcodebasic'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_BASIC) ? true : false;
		$show['align_bbcode'] = ($sig_perms & $sig_perms_bits['canbbcodealign'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_ALIGN) ? true : false;
		$show['list_bbcode']  = ($sig_perms & $sig_perms_bits['canbbcodelist'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_LIST) ? true : false;
		$show['code_bbcode']  = ($sig_perms & $sig_perms_bits['canbbcodecode'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_CODE) ? true : false;
		$show['html_bbcode']  = ($sig_perms & $sig_perms_bits['canbbcodehtml'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_HTML) ? true : false;
		$show['php_bbcode']   = ($sig_perms & $sig_perms_bits['canbbcodephp'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_PHP) ? true : false;
		$show['url_bbcode']   = ($sig_perms & $sig_perms_bits['canbbcodelink'] AND $vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_URL) ? true : false;
		$show['quote_bbcode'] = ($sig_perms & $sig_perms_bits['canbbcodequote']) ? true : false;
	}
	else
	{
		require_once(DIR . '/includes/class_bbcode.php');
		$show['font_bbcode']  = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_FONT)  ? true : false;
		$show['size_bbcode']  = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_SIZE)  ? true : false;
		$show['color_bbcode'] = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_COLOR) ? true : false;
		$show['basic_bbcode'] = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_BASIC) ? true : false;
		$show['align_bbcode'] = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_ALIGN) ? true : false;
		$show['list_bbcode']  = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_LIST)  ? true : false;
		$show['code_bbcode']  = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_CODE)  ? true : false;
		$show['html_bbcode']  = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_HTML)  ? true : false;
		$show['php_bbcode']   = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_PHP)   ? true : false;
		$show['url_bbcode']   = ($vbulletin->options['allowedbbcodes'] & ALLOW_BBCODE_URL)   ? true : false;
		$show['quote_bbcode'] = true; // can't disable this anywhere but in sigs
	}

	$ajax_extra = '';

	$allow_custom_bbcode = true;

	if (empty($forumid))
	{
		$forumid = 'nonforum';
	}
	switch($forumid)
	{
		case 'privatemessage':
			$can_toolbar = $vbulletin->options['privallowbbcode'];
			$show['img_bbcode'] = $vbulletin->options['privallowbbimagecode'];
			break;

		case 'usernote':
			$can_toolbar = $vbulletin->options['unallowvbcode'];
			$show['img_bbcode'] = $vbulletin->options['unallowimg'];
			break;

		case 'calendar':
			global $calendarinfo;
			$can_toolbar = $calendarinfo['allowbbcode'];
			$show['img_bbcode'] = $calendarinfo['allowimgcode'];
			$ajax_extra = "calendarid=$calendarinfo[calendarid]";
			break;

		case 'announcement':
			$can_toolbar = true;
			$show['img_bbcode'] = true;
			break;

		case 'signature':
			// see above -- these are handled earlier
			break;

		case 'visitormessage':
		case 'groupmessage':
		case 'picturecomment':
		{
			switch($forumid)
			{
				case 'groupmessage':
					$allowedoption = $vbulletin->options['sg_allowed_bbcode'];
				break;

				case 'picturecomment':
					$allowedoption = $vbulletin->options['pc_allowed_bbcode'];
				break;

				default:
					$allowedoption = $vbulletin->options['vm_allowed_bbcode'];
				break;
			}

			$show['font_bbcode']  = ($show['font_bbcode']  AND $allowedoption & ALLOW_BBCODE_FONT)  ? true : false;
			$show['size_bbcode']  = ($show['size_bbcode']  AND $allowedoption & ALLOW_BBCODE_SIZE)  ? true : false;
			$show['color_bbcode'] = ($show['color_bbcode'] AND $allowedoption & ALLOW_BBCODE_COLOR) ? true : false;
			$show['basic_bbcode'] = ($show['basic_bbcode'] AND $allowedoption & ALLOW_BBCODE_BASIC) ? true : false;
			$show['align_bbcode'] = ($show['align_bbcode'] AND $allowedoption & ALLOW_BBCODE_ALIGN) ? true : false;
			$show['list_bbcode']  = ($show['list_bbcode']  AND $allowedoption & ALLOW_BBCODE_LIST)  ? true : false;
			$show['code_bbcode']  = ($show['code_bbcode']  AND $allowedoption & ALLOW_BBCODE_CODE)  ? true : false;
			$show['html_bbcode']  = ($show['html_bbcode']  AND $allowedoption & ALLOW_BBCODE_HTML)  ? true : false;
			$show['php_bbcode']   = ($show['php_bbcode']   AND $allowedoption & ALLOW_BBCODE_PHP)   ? true : false;
			$show['url_bbcode']   = ($show['url_bbcode']   AND $allowedoption & ALLOW_BBCODE_URL)   ? true : false;
			$show['quote_bbcode'] = ($show['quote_bbcode'] AND $allowedoption & ALLOW_BBCODE_QUOTE) ? true : false;
			$show['img_bbcode']   = ($allowedoption & ALLOW_BBCODE_IMG) ? true : false;

			$can_toolbar = (
				$show['font_bbcode'] OR $show['size_bbcode'] OR $show['color_bbcode'] OR
				$show['basic_bbcode'] OR $show['align_bbcode'] OR $show['list_bbcode'] OR
				$show['code_bbcode'] OR $show['html_bbcode'] OR $show['php_bbcode'] OR
				$show['url_bbcode'] OR $show['quote_bbcode'] OR $show['img_bbcode']
			);

			$allow_custom_bbcode = ($allowedoption & ALLOW_BBCODE_CUSTOM ? true : false);
		}
		break;

		case 'nonforum':
			$can_toolbar = $vbulletin->options['allowbbcode'];
			$show['img_bbcode'] = $vbulletin->options['allowbbimagecode'];
			break;

		default:
			if ($editor_override)
			{
				$editor_settings = $editor_override->get_editor_settings();

				$can_toolbar = $editor_settings['can_toolbar'];
				$allow_custom_bbcode = $editor_settings['allow_custom_bbcode'];
				// note: set $show variables directly as necessary in your get_editor_settings function
			}
			else if (intval($forumid))
			{
				$forum = fetch_foruminfo($forumid);
				$can_toolbar = $forum['allowbbcode'];
				$show['img_bbcode'] = $forum['allowimages'];
			}
			else
			{
				$can_toolbar = false;
				$show['img_bbcode'] = false;
			}

			($hook = vBulletinHook::fetch_hook('editor_toolbar_switch')) ? eval($hook) : false;
			break;
	}

	// set the editor mode
	if (isset($_REQUEST['wysiwyg']))
	{
		// 2 = wysiwyg; 1 = standard
		if ($_REQUEST['wysiwyg'])
		{
			$vbulletin->userinfo['showvbcode'] = 2;
		}
		else if ($vbulletin->userinfo['showvbcode'] == 0)
		{
			$vbulletin->userinfo['showvbcode'] = 0;
		}
		else
		{
			$vbulletin->userinfo['showvbcode'] = 1;
		}
	}
	$toolbartype = $can_toolbar ? is_wysiwyg_compatible(-1, $editor_type) : 0;

	$show['wysiwyg_compatible'] = (is_wysiwyg_compatible(2, $editor_type) == 2);
	$show['editor_toolbar'] = ($toolbartype > 0);

	foreach(array('editor_jsoptions_font', 'editor_jsoptions_size') AS $template)
	{
		$templater = vB_Template::create($template);
		$string = $templater->render(true);
		$fonts = preg_split('#\r?\n#s', $string, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($fonts AS $font)
		{
			if ($template == 'editor_jsoptions_font')
			{
				$templater = vB_Template::create('editor_toolbar_fontname');
					$templater->register('fontname', trim($font));
				$fontnames .= $templater->render(true);
			}
			else
			{
				$templater = vB_Template::create('editor_toolbar_fontsize');
					$templater->register('fontsize', trim($font));
				$fontsizes .= $templater->render(true);
			}
		}
	}

	$templater = vB_Template::create('editor_toolbar_colors');
	$colors = $templater->render();

	switch ($editor_type)
	{
		case 'qr':
			if ($force_editorid == '')
			{
				$editorid = 'vB_Editor_QR';
			}
			else
			{
				$editorid = $force_editorid;
			}

			$editor_height = 100;

			$editor_template_name = 'showthread_quickreply';
			break;

		case 'qr_small':
			if ($force_editorid == '')
			{
				$editorid = 'vB_Editor_QR';
			}
			else
			{
				$editorid = $force_editorid;
			}

			$editor_height = 60;

			$editor_template_name = 'showthread_quickreply';
			break;

		case 'qr_pm':
			if ($force_editorid == '')
			{
				$editorid = 'vB_Editor_QR';
			}
			else
			{
				$editorid = $force_editorid;
			}

			$editor_height = 120;

			$editor_template_name = 'pm_quickreply';
			break;

		case 'qe':
		case 'qenr':
			if ($force_editorid == '')
			{
				$editorid = 'vB_Editor_QE';
			}
			else
			{
				$editorid = $force_editorid;
			}

			$editor_height = 200;

			$editor_template_name = 'postbit_quickedit';
			break;

/*
		case 'qenr':
			if ($force_editorid == '')
			{
				$editorid = 'vB_Editor_QE';
			}
			else
			{
				$editorid = $force_editorid;
			}

			$editor_height = 200;

			$editor_template_name = 'memberinfo_quickedit';
			break;
*/

		default:
			if ($editor_override)
			{
				$editorcount++;

				$editor_info = $editor_override->get_editor_type(array(
					'force_editorid' => $force_editorid,
					'editor_count' => $editorcount,
					'editor_type' => $editor_type,
					'toolbar_type' => $toolbartype
				));

				$editorid = $editor_info['editor_id'];
				$editor_height = $editor_info['editor_height'];
				$editor_template_name = $editor_info['editor_template_name'];
			}
			else
			{
				if ($force_editorid == '')
				{
					$editorid = 'vB_Editor_' . str_pad(++$editorcount, 3, 0, STR_PAD_LEFT);
				}
				else
				{
					$editorid = $force_editorid;
				}

				// set the height of the editor based on the editor_height cookie if it exists
				$editor_height = $vbulletin->input->clean_gpc('c', 'editor_height', TYPE_UINT);
				$editor_height = ($editor_height > 100) ? $editor_height : 250;

				$editor_template_name = ($toolbartype ? 'editor_toolbar_on' : 'editor_toolbar_off');
			}
			break;
	}

	// init the variables used by the templates built by this function
	$vBeditJs = array(
		'normalmode'         => 'false'
	);
	$vBeditTemplate = array(
		'clientscript'       => '',
		'fontfeedback'       => '',
		'sizefeedback'       => '',
		'smiliepopup'        => ''
	);
	$extrabuttons = '';

	($hook = vBulletinHook::fetch_hook('editor_toolbar_start')) ? eval($hook) : false;

	// show a post editing toolbar of some sort
	if ($show['editor_toolbar'])
	{
		if ($can_attach)
		{
			$show['attach'] = true;
		}

		// get extra buttons... experimental at the moment
		$extrabuttons = construct_editor_extra_buttons($editorid, $allow_custom_bbcode);

		if ($toolbartype == 2 OR (defined('VB_API') AND VB_API === true))
		{
			// got to parse the message to be displayed from bbcode into HTML
			if ($text !== '')
			{
				if ($editor_override)
				{
					$newpost['message'] = $editor_override->parse_for_wysiwyg($text, array(
						'allowsmilies' => ($allowsmilie AND $parsesmilie),
						'ishtml' => $ishtml
					));
				}
				else
				{
					require_once(DIR . '/includes/functions_wysiwyg.php');
					$newpost['message'] = parse_wysiwyg_html($text, $ishtml, $forumid, iif($allowsmilie AND $parsesmilie, 1, 0));
				}
			}
			else
			{
				$newpost['message'] = '';
			}

			$newpost['message'] = htmlspecialchars($newpost['message']);

			if ((defined('VB_API') AND VB_API === true))
			{
				if ($ishtml)
				{
					$newpost['message_bbcode'] = convert_wysiwyg_html_to_bbcode($text);
				}
				else
				{
					$newpost['message_bbcode'] = $text;
				}
			}
		}
		else
		{
			$newpost['message'] = $text;
			// set mode based on cookie set by javascript
			/*$vbulletin->input->clean_gpc('c', COOKIE_PREFIX . 'vbcodemode', TYPE_INT);
			$modechecked[$vbulletin->GPC[COOKIE_PREFIX . 'vbcodemode']] = 'checked="checked"';*/
		}

	}
	else
	{
		// do not show a post editing toolbar
		$newpost['message'] = $text;
	}

	// disable smilies option and clickable smilie
	$show['smiliebox'] = false;
	$smiliebox = '';
	$smiliepopup = '';
	$disablesmiliesoption = '';

	if ($editor_type == 'qr' OR $editor_type == 'qr_small')
	{
		// no smilies
	}
	else if ($allowsmilie AND $show['editor_toolbar'])
	{
		// deal with disable smilies option
		if (!isset($checked['disablesmilies']))
		{
			$vbulletin->input->clean_gpc('r', 'disablesmilies', TYPE_BOOL);
			$checked['disablesmilies'] = iif($vbulletin->GPC['disablesmilies'], 'checked="checked"');
		}
		$templater = vB_Template::create('newpost_disablesmiliesoption');
			$templater->register('checked', $checked);
		$disablesmiliesoption = $templater->render();

		if ($toolbartype AND ($vbulletin->options['smtotal'] > 0 OR $vbulletin->options['wysiwyg_smtotal'] > 0))
		{
			// query smilies
			$smilies = $vbulletin->db->query_read_slave("
				SELECT smilieid, smilietext, smiliepath, smilie.title,
					imagecategory.title AS category
				FROM " . TABLE_PREFIX . "smilie AS smilie
				LEFT JOIN " . TABLE_PREFIX . "imagecategory AS imagecategory USING(imagecategoryid)
				ORDER BY imagecategory.displayorder, imagecategory.title, smilie.displayorder
			");

			// get total number of smilies
			$totalsmilies = $vbulletin->db->num_rows($smilies);

			if ($totalsmilies > 0)
			{
				if ($vbulletin->options['wysiwyg_smtotal'] > 0)
				{
					$show['wysiwygsmilies'] = true;

					// smilie dropdown menu
					$i = 0;
					while ($smilie = $vbulletin->db->fetch_array($smilies))
					{
						if ($prevcategory != $smilie['category'])
						{
							$prevcategory = $smilie['category'];
							$templater = vB_Template::create('editor_smilie_category');
								$templater->register('smilie', $smilie);
							$smiliepopup .= $templater->render();
						}
						if ($i++ < $vbulletin->options['wysiwyg_smtotal'])
						{
							$templater = vB_Template::create('editor_smilie_row');
								$templater->register('smilie', $smilie);
							$smiliepopup .= $templater->render();
						}
						else
						{
							$show['moresmilies'] = true;
							break;
						}
					}
				}
				else
				{
					$show['wysiwygsmilies'] = false;
				}

				// clickable smilie box
				if ($vbulletin->options['smtotal'])
				{
					$vbulletin->db->data_seek($smilies, 0);
					$i = 0;
					$smiliebits = '';
					while ($smilie = $vbulletin->db->fetch_array($smilies) AND $i++ < $vbulletin->options['smtotal'])
					{
						$templater = vB_Template::create('editor_smilie');
							$templater->register('smilie', $smilie);
							$templater->register('editorid', $editorid);
						$smiliebits .= $templater->render();
					}

					$show['moresmilieslink'] = ($totalsmilies > $vbulletin->options['smtotal']);
					$show['smiliebox'] = true;
				}

				$vbulletin->db->free_result($smilies);
			}
		}
		if ($vbulletin->options['smtotal'] > 0)
		{
			$templater = vB_Template::create('editor_smiliebox');
				$templater->register('editorid', $editorid);
				$templater->register('smiliebits', $smiliebits);
				$templater->register('totalsmilies', $totalsmilies);
			$smiliebox = $templater->render();
		}
		else
		{
			$smiliebox = '';
		}
	}

	($hook = vBulletinHook::fetch_hook('editor_toolbar_end')) ? eval($hook) : false;

	$templater = vB_Template::create('editor_clientscript');
		$templater->register('vBeditJs', $vBeditJs);
		$templater->register('attachinfo', $attachinfo);
		$values = '';
		if (!empty($attachinfo['values']))
		{
			foreach($attachinfo['values'] AS $key => $value)
			{
				$values .= "
					$key: '" . addslashes_js($value) . "',
				";
			}
		}
		$templater->register('values', $values);
	$vBeditTemplate['clientscript'] = $templater->render();

	$ajax_extra = addslashes_js($ajax_extra);
	$editortype = ($toolbartype == 2 ? 1 : 0);
	$show['is_wysiwyg_editor'] = intval($editortype);

	$templater = vB_Template::create($editor_template_name);
		$templater->register('extrabuttons', $extrabuttons);
		$templater->register('ajax_extra', $ajax_extra);
		$templater->register('editorid', $editorid);
		$templater->register('editortype', $editortype);
		$templater->register('editor_height', $editor_height);
		$templater->register('forumid', $editor_override ? $editor_override->get_parse_type() : $forumid);
		$templater->register('istyles', $istyles);
		$templater->register('newpost', $newpost);
		$templater->register('parsesmilie', $parsesmilie);
		$templater->register('smiliebox', $smiliebox);
		$templater->register('vBeditTemplate', $vBeditTemplate);
		$templater->register('fontnames', $fontnames);
		$templater->register('fontsizes', $fontsizes);
		$templater->register('colors', $colors);
		$templater->register('smiliepopup', $smiliepopup);
		$templater->register('attachinfo', $attachinfo);
		$templater->register('content', $content);
	$messagearea = $templater->render();

	return $editorid;
}

// #############################################################################
/**
* Returns the extra buttons as defined by the bbcode editor
*
* @param	string	ID of the editor of which these buttons will be a part
* @param 	boolean	Set to false to disable custom bbcode buttons
*
* @return	string	Extra buttons HTML
*/
function construct_editor_extra_buttons($editorid, $allow_custom_bbcode = true)
{
	global $vbphrase, $vbulletin;

	$extrabuttons = array();

	if ($allow_custom_bbcode and isset($vbulletin->bbcodecache))
	{
		foreach ($vbulletin->bbcodecache AS $bbcode)
		{
			if ($bbcode['buttonimage'] != '')
			{
				$bbcode['tag'] = strtoupper($bbcode['bbcodetag']);
				$extrabuttons[] = $bbcode;
			}
		}
	}

	return $extrabuttons;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 40651 $
|| ####################################################################
\*======================================================================*/
?>