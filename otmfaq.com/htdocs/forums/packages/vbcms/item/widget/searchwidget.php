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
 * Test Widget Item
 *
 * @package vBulletin
 * @author Edwin Brown, vBulletin Development Team
 * @version $Revision: 34955 $
 * @since $Date: 2010-01-13 15:30:49 -0800 (Wed, 13 Jan 2010) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Widget_Searchwidget extends vBCms_Item_Widget
{
	/*Properties====================================================================*/


	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'Searchwidget';

	/** The default configuration **/
	protected $config = array(
		'days'          => 7,
		'keywords'      => '',
		'count'         => 10,
		'friends'       => 0,
		'username'      => '',
		'friends'       => 0,
		'childforums'   => 1,
		'tag'           => '',
		'contenttypeid' => array(),
		'group'         =>  '',
		'forumchoice'   =>  array(),
		'cat'           => array(),
		'prefixchoice'  => array(),
		'template'      => '',
		'template_name' => 'vbcms_widget_searchwidget_page',
		'type_info'     => array(),
	);

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 34955 $
|| ####################################################################
\*======================================================================*/