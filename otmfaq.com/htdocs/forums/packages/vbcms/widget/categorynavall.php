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
 * vBCms_Widget_Nav
 *
 * @package
 * @author ebrown
 * @copyright Copyright (c) 2009
 * @version $Id: categorynavall.php 37602 2010-06-18 18:37:15Z ksours $
 * @access public
 */
class vBCms_Widget_CategoryNavAll extends vBCms_Widget
{
	/**** There are at the time of this writing two category navigation widgets. One
	 * displays categories for the current section and all sections above it in the
	 * section hierarchy. We call that "bottom up". The other displays for the current
	 * section and all sections that descend from it. "Top-Down".
	 *
	 * This is the Top Down widget.
	 *
	 * Note that neither widget currently displays the categories with hierarchy. Although
	 * categories are stored as a hierarchy, they are displayed as flat.
	 ****/

	/*Properties====================================================================*/

	/**
	 * A package identifier.
	 * This is used to resolve any related class names.
	 * It is also used by client code to resolve the class name of this widget.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * A class identifier.
	 * This is used to resolve any related class names.
	 * It is also used by client code to resolve the class name of this widget.
	 *
	 * @var string
	 */
	protected $class = 'CategoryNavAll';

	/** The id of the section for which we are displaying content. ***/
	protected $sectionid;


	/*** cache lifetime, minutes ****/
	protected $cache_ttl = 1440;


	/*Render========================================================================*/

	/**
	 * Returns the config view for the widget.
	 *
	 * @return vBCms_View_Widget				- The view result
	 */
	public function getConfigView($widget = false)
	{
		$this->assertWidget();

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'do'      => vB_Input::TYPE_STR,
			'template_name'    => vB_Input::TYPE_STR
			));

		$view = new vB_View_AJAXHTML('cms_widget_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_widget_x', $this->widget->getTitle());
		$config = $this->widget->getConfig();
		$widgetdm = $this->widget->getDM();

		if ((vB::$vbulletin->GPC['do'] == 'config') AND $this->verifyPostId())
		{
			if (vB::$vbulletin->GPC_exists['template_name'])
			{
				$config['template_name'] = vB::$vbulletin->GPC['template_name'];
			}

			if ($this->content)
			{
				$widgetdm->setConfigNode($this->content->getNodeId());
			}

			$widgetdm->set('config', $config);
			$widgetdm->save();

			if (!$widgetdm->hasErrors())
			{
				if ($this->content)
				{
					$segments = array('node' => $this->content->getNodeURLSegment(),
										'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'EditPage'));
					$view->setUrl(vB_View_AJAXHTML::URL_FINISHED, vBCms_Route_Content::getURL($segments));
				}

				$view->setStatus(vB_View_AJAXHTML::STATUS_FINISHED, new vB_Phrase('vbcms', 'configuration_saved'));
			}
			else
			{
				if (vB::$vbulletin->debug)
				{
					$view->addErrors($widgetdm->getErrors());
				}

				// only send a message
				$view->setStatus(vB_View_AJAXHTML::STATUS_MESSAGE, new vB_Phrase('vbcms', 'configuration_failed'));
			}
		}
		$configview = $this->createView('config');

		if (!isset($config['template_name']) OR ($config['template_name'] == '') )
		{
			$config['template_name'] = 'vbcms_widget_categorynav_page';
		}
		// add the config content
		$configview->template_name = $config['template_name'];


		// item id to ensure form is submitted to us
		$this->addPostId($configview);

		$view->setContent($configview);

		// send the view
		$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_widget'));

		return $view;
	}


	/**
	 * Fetches the standard page view for a widget.
	 *
	 * @param bool $skip_errors					- If using a collection, omit widgets that throw errors
	 * @return vBCms_View_Widget				- The resolved view, or array of views
	 */
	public function getPageView()
	{
		$this->assertWidget();

		$config = $this->widget->getConfig();
		if (!isset($config['template_name']) OR ($config['template_name'] == '') )
		{
			$config['template_name'] = 'vbcms_widget_categorynav_page';
		}

		// Create view
		$view = new vBCms_View_Widget($config['template_name']);
		$this->sectionid = $this->content->getContentTypeId() == vb_Types::instance()->getContentTypeID("vBCms_Section") ?
			$this->content->getNodeId() : $this->content->getParentId();

		try
		{
			$categoryid = max(1, intval(vB_Router::getSegment('value')));
		}
		catch (vB_Exception_Router $e)
		{
			$categoryid = 0;
		}

		$nodes = vBCms_ContentManager::getAllCategories();
		ksort($nodes);

		foreach ($nodes as $nodeid => $record)
		{
			$route = vB_Route::create('vBCms_Route_List', "category/" . $record['route_info'] . "/1")->getCurrentURL();
			$nodes[$nodeid]['view_url'] = $route;

		}
		// Modify $nodes to add myself var (currently selected category)


		$view->widget_title = $this->widget->getTitle();
		$view->nodes = $nodes;
		return $view;
	}

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 37602 $
|| ####################################################################
\*======================================================================*/