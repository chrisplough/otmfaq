<?php

/************************************************************************************
* vBSEO 3.6.0 for vBulletin v3.x & v4.x by Crawlability, Inc.                       *
*                                                                                   *
* Copyright © 2011, Crawlability, Inc. All rights reserved.                         *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

class vB_ProfileBlock_vBSEOLikes extends vB_ProfileBlock
{
var $template_name = 'vbseo_likes_list_container';
var $nowrap = false;
function block_is_enabled()
{
return true;
}
function confirm_display()
{
return true;
}
function prepare_output($id = '', $options = array())
{
global $show, $vbphrase, $vboptions;
$pagenumber = max($options['pagenumber'], 1);
$perpage = $options['perpage'];
$userid  = $options['userid'];
$duserid  = $options['duserid'];
$linfo = vBSEO_UI::get_likes_detailed($userid, $duserid, $pagenumber, $perpage);
$likes = $linfo['results'];
vBSEO_UI::prerender_likes($likes);
$postbits = '';
foreach($likes as $post)
{
$postbits .= vbseo_vbtemplate_render_any('vbseo_likebit',
array('post' => $post)
);
}
$total = $this->block_data['total'] = $linfo['total'];
$sorturl = 'member.php?u=' . $this->profile->userinfo['userid'].'&tab='.($options['tab']);
$this->block_data['likebits'] = $postbits;
$this->block_data['pagenav'] = construct_page_nav($pagenumber, $perpage, $total, $sorturl );
$this->block_data['like_type'] = ($_GET['tab']);
$this->block_data['vb408'] = ($this->registry->versionnumber >= "4.0.8");
}
}
?>