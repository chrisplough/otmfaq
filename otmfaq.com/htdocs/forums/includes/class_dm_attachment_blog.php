<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 2.0.2 - Licence Number VBB906673F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2009 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!class_exists('vB_DataManager'))
{
	exit;
}

// Temporary
require_once(DIR . '/includes/functions_file.php');

/**
* Abstract class to do data save/delete operations for ATTACHMENTS.
* You should call the fetch_library() function to instantiate the correct
* object based on how attachments are being stored unless calling the multiple
* datamanager. There is no support for manipulating the FS via the multiple
* datamanager at the present.
*
* @package	vBulletin
* @version	$Revision: 29467 $
* @date		$Date: 2009-02-05 10:35:14 -0600 (Thu, 05 Feb 2009) $
*/
class vB_DataManager_Attachment_Blog extends vB_DataManager
{
	/**
	* Array of recognized and required fields for attachment inserts
	*
	* @var	array
	*/
	var $validfields = array(
		'attachmentid'       => array(TYPE_UINT,     REQ_INCR, VF_METHOD, 'verify_nonzero'),
		'userid'             => array(TYPE_UINT,     REQ_YES),
		'blogid'             => array(TYPE_UINT,     REQ_NO),
		'dateline'           => array(TYPE_UNIXTIME, REQ_AUTO),
		'filename'           => array(TYPE_STR,      REQ_YES, VF_METHOD, 'verify_filename'),
		'filedata'           => array(TYPE_BINARY,   REQ_NO, VF_METHOD),
		'filesize'           => array(TYPE_UINT,     REQ_YES),
		'visible'            => array(TYPE_STR,      REQ_NO),
		'counter'            => array(TYPE_UINT,     REQ_NO),
		'filehash'           => array(TYPE_STR,      REQ_YES, VF_METHOD, 'verify_md5'),
		'posthash'           => array(TYPE_STR,      REQ_NO, VF_METHOD, 'verify_md5_alt'),
		'thumbnail'          => array(TYPE_BINARY,   REQ_NO, VF_METHOD),
		'thumbnail_dateline' => array(TYPE_UNIXTIME, REQ_AUTO),
		'thumbnail_filesize' => array(TYPE_UINT,     REQ_NO),
		'extension'          => array(TYPE_STR,      REQ_YES),
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'blog_attachment';

	/**
	* Storage holder
	*
	* @var  array   Storage Holder
	*/
	var $lists = array();

	/**
	* Switch to control modlog update
	*
	* @var  boolean
	*/
	var $log = true;

	/**
	* Condition template for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the field names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('attachmentid = %1$d', 'attachmentid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function vB_DataManager_Attachment_Blog(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::vB_DataManager($registry, $errtype);

		($hook = vBulletinHook::fetch_hook('blog_attachdata_start')) ? eval($hook) : false;
	}

	/**
	* Fetches the appropriate subclassed based on how attachments are being stored.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*
	* @return	vB_DataManager_Attachment	Subclass of vB_DataManager_Attachment
	*/
	function &fetch_library(&$registry, $errtype = ERRTYPE_ARRAY)
	{

		// Library
		$selectclass = ($registry->options['blogattachfile']) ? 'vB_DataManager_Attachment_Blog_Filesystem' : 'vB_DataManager_Attachment_Blog_Database';
		return new $selectclass($registry, $errtype);
	}

	/**
	* Verify that posthash is either md5 or empty
	* @param	string the md5
	*
	* @return	boolean
	*/
	function verify_md5_alt(&$md5)
	{
		return (empty($md5) OR (strlen($md5) == 32 AND preg_match('#^[a-f0-9]{32}$#', $md5)));
	}

	/**
	* Set the extension of the filename
	*
	* @param	filename
	*
	* @return	boolean
	*/
	function verify_filename(&$filename)
	{
		$this->set('extension', strtolower(substr(strrchr($filename, '.'), 1)));
		return true;
	}

	/**
	* Set the filesize of the thumbnail
	*
	* @param	integer	Maximum posts per page
	*
	* @return	boolean
	*/
	function verify_thumbnail(&$thumbnail)
	{
		if (strlen($thumbnail) > 0)
		{
			$this->set('thumbnail_filesize', strlen($thumbnail));
		}
		return true;
	}

	/**
	* Set the filehash/filesize of the file
	*
	* @param	integer	Maximum posts per page
	*
	* @return	boolean
	*/
	function verify_filedata(&$filedata)
	{
		if (strlen($filedata) > 0)
		{
			$this->set('filehash', md5($filedata));
			$this->set('filesize', strlen($filedata));
		}

		return true;
	}

	/**
	* Saves the data from the object into the specified database tables
	* Overwrites parent
	*
	* @return	mixed	If this was an INSERT query, the INSERT ID is returned
	*/
	function save($doquery = true, $delayed = false)
	{
		if ($this->has_errors())
		{
			return false;
		}

		if (!$this->pre_save($doquery))
		{
			return false;
		}

		if ($this->condition === null)
		{
			$return = $this->db_insert(TABLE_PREFIX, $this->table, $doquery);
			$this->set('attachmentid', $return);
		}
		else
		{
			$return = $this->db_update(TABLE_PREFIX, $this->table, $this->condition, $doquery, $delayed);
		}

		if ($return AND $this->post_save_each($doquery) AND $this->post_save_once($doquery))
		{
			return $return;
		}
		else
		{
			return false;
		}
	}

	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}

