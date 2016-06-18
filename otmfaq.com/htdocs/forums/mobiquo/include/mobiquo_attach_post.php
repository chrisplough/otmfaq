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


/*
*
* @package         vBulletin
* @version        $Revision: 29997 $
* @date         $Date: 2009-03-23 10:11:43 -0700 (Mon, 23 Mar 2009) $
*
*/
class mobiquo_vB_Attachment_Store_vBForum_Post extends mobiquo_vB_Attachment_Store
{
    /**
    *    Postinfo
    *
    * @var    array
    */
    protected $postinfo = array();

    /**
    * Threadinfo
    *
    * @var     array
    */
    protected $threadinfo = array();

    /**
    * Foruminfo
    *
    * @var    array
    */
    protected $foruminfo = array();

    /**
    * Constructor
    *
    * @return    void
    */
    public function __construct(&$registry, $contenttypeid, $categoryid, $values)
    {
        parent::__construct($registry, $contenttypeid, $categoryid, $values);
    }

    /**
    * Verifies permissions to attach content to posts
    *
    * @return    boolean
    */
    public function verify_permissions()
    {
        global $show;
        
        $this->values['postid'] = intval($this->values['p']) ? intval($this->values['p']) : intval($this->values['postid']);
        $this->values['threadid'] = intval($this->values['t']) ? intval($this->values['t']) : intval($this->values['threadid']);
        $this->values['forumid'] = intval($this->values['f']) ? intval($this->values['f']) : intval($this->values['forumid']);

        if ($this->values['postid'])
        {
            if (!($this->postinfo = fetch_postinfo($this->values['postid'])))
            {
                return false;
            }
            $this->values['threadid'] = $this->postinfo['threadid'];
        }

        if ($this->values['threadid'])
        {
            if (!($this->threadinfo = fetch_threadinfo($this->values['threadid'])))
            {
                return false;
            }
            $this->values['forumid'] = $this->threadinfo['forumid'];
        }
  
        if ($this->values['forumid'] AND !($this->foruminfo = fetch_foruminfo($this->values['forumid'])))
        {
            return false;
        }

        if (!$this->foruminfo AND !$this->threadinfo AND !($this->postinfo AND $this->values['editpost']))
        {
            return false;
        }

        $forumperms = fetch_permissions($this->foruminfo['forumid']);

        // No permissions to post attachments in this forum or no permission to view threads in this forum.
        if (
            !($forumperms & $this->registry->bf_ugp_forumpermissions['canpostattachment'])
                OR
            !($forumperms & $this->registry->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $this->registry->bf_ugp_forumpermissions['canviewthreads'])
        )
        {
            return false;
        }

        if (
            (!$this->postinfo AND !$this->foruminfo['allowposting'])
                OR
            $this->foruminfo['link']
                OR
            !$this->foruminfo['cancontainthreads']
        )
        {
            return false;
        }

        if ($this->threadinfo) // newreply.php or editpost.php called
        {
            if ($this->threadinfo['isdeleted'] OR (!$this->threadinfo['visible'] AND !can_moderate($this->threadinfo['forumid'], 'canmoderateposts')))
            {
                return false;
            }
            if (!$this->threadinfo['open'])
            {
                if (!can_moderate($this->threadinfo['forumid'], 'canopenclose'))
                {
                    return false;
                }
            }
            if (
                ($this->registry->userinfo['userid'] != $this->threadinfo['postuserid'])
                    AND
                (
                    !($forumperms & $this->registry->bf_ugp_forumpermissions['canviewothers'])
                        OR
                    !($forumperms & $this->registry->bf_ugp_forumpermissions['canreplyothers'])
                ))
            {
                return false;
            }

            // don't call this part on editpost.php (which will have a $postid)
            if (
                !$this->postinfo
                    AND
                !($forumperms & $this->registry->bf_ugp_forumpermissions['canreplyown'])
                    AND
                $this->registry->userinfo['userid'] == $this->threadinfo['postuserid']
            )
            {
                return false;
            }
        }
        else if (!($forumperms & $this->registry->bf_ugp_forumpermissions['canpostnew'])) // newthread.php
        {
            return false;
        }

        if ($this->postinfo) // editpost.php
        {
            if (!can_moderate($this->threadinfo['forumid'], 'caneditposts'))
            {
                if (!($forumperms & $this->registry->bf_ugp_forumpermissions['caneditpost']))
                {
                    return false;
                }
                else
                {
                    if ($this->registry->userinfo['userid'] != $this->postinfo['userid'])
                    {
                        // check user owns this post
                        return false;
                    }
                    else
                    {
                        // check for time limits
                        if ($this->postinfo['dateline'] < (TIMENOW - ($this->registry->options['edittimelimit'] * 60)) AND $this->registry->options['edittimelimit'])
                        {
                            return false;
                        }
                    }
                }
            }

            $this->contentid = $this->postinfo['postid'];
            $this->userinfo = fetch_userinfo($this->postinfo['userid']);
            cache_permissions($this->userinfo, true);
        }
        else
        {
            $this->userinfo = $this->registry->userinfo;
        }

        // check if there is a forum password and if so, ensure the user has it set
        if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
            return_fault('Your administrator has required a password to access this forum.');

        if (!$this->foruminfo['allowposting'])
        {
            $show['attachoption'] = false;
            $show['forumclosed'] = true;
        }

        return true;
    }

