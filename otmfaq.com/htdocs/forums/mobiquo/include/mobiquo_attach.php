<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.0 Patch Level 1 - Licence Number VBF83FEF44
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
defined('IN_MOBIQUO') or exit;
if (!isset($GLOBALS['vbulletin']->db))
{
    exit;
}


class mobiquo_vB_Attachment_Store_Library
{
    /**
    * Singleton emulation
    *
    */
    private static $instance = null;

    /**
    * Select library
    *
    * @return    object
    */
    public static function &fetch_library(&$registry, $contenttypeid, $categoryid, $values)
    {
        if (self::$instance)
        {
            return self::$instance;
        }

        require_once(DIR . '/includes/class_bootstrap_framework.php');
        require_once(DIR . '/vb/types.php');
        vB_Bootstrap_Framework::init();
        $types = vB_Types::instance();

        if (!($contenttypeid = $types->getContentTypeID($contenttypeid)))
        {
            return false;
        }

        $package = $types->getContentTypePackage($contenttypeid);
        $class = $types->getContentTypeClass($contenttypeid);
        
        $selectclass = "mobiquo_vB_Attachment_Store_{$package}_{$class}";
        include_once(CWD1."/include/mobiquo_attach_post.php");
    
        if (class_exists($selectclass))
        {;
            self::$instance = new $selectclass($registry, $contenttypeid, $categoryid, $values);
        }
        else
        {
            exit;
            return false;
        }

        return self::$instance;
    }
}

/**
* Abstracted Attachment storage class
*
* @package         vBulletin
* @version        $Revision: 34246 $
* @date         $Date: 2009-12-09 17:20:25 -0600 (Wed, 09 Dec 2009) $
*
* @abstract
*/
abstract class mobiquo_vB_Attachment_Store
{
    /**
    * Main data registry
    *
    * @var    vB_Registry
    */
    protected $registry = null;

    /**
    *    Array of information specific to this contenttype, needed for permisson checks, etc
    *
    * @var    array
    */
    protected $values = array();

    /**
    *    contentypeid of this object
    *
    * @var    integer
    */
    private $contenttypeid = 0;

    /**
    *    contentid of the attachment owner
    *
    * @var    integer
    */
    protected $contentid = 0;

    /**
    *    Username of content owner
    *
    * @var    string
    */
    protected $content_owner = '';

    /**
    *    Attachment count of this content object
    *
    * @var    integer
    */
    protected $attachcount = 0;

    /**
    *    Upload errors
    *
    * @var    array
    */
    public $errors = array();

    /**
    *    Userinfo of owner of content
    *
    * @var    array
    */
    public $userinfo = array();

    /**
    * Constructor
    * Sets registry
    *
    * @return    void
    */
    public function __construct(&$registry, $contenttypeid, $categoryid, $values)
    {
        $this->registry =& $registry;
        $this->values = $values;
        $this->userinfo = $this->registry->userinfo;
        $this->contenttypeid = $contenttypeid;
        $this->categoryid = $categoryid;
    }

    /**
    * Verify permissions
    *
    * @return    bool
    */
    abstract protected function verify_permissions();

    /**
    * Verify amount of attachments has not exceed maximum number based on permissions
    *  - defaulting to 'Post' type permissions, but can be overidden by subclasses
    *
    * @param    array        Attachment information
    *
    * @return    bool    true if we are under the limit
    */
    protected function verify_max_attachments($attachment)
    {
        global $vbphrase;

        if ($this->registry->options['attachlimit'] AND $this->attachcount > $this->registry->options['attachlimit'])
        {
            $error = construct_phrase($vbphrase['you_may_only_attach_x_files_per_post'], $this->registry->options['attachlimit']);
            $this->errors[] = array(
                'filename' => is_array($attachment) ? $attachment['name'] : $attachment,
                'error'    => $error
            );
            return false;
        }

        return true;
    }