		// Update an existing attachment (on insert) of the same name so that it maintains its current attachmentid
		if ($this->condition === null AND $this->fetch_field('filename') AND !$this->fetch_field('blogid'))
		{
			if ($foo = $this->dbobject->query_first("
				SELECT attachmentid
				FROM " . TABLE_PREFIX . "blog_attachment AS blog_attachment
				WHERE filename = '" . $this->dbobject->escape_string($this->fetch_field('filename')) . "'
					AND posthash = '" . $this->dbobject->escape_string($this->fetch_field('posthash')) . "'
					AND userid = " . intval($this->fetch_field('userid')) . "
			"))
			{
				$this->condition = "attachmentid = $foo[attachmentid]";
				$this->existing['attachmentid'] = $foo['attachmentid'];
				$this->set('counter', 0);
				$this->set('blogid', 0);
			}
			// this is an edit
			else if ($this->info['blogid'] AND $foo = $this->dbobject->query_first("
				SELECT attachmentid
				FROM " . TABLE_PREFIX . "blog_attachment AS blog_attachment
				WHERE filename = '" . $this->dbobject->escape_string($this->fetch_field('filename')) . "'
					AND blogid = " . intval($this->info['blogid']) . "
			"))
			{
				$this->condition = "attachmentid = $foo[attachmentid]";
				$this->existing['attachmentid'] = $foo['attachmentid'];
				$this->set('counter', 0);
				$this->set('blogid', 0);
				$this->info['update_existing'] = intval($this->info['blogid']);
			}
		}

		$return_value = true;
		($hook = vBulletinHook::fetch_hook('blog_attachdata_presave')) ? eval($hook) : false;

		$this->presave_called = $return_value;
		return $return_value;
	}

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true)
	{
		if (!empty($this->info['update_existing']))
		{
			// we're updating an existing attachment that has already been counted
			// We need to decrement those fields because they will be incremented on save.
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "blog SET
					attach = IF(attach > 0, attach - 1, 0)
				WHERE blogid = " . intval($this->info['update_existing'])
			);
		}

		($hook = vBulletinHook::fetch_hook('blog_attachdata_postsave')) ? eval($hook) : false;
		return parent::post_save_each($doquery);
	}

	/**
	* Any code to run before deleting. Builds lists and updates mod log
	*
	* @param	Boolean Do the query?
	*/
	function pre_delete($doquery = true)
	{
		@ignore_user_abort(true);

		// init lists
		$this->lists = array(
			'idlist'   => array(),
			'bloglist' => array(),
		);

		$replaced = array();

		$ids = $this->registry->db->query_read("
			SELECT
				blog_attachment.attachmentid,
				blog_attachment.userid,
				blog.blogid,
				blog.firstblogtextid,
				blog.dateline AS blog_dateline,
				blog.userid AS blog_userid
			FROM " . TABLE_PREFIX . "blog_attachment AS blog_attachment
			LEFT JOIN " . TABLE_PREFIX . "blog AS blog ON (blog.blogid = blog_attachment.blogid)
			WHERE " . $this->condition
		);
		while ($id = $this->registry->db->fetch_array($ids))
		{
			$this->lists['idlist']["{$id['attachmentid']}"] = $id['userid'];

			if ($id['blogid'])
			{
				$this->lists['bloglist']["{$id['blogid']}"]++;

				if ($this->log)
				{
					if (($this->registry->userinfo['permissions']['genericoptions'] & $this->registry->bf_ugp_genericoptions['showeditedby']) AND $id['post_dateline'] < (TIMENOW - ($this->registry->options['noeditedbytime'] * 60)))
					{
						if (empty($replaced["$id[blogid]"]))
						{
							/*insert query*/
							$this->registry->db->query_write("
								REPLACE INTO " . TABLE_PREFIX . "blog_editlog
										(blogtextid, userid, username, dateline)
								VALUES
									($id[firstblogtextid], " . $this->registry->userinfo['userid'] . ", '" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "', " . TIMENOW . ")"
							);
							$replaced["$id[blogid]"] = true;
						}
					}

					if ($this->registry->userinfo['userid'] != $id['blog_userid'] AND can_moderate_blog('caneditentries'))
					{
						$bloginfo = array(
							'blogid'       =>& $id['blogid'],
							'attachmentid' =>& $id['attachmentid'],
						);
						require_once(DIR . '/includes/blog_functions_log_error.php');
						blog_moderator_action($bloginfo, 'attachment_removed');
					}
				}
			}
		}

		if ($this->registry->db->num_rows($ids) == 0)
		{	// nothing to delete
			return false;
		}
		else
		{
			// condition needs to have any attachment. replaced with TABLE_PREFIX . attachment
			// since DELETE doesn't suport table aliasing in some versions of MySQL
			// we needed the attachment. for the query run above at the start of this function
			$this->condition = preg_replace('#(blog_attachment\.)#si', TABLE_PREFIX . '\1', $this->condition);
			return true;
		}
	}

	/**
	* Any code to run after deleting
	*
	* @param	Boolean Do the query?
	*/
	function post_delete($doquery = true)
	{
		// A little cheater function..
		if (!empty($this->lists['idlist']) AND $this->registry->options['blogattachfile'])
		{
			require_once(DIR . '/includes/functions_file.php');
			// Delete attachments from the FS
			foreach ($this->lists['idlist'] AS $attachmentid => $userid)
			{
				@unlink(fetch_attachment_path($userid, $attachmentid, false, $this->registry->options['blogattachpath']));
				@unlink(fetch_attachment_path($userid, $attachmentid, true, $this->registry->options['blogattachpath']));
			}
		}

		// Build MySQL CASE Statement to update post/thread attach counters
		// future: examine using subselect option for MySQL 4.1

		foreach($this->lists['bloglist'] AS $blogid => $count)
		{
			$blogidlist .= ",$blogid";
			$blogcasesql .= " WHEN blogid = $blogid THEN $count";
		}

		if ($blogcasesql)
		{
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "blog
				SET attach = attach -
				CASE
					$blogcasesql
					ELSE 0
				END
				WHERE blogid IN (-1$blogidlist)
			");
		}

		($hook = vBulletinHook::fetch_hook('blog_attachdata_delete')) ? eval($hook) : false;
	}
}

/**
* Class to do data save/delete operations for ATTACHMENTS in the DATABASE.
*
* @package	vBulletin
* @version	$Revision: 29467 $
* @date		$Date: 2009-02-05 10:35:14 -0600 (Thu, 05 Feb 2009) $
*/

class vB_DataManager_Attachment_Blog_Database extends vB_DataManager_Attachment_Blog
{
	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/

	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}

