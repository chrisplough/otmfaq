<?php
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/

defined('IN_MOBIQUO') or exit;

function construct_forum_bit_mobiquo($parentid, $depth = 0, $subsonly = 0, $subscribe_forums = array(), $show_hide = 0)
{
    global $vbulletin, $show, $lastpostarray;
    
    $return_forum = array();
    // this function takes the constant MAXFORUMDEPTH as its guide for how
    // deep to recurse down forum lists. if MAXFORUMDEPTH is not defined,
    // it will assume a depth of 2.

    // call fetch_last_post_array() first to get last post info for forums
    if (!is_array($lastpostarray))
    {
        fetch_last_post_array($parentid);
    }
    if (empty($vbulletin->iforumcache["$parentid"]))
    {
        return;
    }

    define('MAXFORUMDEPTH', 2);

    $forumbits = '';
    $return_forumbits = array();
    $depth++;

    foreach ($vbulletin->iforumcache["$parentid"] AS $forumid)
    {
        // grab the appropriate forum from the $vbulletin->forumcache
        $forum = $vbulletin->forumcache["$forumid"];

        //$lastpostforum = $vbulletin->forumcache["$lastpostarray[$forumid]"];
        $lastpostforum = (empty($lastpostarray[$forumid]) ? array() : $vbulletin->forumcache["$lastpostarray[$forumid]"]);
        if(!$show_hide){
            if (!$forum['displayorder'] OR !($forum['options'] & $vbulletin->bf_misc_forumoptions['active']))
            {
                continue;
            }
        }
        $forumperms = $vbulletin->userinfo['forumpermissions']["$forumid"];
        $lastpostforumperms = (empty($lastpostarray[$forumid]) ? 0 : $vbulletin->userinfo['forumpermissions']["$lastpostarray[$forumid]"]);
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) AND ($vbulletin->forumcache["$forumid"]['showprivate'] == 1 OR (!$vbulletin->forumcache["$forumid"]['showprivate'] AND !$vbulletin->options['showprivateforums'])))
        { // no permission to view current forum
            continue;
        }

        $lastpostinfo = $vbulletin->forumcache["$lastpostarray[$forumid]"];
        $forum['statusicon'] = fetch_forum_lightbulb($forumid, $lastpostinfo, $forum);
        $show['newposticon'] = (($forum['statusicon'] == 'new') ? true : false);

        if (!defined('RETURN_DESCRIPTION') || !RETURN_FIRST_LEVEL)
        {
            $childforumbits = array();
            if ($subsonly)
            {
                $childforumbits = construct_forum_bit_mobiquo($forum['forumid'], 1, $subsonly, $subscribe_forums);
            }
            else if ($depth < MAXFORUMDEPTH)
            {
                $childforumbits = construct_forum_bit_mobiquo($forum['forumid'], $depth, $subsonly, $subscribe_forums);
            }
        }

        // do stuff if we are not doing subscriptions only, or if we ARE doing subscriptions,
        // and the forum has a subscribedforumid
        if (!$subsonly OR ($subsonly AND !empty($forum['subscribeforumid'])))
        {
            $GLOBALS['forumshown'] = true; // say that we have shown at least one forum

            if (($forum['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads']))
            {
                $tempext = '_post';
                $only_sub = 0;
            }
            else
            {
                $tempext = '_nopost';
                $only_sub = 1;
            }
            
            if (!defined('RETURN_DESCRIPTION') || !RETURN_FIRST_LEVEL)
            {
                $forum['subforums'] = array();
                if ($subsonly OR $depth == MAXFORUMDEPTH )
                {
                    $forum['subforums'] = construct_forum_bit_mobiquo($forum['forumid'], 1, 0, $subscribe_forums);
                }
            }

            $children = explode(', ', $forum['childlist']);
            
            $mobiquo_is_subscribed = false;

            if(isset($subscribe_forums) && !empty($subscribe_forums[$forum[forumid]])){
                $mobiquo_is_subscribed = true;
            }
            $mobiquo_can_subscribe =  iif($only_sub == 0, true, false);

            $forumbits_list = array(
                'forum_id'      => new xmlrpcval($forum['forumid'], 'string'),
                'forum_name'    => new xmlrpcval(mobiquo_encode($forum['title']), 'base64'),
                'parent_id'     => new xmlrpcval($forum['parentid'], 'string'),
                
                'can_subscribe' => new xmlrpcval($mobiquo_can_subscribe, 'boolean'),
            );
            
            if ($only_sub)              $forumbits_list['sub_only']         = new xmlrpcval(true, 'boolean');
            if ($forum['password'])     $forumbits_list['is_protected']     = new xmlrpcval(true, 'boolean');
            if ($show['newposticon'])   $forumbits_list['new_post']         = new xmlrpcval(true, 'boolean');
            if ($mobiquo_is_subscribed) $forumbits_list['is_subscribed']    = new xmlrpcval(true, 'boolean');
            //if ($mobiquo_can_subscribe) $forumbits_list['can_subscribe']    = new xmlrpcval(true, 'boolean');
            
            if (defined('RETURN_DESCRIPTION') && RETURN_DESCRIPTION && $forum['description'])
                $forumbits_list['description'] = new xmlrpcval(mobiquo_encode($forum['description']), 'base64');
            
            if ($logo_url = get_forum_icon($forumid))
                $forumbits_list['logo_url'] = new xmlrpcval($logo_url, 'string');
            
            if (empty($childforumbits) && empty($forum['subforums']) && $forum['link'])
                $forumbits_list['url'] = new xmlrpcval($forum['link'], 'string');
            
            if($childforumbits){
                $forumbits_list['child'] = new xmlrpcval($childforumbits, 'array');
            }
            
            if($forum['subforums']){
                $forumbits_list['child'] = new xmlrpcval($forum['subforums'], 'array');
            }

            $return_forumbits[$forum['forumid']]  = new xmlrpcval($forumbits_list, 'struct');

        } // end if (!$subsonly OR ($subsonly AND !empty($forum['subscribeforumid'])))
        else
        {
            if(isset($childforumbits)){
                $return_forumbits =  array_merge($return_forumbits, $childforumbits);
            }
        }
    }
    
    if(sizeof($return_forumbits)>0){
        return array_values($return_forumbits);
    } else {
        return;
    }
}
