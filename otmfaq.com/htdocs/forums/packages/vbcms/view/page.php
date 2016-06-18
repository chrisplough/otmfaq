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
 * CMS Default View
 * View for rendering the default page controller output.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: $
 * @since $Date: $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_View_Page extends vB_View
{
	/*Properties====================================================================*/

	/**
	 * Evaluated legacy navbits from breadcrumbinfo.
	 *
	 * @var array string						- Assoc array of URL => Title
	 */
	protected $navbits;

	/**
	 * Array of breadcrumbinfo to use for navbits.
	 *
	 * @var array string
	 */
	protected $breadcrumbinfo = array();



	/*Render========================================================================*/

	/**
	 * Prepare the widget block locations and other info.
	 */
	protected function prepareProperties()
	{
		self::$_templaters[$this->_output_type]->notifyResult('navbar_link');
		self::$_templaters[$this->_output_type]->notifyResult('nav_title');
		self::$_templaters[$this->_output_type]->notifyResult('nav_url');
		self::$_templaters[$this->_output_type]->notifyResult('navbar');
		self::$_templaters[$this->_output_type]->prefetchResources();

		// Prepare breadcrumb
		$this->prepareBreadcrumb();

		// Prepare header, navbar and footer
		$this->prepareLegacyPage();
	}


	/**
	 * Prepares the breadcrumb for the template.
	 */
	protected function prepareBreadCrumb()
	{
		// Set the navbits from the breadcrumbinfo
		$navbits = array();

		foreach ($this->breadcrumbinfo AS $breadcrumb)
		{
			$navbits[$breadcrumb['link']] = $breadcrumb['title'];
		}

		$navbits[''] = $this->pagetitle;
		$this->navbits = construct_navbits($navbits);
	}


	/**
	 * Prepares the legacy output.
	 * Registers the globals required for the legacy output such as the header,
	 * footer and navbar.
	 */
	protected function prepareLegacyPage()
	{
		// Make the legacy globals available to the template
		global $headinclude, $header, $navbar, $footer, $style;


		$this->headinclude = $headinclude;
		$this->header = $header;
		$this->footer = $footer;
		$this->style = $style;
		$this->_properties['pagetitle'] = $this->pagetitle;

		// Add the navbar as a view
		$this->navbar = new vBCms_View_NavBar('navbar');
		$this->navbar->navbits = $this->navbits;
		unset($this->navbits);
	}



	/*Accessors=====================================================================*/

	/**
	 * Sets the breadcrumbinfo.
	 * The breadcrumbinfo is translated to navbits in the legacy page structure.
	 *
	 * @return string
	 */
	public function setBreadcrumbInfo(array $info)
	{
		$this->breadcrumbinfo = $info;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 28709 $
|| ####################################################################
\*======================================================================*/