		if (!empty($this->info['filedata']))
		{
			$this->setr('filedata', $this->info['filedata']);
		}
		if (!empty($this->info['thumbnail']))
		{
			$this->setr('thumbnail', $this->info['thumbnail']);
		}

		return parent::pre_save($doquery);
	}
}


/**
* Class to do data save/delete operations for ATTACHMENTS in the FILE SYSTEM.
*
* @package	vBulletin
* @version	$Revision: 29467 $
* @date		$Date: 2009-02-05 10:35:14 -0600 (Thu, 05 Feb 2009) $
*/
class vB_DataManager_Attachment_Blog_Filesystem extends vB_DataManager_Attachment_Blog
{
	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}

		// make sure we don't have the binary data set
		// if so move it to an information field
		// benefit of this is that when we "move" files from DB to FS,
		// the filedata/thumbnail fields are not blanked in the database
		// during the update.
		if ($file =& $this->fetch_field('filedata'))
		{
			$this->setr_info('filedata', $file);
			$this->do_unset('filedata');
		}

		if ($thumb =& $this->fetch_field('thumbnail'))
		{
			$this->setr_info('thumbnail', $thumb);
			$this->do_unset('thumbnail');
		}

		if (!empty($this->info['filedata']))
		{
			$this->set('filehash', md5($this->info['filedata']));
			$this->set('filesize', strlen($this->info['filedata']));
		}
		if (!empty($this->info['thumbnail']))
		{
			$this->set('thumbnail_filesize', strlen($this->info['thumbnail']));
		}

		if (!empty($this->info['filedata']) OR !empty($this->info['thumbnail']))
		{
			$path = $this->verify_attachment_path($this->fetch_field('userid'));
			if (!$path)
			{
				$this->error('attachpathfailed');
				return false;
			}

			if (!is_writable($path))
			{
				$this->error('upload_file_system_is_not_writable');
				return false;
			}
		}

		return parent::pre_save($doquery);
	}

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true)
	{

		$attachmentid =& $this->fetch_field('attachmentid');
		$userid =& $this->fetch_field('userid');
		$failed = false;

		// Check for filedata in an information field
		if (!empty($this->info['filedata']))
		{
			$filename = fetch_attachment_path($userid, $attachmentid, false, $this->registry->options['blogattachpath']);
			if ($fp = fopen($filename, 'wb'))
			{
				if (!fwrite($fp, $this->info['filedata']))
				{
					$failed = true;
				}
				fclose($fp);
				#remove possible existing thumbnail in case no thumbnail is written in the next step.
				if (file_exists(fetch_attachment_path($userid, $attachmentid, true, $this->registry->options['blogattachpath'])))
				{
					@unlink(fetch_attachment_path($userid, $attachmentid, true, $this->registry->options['blogattachpath']));
				}
			}
			else
			{
				$failed = true;
			}
		}

		if (!$failed AND !empty($this->info['thumbnail']))
		{
			// write out thumbnail now
			$filename = fetch_attachment_path($userid, $attachmentid, true, $this->registry->options['blogattachpath']);
			if ($fp = fopen($filename, 'wb'))
			{
				if (!fwrite($fp, $this->info['thumbnail']))
				{
					$failed = true;
				}
				fclose($fp);
			}
			else
			{
				$failed = true;
			}
		}

		($hook = vBulletinHook::fetch_hook('blog_attachdata_postsave')) ? eval($hook) : false;

		if ($failed)
		{
			if ($this->condition === null) // Insert, delete attachment
			{
				$this->condition = "attachmentid = $attachmentid";
				$this->log = false;
				$this->delete();
			}

			// $php_errormsg is automatically set if track_vars is enabled
			$this->error('upload_copyfailed', htmlspecialchars_uni($php_errormsg), fetch_attachment_path($userid, 0 , true, $this->registry->options['blogattachpath']));
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Verify that user's attach path exists, create if it doesn't
	*
	* @param	int		userid
	*/
	function verify_attachment_path($userid)
	{
		if (!$userid)
		{
			return false;
		}

		$path = fetch_attachment_path($userid, 0, false, $this->registry->options['blogattachpath']);
		if (vbmkdir($path))
		{
			return $path;
		}
		else
		{
			return false;
		}
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 09:18, Thu Jul 23rd 2009
|| # SVN: $Revision: 29467 $
|| ####################################################################
\*======================================================================*/
?>