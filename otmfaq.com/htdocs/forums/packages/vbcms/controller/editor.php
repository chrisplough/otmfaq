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
 * Wysiwyg Editor Controller
 * Provides views for the wysiwyg editor via AJAX.
 *
 * @TODO: Some seperation of view/controller data.
 *
 * @package vBulletin
 * @author Mike Sullivan, vBulletin Development Team
 * @version $Revision: 29533 $
 * @since $Date: 2009-02-12 16:00:09 +0000 (Thu, 12 Feb 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Controller_Editor extends vB_Controller
{
	/*Properties====================================================================*/

	/**
	 * The package that the controller belongs to.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * The class string id that identifies the controller.
	 *
	 * @var string
	 */
	protected $class = 'Editor';

	/**
	 * The action definitions for the controller.
	 *
	 * @var array string => bool
	 */
	protected $actions = array(
		'SwitchMode',
		'TableOverlay'
	);

	/**
	 * A reference to the legacy bootstrap.
	 *
	 * @var vB_Bootstrap
	 */
	protected $bootstrap;


	/*Initialization================================================================*/

	/**
	 * Constructor.
	 *
	 * @param array mixed $parameters			- User requested parameters.
	 * @param string $action					- Optional action for the controller's getResponse()
	 */
	public function __construct($parameters, $action = false)
	{
		parent::__construct($parameters, $action);

		global $bootstrap;

		$this->bootstrap = $bootstrap;

		// Register the templater to be used for XHTML
		vB_View::registerTemplater(vB_View::OT_XHTML, new vB_Templater_vB());
	}



	/*Actions=======================================================================*/

	/**
	 * Gets editor in the selected mode.
	 *
	 * @return string
	 */
	public function actionSwitchMode()
	{
		// Set up the style info - we need charset to be set for convert_urlencoded_unicode
		$this->bootstrap->force_styleid(0);
		$this->bootstrap->load_style();


		require_once DIR . '/includes/class_xml.php';

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'towysiwyg' => vB_Input::TYPE_BOOL,
			'allowsmilie' => vB_Input::TYPE_BOOL,
			'message' => vB_Input::TYPE_STR,
		));

		vB::$vbulletin->GPC['message'] = convert_urlencoded_unicode(vB::$vbulletin->GPC['message']);

		$xml = new vB_AJAX_XML_Builder(vB::$vbulletin, 'text/xml');

		if (vB::$vbulletin->GPC['towysiwyg'])
		{
			$wysiwyg_parser = new vBCms_BBCode_Wysiwyg(vB::$vbulletin, vBCms_BBCode_Wysiwyg::fetchCmsTags());

			// todo: options
			$wysiwyg_html = $wysiwyg_parser->do_parse(vB::$vbulletin->GPC['message'], false, vB::$vbulletin->GPC['allowsmilie'], true, true, true);

			$xml->add_tag('message', process_replacement_vars($wysiwyg_html));
		}
		else
		{
			$html_parser = new vBCms_WysiwygHtmlParser(vB::$vbulletin);
			$do_html = false; // todo: option
			$message = $html_parser->parse(vB::$vbulletin->GPC['message'], $do_html);

			$xml->add_tag('message', process_replacement_vars($message));
		}

		if (!vB::contentHeadersSent())
		{
			$xml->send_content_type_header();
			$xml->send_content_length_header();

			vB::contentHeadersSent(true);
		}

		return $xml->fetch_xml();
	}


	/**
	 * Produces the configuration overlay for table tags.
	 *
	 * @return string
	 */
	public function actionTableOverlay()
	{
		$view = new vB_View_AJAXHTML('vbcms_editor_table_overlay');
		$properties = array(
			'type' => vB::$vbulletin->input->clean_gpc('p', 'type', vB_Input::TYPE_STR)
		);

		$formview = new vB_View('vbcms_editor_table_overlay');
		$formview->addArray($properties);
		$view->setContent($formview);

		// need posting group
		require_once DIR . '/includes/functions_databuild.php' ;
		fetch_phrase_group('posting');

		return $view->render(true);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 30298 $
|| ####################################################################
\*======================================================================*/