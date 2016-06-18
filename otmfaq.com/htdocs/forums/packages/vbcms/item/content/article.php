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
 * CMS Article Content Item
 * The model item for CMS articles.
 *
 * @author vBulletin Development Team
 * @version $Revision: 29171 $
 * @since $Date: 2009-01-19 02:05:50 +0000 (Mon, 19 Jan 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Content_Article extends vBCms_Item_Content
{
	/*Properties====================================================================*/

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'Article';

	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * The DM for handling CMS Article data.
	 *
	 * @var string
	 */
	protected $dm_class = 'vBCms_DM_Article';

	/**
	 * Map of query => info.
	 * Include INFO_CONTENT in QUERY_BASIC.
	 *
	 * @var array int => int
	 */
	protected $query_info = array(
		self::QUERY_BASIC => /* self::INFO_BASIC | self::INFO_NODE | self::INFO_DEPTH | self::INFO_CONTENT */ 39,
		self::QUERY_PARENTS => self::INFO_PARENTS,
		self::QUERY_CONFIG => self::INFO_CONFIG
	);

	/*ModelProperties===============================================================*/

	/**
	 * Node model properties.
	 *
	 * @var array string
	 */
	protected $content_properties = array(
		/*INFO_CONTENT================*/
			'pagetext',	'threadid' , 'blogid', 'posttitle' ,
			'postauthor', 'poststarter', 'postid', 'blogpostid', 'showrating', 'htmlstate',
			'post_posted', 'post_started', 'previewimage', 'imagewidth', 'imageheight', 'previewvideo'
	);

	/*INFO_CONTENT================*/

	/**
	 * The raw pagetext of the article.
	 *
	 * @var string
	 */
	protected $pagetext;

	/**htmlstate ****/
	protected $htmlstate;

	/**threadid, if it was promoted from a thread ****/
	protected $threadid;

	/**blog post id, if it was promoted from a blog ****/
	protected $blogid;

	/**blog or thread title, if it was promoted from a thread or blog****/
	protected $posttitle;

	/**userid of the original blog or thread author, if it was promoted from a thread or blog ****/
	protected $postauthor;

	/**original blog or thread author, if it was promoted from a thread or blog ****/
	protected $poststarter;

	/**original postid, if it was promoted from a post ****/
	protected $postid;

	/**original blog comment id, if it was promoted from a blog comment****/
	protected $blogpostid;

	/**original post started date if it was a blog or post comment****/
	protected $post_started;

	/**original post date if it was a blog or post comment****/
	protected $post_posted;

	protected $previewimage;

	protected $imagewidth;

	protected $imageheight;

	protected $previewvideo;

	protected $showrating;

	protected $query_hook = 'vbcms_article_querydata';

	/*LoadInfo======================================================================*/

	/**
	 * Fetches the SQL for loading.
	 *
	 * @param int $required_query				- The required query
	 * @return string
	 */
	protected function getLoadQuery($required_query)
	{
		// Hooks should check the required query before populating the hook vars
		$hook_query_fields = $hook_query_joins = $hook_query_where = '';
		($hook = vBulletinHook::fetch_hook($this->query_hook)) ? eval($hook) : false;

		if (self::QUERY_BASIC == $required_query OR self::QUERY_CONTENT == $required_query)
		{
			 $sql = "SELECT node.nodeid, node.showrating, node.setpublish,
						node.contenttypeid, node.contentid, node.url, node.parentnode, node.styleid, node.userid, node.nodeleft, node.noderight,
						node.layoutid, node.publishdate, node.publicpreview, node.issection, node.permissionsfrom, node.showtitle, node.showuser, node.showpreviewonly,
						node.showupdated, node.showviewcount, node.showpublishdate, node.settingsforboth, node.includechildren, node.showall, node.editshowchildren,
						node.shownav, node.hidden, node.nosearch, node.comments_enabled,
						info.description, info.html_title, info.title, info.viewcount, info.creationdate, info.workflowdate, info.associatedthreadid,
					 	info.workflowstatus, info.workflowcheckedout, info.viewcount, info.workflowlevelid, info.keywords, info.ratingnum, info.ratingtotal, info.rating,
						user.username, article.pagetext, article.threadid, article.blogid,
					article.posttitle, article.postauthor, thread.replycount, article.poststarter,
					article.previewimage, article.imagewidth, article.imageheight, article.previewvideo,
					article.postid, article.blogpostid, article.post_started, article.post_posted, article.htmlstate
					 $hook_query_fields
				FROM " . TABLE_PREFIX . "cms_node AS node
				INNER JOIN " . TABLE_PREFIX . "cms_article AS article ON article.contentid = node.contentid
				INNER JOIN " . TABLE_PREFIX . "cms_nodeinfo AS info ON info.nodeid = node.nodeid
				LEFT JOIN " . TABLE_PREFIX . "user AS user ON user.userid = node.userid
				LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON thread.threadid = info.associatedthreadid
				$hook_query_joins
				WHERE " .
				(is_numeric($this->itemid) ? 'node.nodeid = ' . intval($this->nodeid)
										: 'node.contenttypeid = ' . intval($this->contenttypeid) . ' AND node.contentid = ' . intval($this->contentid)) . "
				$hook_query_where
			";
			return $sql;
		}

		return parent::getLoadQuery($required_query);
	}


	/*Accessors=====================================================================*/

	/**
	 * Fetches the article pagetext.
	 * Note: It's the responsibility of the client code to parse the bbcode.
	 *
	 * @return string
	 */
	public function getPageText()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->pagetext;
	}

	/**
	 * Fetches the article pagetext.
	 * Note: It's the responsibility of the client code to parse the bbcode.
	 *
	 * @return string
	 */
	public function getHtmlState()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->htmlstate;
	}

	/**
	 * If this post was escalated from a post or blog comment, this is the threadid .
	 *
	 * @return string
	 */
	public function getThreadId()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->threadid;
	}

	/**
	 * If this post was escalated from ablog comment, this is blog id.
	 *
	 * @return string
	 */
	public function getBlogId()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->blogid;
	}

	/**
	 * If this post was escalated from a blog comment, this is blog comment id.
	 *
	 * @return string
	 */
	public function getBlogPostId()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->blogpostid;
	}

	/**
	 * If this post was escalated from a forum post, this is the post id.
	 *
	 * @return string
	 */
	public function getPostId()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->postid;
	}

	/**
	 * If this post was escalated from a post or blog comment, this is the id of
	 * whoever originally started the thread.
	 *
	 * @return string
	 */
	public function getPostStarter()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->poststarter;
	}
	/**
	 * If this post was escalated from a post or blog comment, this is the timestamp
	 * of when the post or blog was started
	 *
	 * @return string
	 */
	public function getPostStarted()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->post_started;
	}
	/**
	 * If this post was escalated from a post or blog comment, this is the timestamp
	 * of when the post or blog was posted.
	 *
	 * @return string
	 */
	public function getPostPosted()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->post_posted;
	}
	/**
	 *If this post was escalated from a post or blog comment, this is the title of the original thread.
	 *
	 * @return string
	 */
	public function getPostTitle()
	{
		$this->Load(self::INFO_CONTENT);

		if ($this->canusehtml == -1)
		{
			$this->canusehtml = vBCMS_Permissions::canUseHtml($this->nodeid, $this->contenttypeid, $this->userid);
		}

		if (!$this->canusehtml)
		{
			$this->posttitle = strip_tags($this->posttitle);
		}
		return $this->posttitle;
	}

	/**
	 * If this post was escalated from a post or blog comment, this is who originally started the thread.
	 *
	 * @return string
	 */
	public function getPostAuthor()
	{
		$this->Load(self::INFO_CONTENT);

		if ($this->canusehtml == -1)
		{
			$this->canusehtml = vBCMS_Permissions::canUseHtml($this->nodeid, $this->contenttypeid,
				$this->userid);
		}

		if (!$this->canusehtml)
		{
			$this->postauthor = strip_tags($this->postauthor);
		}

		return $this->postauthor;
	}

	/**** Returns the item contenttypeid
	 *
	 * @return int
	 ****/

	public function getContentTypeId()
	{
		return vb_Types::instance()->getContentTypeID("vBCms_Article");
	}

	/**** returns the item previewtext
	 *
	 * @return string
	 ****/
	public function getPreviewText($forceload = false)
	{
		$context = new vB_Context($this->package . '_' . $this->class . '_previewtext_' . $this->nodeid);
		$hashkey = strval($context);
		if (!$forceload AND ($rendered = vB_Cache::instance()->read($hashkey, true, true)))
		{
			return $rendered;
		}

		require_once DIR . '/includes/functions.php';
		$this->Load(self::INFO_CONTENT);

		//First, parse the bbcode.
		$html_parser = new vBCms_WysiwygHtmlParser(vB::$vbulletin);
		//if we have any video code we need to throw it away. The parser doesn't do that.

		$previewtext = vBCms_ContentManager::makePreviewText($this->pagetext,
			vB::$vbulletin->options['default_cms_previewlength'],
			$this->canUseHtml($this->userid),
			$this->htmlstate);

		vB_Cache::instance()->write($hashkey ,
			$previewtext, 1440, array_merge($this->getCacheEvents(), array($this->getContentCacheEvent())));
		return fetch_censored_text($previewtext) ;

	}
	/**** returns the previewimage value from the database record
	 *
	 * @return string
	 ****/
	public function getPreviewImage()
	{
		$this->Load(self::INFO_CONTENT);
		return $this->previewimage ;
	}

	/**** Returns the preview image width as set in the database records
	 *
	 * @return int
	 ****/
	public function getImageWidth()
	{
		$this->Load(self::INFO_CONTENT);
		return $this->imagewidth ;
	}

	/**** Returns the preview image height as set in the database records
	 *
	 * @return int
	 ****/
	public function getImageHeight()
	{
		$this->Load(self::INFO_CONTENT);
		return $this->imageheight ;
	}

	/**** returns the url of a preview video
	 *
	 * @return string
	 ****/
	public function getPreviewVideo()
	{
		$this->Load(self::INFO_CONTENT);
		return $this->previewvideo ;
	}

	public function getRendered($forceload = false)
	{
		$context = new vB_Context($this->package . '_' . $this->class . '_pagetext_' ,
			array( 'nodeid' => $this->nodeid,
			'permissions' => vB::$vbulletin->userinfo['permissions']['cms']));
		$hashkey = strval($context);

		// API should always forceload because it requires sessionurl
		if (!$forceload AND ($rendered = vB_Cache::instance()->read($hashkey, true, true)) AND !VB_API)
		{
			return $rendered;
		}
		$this->Load(self::INFO_CONTENT);

		$bbcode_parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());
		$bbcode_parser->setCanDownload($this->canDownload());
		$pages = array();
		// Articles will generally have an attachment but they should still keep a counter so that this query isn't always running
		require_once(DIR . '/packages/vbattach/attach.php');
		if ($this->canDownload())
		{
			$viewinfo = array();
			$attach = new vB_Attach_Display_Content(vB::$vbulletin, 'vBCms_Article');
			$attachments = $attach->fetch_postattach(0, $this->nodeid);
			$bbcode_parser->attachments = $attachments;
			$bbcode_parser->unsetattach = true;
		}

		$validpage = true;
		$pageno = 1;

		require_once DIR . '/includes/functions.php';
		while($validpage)
		{
			$bbcode_parser->setOutputPage($pageno);
			$bbcode_parser->attachments = $attachments;

			$pagetext = fetch_censored_text($bbcode_parser->do_parse(
				$this->pagetext,
				vBCMS_Permissions::canUseHtml($this->nodeid, $this->contenttypeid, $this->userid),
				$this->htmlstate
				));

			$validpage = $bbcode_parser->fetchedValidPage();

			if ($pageno == 1)
			{
				$pagelist = $bbcode_parser->getPageTitles();
			}

			if ($validpage)
			{
				$pages[$pageno] = $pagetext;
			}
			


			$pageno++;
		}
		if ($this->canDownload())
		{
			$attach->process_attachments($viewinfo, $bbcode_parser->attachments, false, false, true, false, true);
		}

		$rendered = array('pages' => $pages, 'attachments' => $bbcode_parser->attachments,
			'viewinfo' => $viewinfo, 'pagelist' => $pagelist);
		vB_Cache::instance()->write($hashkey ,
			$rendered, 1440, array_merge($this->getCacheEvents(), array($this->getContentCacheEvent())));
		//If we updated the page text we need to also update the preview.
		$this->getPreviewText(true);
		return $rendered;
	}



	/**
	 * Gets a consistent key for cache events.
	 *
	 * @return array string
	 */
	public function getCacheEvents()
	{
		$events = parent::getCacheEvents();
		if ($thread = $this->getAssociatedThreadId())
		{
			$events[] = "cms_comments_change_$thread";
		}
		return $events;
	}


}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 28694 $
|| ####################################################################
\*======================================================================*/