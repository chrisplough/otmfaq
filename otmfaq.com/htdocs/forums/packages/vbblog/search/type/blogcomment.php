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

/**
 * @package vBulletin
 * @subpackage Search
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 28678 $
 * @since $Date: 2008-12-03 16:54:12 +0000 (Wed, 03 Dec 2008) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . '/vb/search/type.php');
require_once (DIR . '/packages/vbforum/search/result/forum.php');

/**
 * @package vBulletin
 * @subpackage Search
 */
class vBBlog_Search_Type_BlogComment extends vB_Search_Type
{
	public function __construct()
	{
		//make sure that this gets initialized
		global $vbulletin;
		if (!$vbulletin->userinfo['blogcategorypermissions'])
		{
			require_once (DIR . '/includes/blog_functions_shared.php');
			prepare_blog_category_permissions($vbulletin->userinfo, true);
		}
	}

	public function fetch_validated_list($user, $ids, $gids)
	{
		$list = array_fill_keys($ids, false);
		$items = vBBlog_Search_Result_BlogComment::create_array($ids);
		foreach ($items as $id => $item)
		{
			if ($item->can_search($user))
			{
				$list[$id] = $item;
			}
		}

		return array('list' => $list, 'groups_rejected' => array());
	}

	/**
	 * @param unknown_type $id
	 */
	public function create_item($id)
	{
		return vBBlog_Search_Result_BlogComment::create($id);
	}
	/**
	 * You can create from an array also
	 *
	 * @param integer $id
	 * @return object
	 */
	public function create_array($ids)
	{
		return vBBlog_Search_Result_BlogComment::create_array($ids);
	}
	public function listUi($prefs)
	{
		$phrase = new vB_Legacy_Phrase();
		$phrase->add_phrase_groups(array('vbblogglobal', 'vbblogcat'));

		global $vbulletin;
		$template = vB_Template::create('search_input_blogcomment');
		$template->register('securitytoken', $vbulletin->userinfo['securitytoken']);
		$template->register('contenttypeid', $this->get_contenttypeid());

		$prefsettings = array(
			'select'=> array('searchdate', 'beforeafter', 'sortby',
				'titleonly', 'sortorder', 'starteronly'),
			'cb' => array('nocache', 'exactname'),
		 	'value' => array('query', 'searchuser')
		);

		$this->setPrefs($template, $prefs, $prefsettings);
		vB_Search_Searchtools::searchIntroRegisterHumanVerify($template);
		return $template->render();	}


	public function get_display_name()
	{
		return new vB_Phrase('search', 'searchtype_blog_comments');
	}

	public function can_group()
	{
		return true;
	}

	public function group_by_default()
	{
		return true;
	}

	/**
	 * vBForum_Search_Type_SocialGroupMessage::additional_pref_defaults()
	 * Each search type has some responsibilities, one of which is to tell
	 * what are its defaults
	 *
	 * @return array
	 */
	public function additional_pref_defaults()
	{
		return array (
			'sortby'			=> 'dateline'
		);
	}

	protected $package = "vBBlog";
	protected $class = "BlogComment";
	protected $group_package = "vBBlog";
	protected $group_class = "BlogEntry";
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 28678 $
|| ####################################################################
\*======================================================================*/