    /**
    * Count new and pre-existing attachments
    *
    * @return    bool
    */
    public function fetch_attachcount()
    {
        $currentattaches = $this->registry->db->query_first("
            SELECT COUNT(*) AS count
            FROM " . TABLE_PREFIX . "attachment
            WHERE
                posthash = '" . $this->registry->db->escape_string($this->values['posthash']) . "'
                    AND
                contenttypeid = " . $this->contenttypeid . "
                " . ($this->contentid ? " AND contentid = 0 "  : "") . "
        ");
        $this->attachcount = $currentattaches['count'];

        if ($this->contentid)
        {
            $currentattaches = $this->registry->db->query_first("
                SELECT COUNT(*) AS count
                FROM " . TABLE_PREFIX . "attachment
                WHERE
                    contentid = " . $this->contentid . "
                        AND
                    contenttypeid = " . $this->contenttypeid . "
            ");
            $this->attachcount += $currentattaches['count'];
            $show['postowner'] = true;
        }
        else
        {
            $show['postowner'] = false;
        }

        return true;
    }

    /**
    * Fetch new and pre-existing attachments
    *
    * @return    object
    */
    public function fetch_attachments()
    {
        $attachments = $this->registry->db->query_read(
            ($this->contentid ? "(" : "") .
                "SELECT
                    a.contentid, a.dateline, a.filename, a.attachmentid,
                    fd.filesize, IF(fd.thumbnail_filesize > 0, 1, 0) AS hasthumbnail, fd.filedataid, fd.userid AS fuserid, fd.extension,
                    fd.width, fd.height, fd.thumbnail_width, fd.thumbnail_height, fd.thumbnail_dateline
                FROM " . TABLE_PREFIX . "attachment AS a
                INNER JOIN " . TABLE_PREFIX . "filedata AS fd ON (a.filedataid = fd.filedataid)
                WHERE
                    a.posthash = '" . $this->registry->db->escape_string($this->values['posthash']) . "'
                        AND
                    a.contenttypeid = " . intval($this->contenttypeid) . "
            " . ($this->contentid ? ") UNION (" : "") . "
            " . ($this->contentid ? "
                SELECT
                    a.contentid, a.dateline, a.filename, a.attachmentid,
                    fd.filesize, IF(fd.thumbnail_filesize > 0, 1, 0) AS hasthumbnail, fd.filedataid, fd.userid AS fuserid, fd.extension,
                    fd.width, fd.height, fd.thumbnail_width, fd.thumbnail_height, fd.thumbnail_dateline
                FROM " . TABLE_PREFIX . "attachment AS a
                INNER JOIN " . TABLE_PREFIX . "filedata AS fd ON (a.filedataid = fd.filedataid)
                WHERE
                    a.contentid = " . intval($this->contentid) . "
                        AND
                    a.contenttypeid = " . intval($this->contenttypeid) . "
            )
            " : "") . "
            ORDER BY attachmentid
        ");

        return $attachments;
    }

    public function delete($ids)
    {
        if (!empty($ids))
        {
            $attachdata =& datamanager_init('Attachment', $this->registry, ERRTYPE_STANDARD, 'attachment');
            $attachids = array_map('intval', array_keys($ids));

            $condition = array(
                "a.attachmentid IN (" . implode(", ", $attachids) . ")",
                "a.contenttypeid = " . intval($this->contenttypeid),
            );
            if ($this->contentid)
            {
                $condition[] = "(a.contentid = " . intval($this->contentid) . " OR a.posthash = '" . $this->registry->db->escape_string($this->values['posthash']) . "')";
            }
            else
            {
                $condition[] = "a.posthash = '" . $this->registry->db->escape_string($this->values['posthash']) . "'";
            }
            $attachdata->condition = implode(" AND ", $condition);
            $attachdata->delete(true, false);
            unset($attachdata);
        }
    }

    public function upload($files, $urls, $filedata)
    {
        $errors = array();
    
        require_once(CWD1.'/include/mobiquo_class_upload.php');
        require_once(DIR . '/includes/class_image.php');
    
        // check for any funny business
        $filecount = 1;
        if (!empty($files['tmp_name']))
        {
            foreach ($files['tmp_name'] AS $filename)
            {
                if (!empty($filename))
                {
                    if ($filecount > $this->registry->options['attachboxcount'])
                    {
                        @unlink($filename);
                    }
                    $filecount++;
                }
            }
        }

        // Move any urls into the attachment array if we allow url upload
        if ($this->registry->options['attachurlcount'])
        {
            $urlcount = 1;
            foreach ($urls AS $url)
            {
                if (!empty($url) AND $urlcount <= $this->registry->options['attachurlcount'])
                {
                    $index = count($files['name']);
                    $files['name']["$index"] = $url;
                    $files['url']["$index"] = true;
                    $urlcount++;
                }
            }
        }

        if (!empty($filedata))
        {
            foreach($filedata AS $filedataid)
            {
                $index = count($files['name']);
                $files['name']["$index"] = 'filedata';
                $files['filedataid']["$index"] = $filedataid;
            }
        }

        //$this->attachcount = 0;
        $ids = array();
        $uploadsum = count($files['name']);
        for ($x = 0; $x < $uploadsum; $x++)
        {
            if (!$files['name']["$x"])
            {
                if ($files['tmp_name']["$x"])
                {
                    @unlink($files['tmp_name']["$x"]);
                }
                continue;
            }

            $attachdata =& $this->fetch_attachdm();

            $upload = new vB_Upload_Attachment($this->registry);
            $upload->contenttypeid = $this->contenttypeid;
            $image =& vB_Image::fetch_library($this->registry);
            $upload->userinfo = $this->userinfo;

            $upload->data =& $attachdata;
            $upload->image =& $image;
            if ($uploadsum > 1)
            {
                $upload->emptyfile = false;
            }

            if ($files['filedataid']["$x"])
            {
                if (!($filedatainfo = $this->registry->db->query_first_slave("
                    SELECT
                        acu.filedataid, acu.filename, fd.filehash, fd.filesize, fd.extension
                    FROM " . TABLE_PREFIX . "attachmentcategoryuser AS acu
                    INNER JOIN " . TABLE_PREFIX . "filedata AS fd ON (acu.filedataid = fd.filedataid)
                    WHERE
                        acu.filedataid = " . intval($files['filedataid']["$x"]) . "
                            AND
                        acu.userid = " . $this->registry->userinfo['userid'] . "
                ")))
                {
                    $this->errors[] = array(
                        'filename' => "",
                        'error'    => fetch_error('invalid_filedataid_x', $files['filedataid']["$x"])
                    );
                    continue;
                }

                $attachment = array(
                    'filedataid' => $files['filedataid']["$x"],
                    'name'       => $filedatainfo['filename'],
                    'filehash'   => $filedatainfo['filehash'],
                    'filesize'   => $filedatainfo['filesize'],
                    'extension' => $filedatainfo['extension'],
                    'filename'   => $filedatainfo['filename'],
                );
            }
            else if ($files['url']["$x"])
            {
                $attachment = $files['name']["$x"];
            }
            else
            {
                $attachment = array(
                    'name'     => $files['name']["$x"],
                    'tmp_name' => $files['tmp_name']["$x"],
                    'error'    => $files['error']["$x"],
                    'size'     => $files['size']["$x"],
                );
            }
            $this->attachcount++;
    
            $ids[] = $this->process_upload($upload, $attachment);
        }

        return implode(', ', $ids);
    }

    protected function &fetch_attachdm()
    {
        // here we call the attach/file data combined dm
        $attachdata =& datamanager_init('AttachmentFiledata', $this->registry, ERRTYPE_ARRAY, 'attachment');
        $attachdata->set('contenttypeid', $this->contenttypeid);
        $attachdata->set('posthash', $this->values['posthash']);
        $attachdata->set_info('contentid', $this->contentid);
        $attachdata->set_info('categoryid', $this->categoryid);
        $attachdata->set('state', 'visible');

        return $attachdata;
    }

    protected function process_upload($upload, $attachment)
    {
        // first verify maximum number of attachments has not been reached
        if (!$this->verify_max_attachments($attachment))
        { 
            
            return false;
        }

        // process the upload
            
        if (!($attachmentid = $upload->process_upload($attachment)))
        {
            
            $this->attachcount--;
        }

        // add any upload errors to the error array
        if ($error = $upload->fetch_error())
        {
            $this->errors[] = array(
                'filename' => is_array($attachment) ? $attachment['name'] : $attachment,
                'error'    => $error,
            );
        }

        return $attachmentid;
    }
}

/**
* Class for initiating proper subclass to extende attachment DM operations
*
* @package         vBulletin
* @version        $Revision: 34246 $
* @date         $Date: 2009-12-09 17:20:25 -0600 (Wed, 09 Dec 2009) $
*
*/


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 02:58, Mon Jan 4th 2010
|| # CVS: $RCSfile$ - $Revision: 34246 $
|| ####################################################################
\*======================================================================*/