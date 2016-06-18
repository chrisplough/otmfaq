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
 * Special Route for the WYSIWYG Editor
 *
 * @author vBulletin Development Team
 * @version $Revision: 31038 $
 * @since $Date: 2009-06-01 01:06:12 +0100 (Mon, 01 Jun 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Route_Editor extends vB_Route
{
	/*Properties====================================================================*/

	/**
	 * The segment scheme
	 *
	 * @see vB_Route::$_segment_scheme
	 *
	 * @var array mixed
	 */
	protected $_segment_scheme = array(
		'action'		=> array (
			'optional' 	=> false,
			'values'	=> array(
							'switch',
							),
			'default'	=> ''
		),
	);

	/**
	 * Default path.
	 *
	 * @var string
	 */
	protected $_default_path = '404';



	/*URL===========================================================================*/

	/**
	 * Returns a representative URL of a route.
	 * Optional segments and parameters may be passed to set the route state.
	 *
	 * @param array mixed $segments				- Assoc array of segment => value
	 * @param array mixed $parameters			- Array of parameter values, in order
	 * @return string							- The URL representing the route
	 */
	public static function getURL(array $segments = null, array $parameters = null, $absolute_path = false)
	{
		return '';
	}



	/*Response======================================================================*/

	/**
	 * Returns the response for the route.
	 *
	 * @return string							- The response
	 */
	public function getResponse()
	{
		if (!$this->_is_valid)
		{
			throw (new vB_Exception_404('Invalid route'));
		}

		if (!($controller = vB_Router::getActionController(get_class($this), $this->action, $this->_parameters)))
		{
			throw (new vB_Exception_404('Invalid action requested'));
		}

		return $controller->getResponse();
	}


	/**
	 * Builds dynamic segment schemes.
	 */
	protected function buildSegmentScheme()
	{
		$actions = vB_Router::getRouteActions(get_class($this));

		$this->_segment_scheme['action']['values'] = $actions;
	}

	public function assertSubdirectoryUrl()
	{
		//logic is shared with the core app
		verify_subdirectory_url(vB::$vbulletin->options['vbcms_url']);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 30055 $
|| ####################################################################
\*======================================================================*/