<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.1 Patch Level 1 - Licence Number VBS309B87F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
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
 * @version $Revision: 35936 $
 * @since $Date: 2010-03-24 08:50:04 -0700 (Wed, 24 Mar 2010) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Widget_CategoryNavCurrent extends vBCms_Item_Widget
{
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
	protected $class = 'CategoryNavCurrent';

	/** The default configuration **/
	protected $config = array(
		'template_name' => 'vbcms_widget_categorynavcurrent_page',
	);

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 35936 $
|| ####################################################################
\*======================================================================*/