    /**
    * Ensures that attachment interface isn't display if forum doesn't allow posting and there are no existing attachments
    *
    * @return    boolean
    */
    public function fetch_attachcount()
    {
        parent::fetch_attachcount();

        if (!$this->foruminfo['allowposting'] AND !$this->attachcount)
        {
            return false;
        }

        return true;
    }

    /**
    * Verifies permissions to attach content to posts
    *
    * @param    object    vB_Upload
    * @param    array        Information about uploaded attachment
    *
    * @return    void
    */
    protected function process_upload($upload, $attachment)
    {
        if (!$this->foruminfo['allowposting'])
        {
            $error = $vbphrase['this_forum_is_not_accepting_new_attachments'];
            $errors[] = array(
                'filename' => is_array($attachment) ? $attachment['name'] : $attachment,
                'error'    => $error
            );
        }
        else
        {
            if (
                ($attachmentid = parent::process_upload($upload, $attachment))
                    AND
                $this->registry->userinfo['userid'] != $this->postinfo['userid']
                    AND
                can_moderate($this->threadinfo['forumid'], 'caneditposts')
            )
            {
                $this->postinfo['attachmentid'] = $attachmentid;
                $this->postinfo['forumid'] = $foruminfo['forumid'];
                require_once(DIR . '/includes/functions_log_error.php');
                log_moderator_action($this->postinfo, 'attachment_uploaded');
            }

            return $attachmentid;
        }
    }

    /**
    * Set attachment to moderated if the forum dictates it so
    *
    * @return    object
    */
    protected function &fetch_attachdm()
    {
        $attachdata =& parent::fetch_attachdm();
        $state = (
            !isset($this->foruminfo['moderateattach'])
                OR
            (
                !$this->foruminfo['moderateattach']
                    OR
                can_moderate($this->foruminfo['forumid'], 'canmoderateattachments')
            )
        ) ? 'visible' : 'moderation';
        $attachdata->set('state', $state);

        return $attachdata;
    }
}

/**
* Class for deleting post attachments
*
* @package         vBulletin
* @version        $Revision: 29997 $
* @date         $Date: 2009-03-23 10:11:43 -0700 (Mon, 23 Mar 2009) $
*
*/


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 02:58, Mon Jan 4th 2010
|| # CVS: $RCSfile$ - $Revision: 29983 $
|| ####################################################################
\*======================================================================*/
?>