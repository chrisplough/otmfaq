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

if(!defined('VBSEO_IS_VBSEOCP')) exit;
define('VBSEO_NO_LICENSE_CHECK_5342', true);
include dirname(__FILE__).'/functions_vbseo.php';
include vBSEO_Storage::path('vbseoinc').'/functions_vbseocp_abstract.php';
class vBSEO_CP extends CP_Abstract {
private static $legacy_files = array(
'vbseocpform.php',
'includes/config_vbseo.php',
'includes/functions_vbseo.php',
'includes/functions_vbseo_cache.php',
'includes/functions_vbseo_createurl.php',
'includes/functions_vbseo_crr.php',
'includes/functions_vbseo_db.php',
'includes/functions_vbseo_extra.php',
'includes/functions_vbseo_hook.php',
'includes/functions_vbseo_linkback.php',
'includes/functions_vbseo_misc.php',
'includes/functions_vbseo_ppclass.php',
'includes/functions_vbseo_pre.php',
'includes/functions_vbseo_seo.php',
'includes/functions_vbseo_startup.php',
'includes/functions_vbseo_url.php',
'includes/functions_vbseo_vb.php',
'includes/cron/vbseo_serviceupdate.php',
);
public static function proc_page($litem)
{
global $vboptions;
$tpl = $litem;
$ttlitem = 'cp_'.$litem;
$result = $repl = array();
$result['messages'] = array();
switch($litem)
{
case 'login':
break;
case 'dashboard':
$lictext = $liccode = $vboptions['vbseo_confirmation_code'];
if(!$liccode)
$lictext = self::lang('contact_license');
if(preg_match('#Unauthorized Upgrade#i', $liccode))
{
$lictext = self::lang('unauth_upgrade');
$liccode = '';
}
$keyvalid = $liccode && ($liccode == vBSEO_Storage::setting('VBSEO_LICENSE_CODE'));
$repl = array(                                                        
'version' => VBSEO_VERSION2_MORE.(defined('VBSEO_LICENSE_STR') ? (VBSEO_LICENSE_TYPE==1 ? '' : ' '.VBSEO_LICENSE_STR) : ', Unreg'),
'lic_code' => vBSEO_Storage::setting('VBSEO_LICENSE_CODE'),
'keyclass' => $keyvalid ? 'success' : 'error',
'keycheck' => self::lang($keyvalid ? 'cp_validkey' : 'cp_invalidkey'),
'valid_code_msg' => $lictext,
'valid_class' => $liccode ? 'green' : 'red',
'valid_code' => $liccode,
);
if(!$liccode)
$result['messages'][] = array('attention', self::lang('cannot_retrieve_license'));
break;
case 'vbseo_opt':
$opts = self::display_option_yesno('activate_desc', 'VBSEO_ENABLED');
$ll = self::get_flist('vbseocp_(.+)\.xml');
foreach($ll as $v)
$langlist[$v] = ucfirst($v);
$opts .= self::display_option_select('language_desc', 'VBSEO_CP_LANGUAGE', $langlist);
$opts .= self::display_option_yesno('redir_desc', 'VBSEO_THREAD_301_REDIRECT');
$opts .= self::display_option_yesno('domname_desc', 'VBSEO_USE_HOSTNAME_IN_URL');
$opts .= self::display_option_yesno('redirext_desc', 'VBSEO_REDIRECT_PRIV_EXTERNAL');
$opts .= self::display_option_yesno('email_desc', 'VBSEO_REWRITE_EMAILS');
$opts .= self::display_option_yesno('link_desc', 'VBSEO_LINK');
$opts .= self::display_option_radio('fnf_desc', 'VBSEO_404_HANDLE', 
array(
0 => self::lang('redire_hp'),
1 => self::lang('send_404'),
2 => self::lang('inc_custom'),
),
self::display_option_textfield('VBSEO_404_CUSTOM',
'onkeyup="if(this.value)document.getElementById(\'setting_VBSEO_404_HANDLE2\').checked=true"'
)
);
$opts .= self::display_option_area('exclpages_desc', 'VBSEO_IGNOREPAGES', '|');
$opts .= self::display_option_text('vbseo_affid', 'VBSEO_AFFILIATE_ID');
$opts2 .= self::display_header('vbseo_copyright_header');
$opts2 .= self::display_option_select('copyright_position_desc', 'VBSEO_COPYRIGHT', 
array(
0 => self::lang('copyright_sel_0'),
1 => self::lang('copyright_sel_1'),
2 => self::lang('copyright_sel_2'),
3 => self::lang('copyright_sel_3'),
4 => self::lang('copyright_sel_4'),
9 => self::lang('copyright_sel_5'),
5 => self::lang('copyright_sel_6'),
6 => self::lang('copyright_sel_7'),
7 => self::lang('copyright_sel_8'),
8 => self::lang('copyright_sel_9'),
10 => self::lang('copyright_sel_10'),
));
$opts2 .= self::display_option_yesno('vbseo_noversion_desc', 'VBSEO_NOVER_INFO');
$opts2 .= self::display_header('vbseo_advanced_custom_header');
if(!VBSEO_VB4)
{
$opts2 .= self::display_option_yesno('vbseo_gars_support', 'VBSEO_ENABLE_GARS');
}
$opts2 .= self::display_option_text('acro_auto_link_desc', 'VBSEO_AUTOLINK_FORMAT');
$opts2 .= self::display_option_yesno('vbseo_last_modified_header_desc', 'VBSEO_LASTMOD_HEADER');
$opts2 .= self::display_option_text('vbseo_vbulletin_php_extension', 'VBSEO_VB_EXT', 'medium');
$opts2 .= self::display_option_text('vbseo_vbulletin_config_php', 'VBSEO_VB_CONFIG', 'medium');
$opts2 .= self::display_option_text('vbseo_custom_doc_root', 'VBSEO_CUSTOM_DOCROOT');
$opts2 .= self::display_option_text('vbseo_custom_doc_relative', 'VBSEO_CUSTOM_TOPREL');
$opts2 .= self::display_option_area('vbseo_forum_slugs', 'forum_slugs', 'd');
$opts2 .= self::display_option_text('vbseo_applyto_forums', 'applyto_forums');
$opts2 .= self::display_header('vbseo_image_prefix_header');
$opts2 .= self::display_option_text('vbseo_avatar_prefix', 'VBSEO_AVATAR_PREFIX');
$opts2 .= self::display_option_text('vbseo_atttachment_prefix', 'VBSEO_ATTACHMENTS_PREFIX');
$opts2 .= self::display_option_text('vbseo_icon_prefix', 'VBSEO_ICON_PREFIX');
$opts2 .= self::display_header('vbseo_blog_prefix_header');
$opts2 .= self::display_option_text('vbseo_blog_cat_prefix_desc', 'VBSEO_BLOG_CAT_UNDEF');
$ttlitem = 'cp_gen_vbseo';
break;
case 'char_opt':
$opts .= self::display_option_select('noneng_desc', 'VBSEO_FILTER_FOREIGNCHARS', 
array(
1 => self::lang('noneng_rem'),
2 => self::lang('noneng_repl'),
0 => self::lang('noneng_keep')
));
$opts .= self::display_option_area('char_repl_desc', 'char_repl');
$opts2 .= self::display_option_yesno('utf8_desc', 'VBSEO_UTF8_SUPPORT');
$opts2 .= self::display_option_yesno('morechars_desc', 'VBSEO_REWRITE_MEMBER_MORECHARS');
$opts2 .= self::display_option_yesno('tagfilter_desc', 'VBSEO_URL_TAGS_FILTER');
$opts2 .= self::display_option_yesno('nourlenc_desc', 'VBSEO_REWRITE_NO_URLENCODING');
$opts2 .= self::display_option_yesno('utf8convert_desc', 'VBSEO_REWRITE_UTF8_CONVERT',
vBSEO_CP::lang('utf8convert_orig_charset').':<br />'.
self::display_option_textfield('VBSEO_REWRITE_UTF8_SRC_CHARSET')
);
$opts2 .= self::display_option_text('cp_charset', 'VBSEO_CP_CHARSET', 'medium');
$ttlitem = 'cp_gen_vbseo';
break;
case 'log_opt':
$opts .= self::display_option_yesno('botact_desc', 'VBSEO_SITEMAP_MOD');
$opts .= self::display_option_yesno('analytics_desc', 'VBSEO_ADD_ANALYTICS_CODE');
$opts .= self::display_option_text('analytics_code_desc', 'VBSEO_ANALYTICS_CODE');
$opts .= self::display_option_yesno('analytics_track_desc', 'VBSEO_ADD_ANALYTICS_CODE_EXT');
$opts .= self::display_option_yesno('analytics_anon_desc', 'VBSEO_ADD_ANALYTICS_ANON');
$opts .= self::display_option_text('analytics_format_desc', 'VBSEO_ANALYTICS_EXT_FORMAT');
$opts .= self::display_option_yesno('funnel_desc', 'VBSEO_ADD_ANALYTICS_GOAL');
$opts .= self::display_option_select('segmentation_desc', 'VBSEO_ANALYTICS_SEGMENTATION', 
array(
1 => self::lang('segmentation_1'),
2 => self::lang('segmentation_2'),
0 => self::lang('segmentation_0')
));
$opts .= self::display_option_yesno('adsense_desc', 'VBSEO_GOOGLE_AD_SEC');
$ttlitem = 'cp_gen_vbseo';
break;
case 'arc_opt':
$selopt = 0;
if(vBSEO_Storage::setting('VBSEO_REWRITE_ARCHIVE_URLS'))$selopt += 2;
if(vBSEO_Storage::setting('VBSEO_REDIRECT_ARCHIVE'))$selopt += 1;
$opts = self::display_option_select('arcopt_desc', 'combo_arc', 
array(
1 => self::lang('arc_redir_301'),
2 => self::lang('arc_rewr_vbarc'),
3 => self::lang('arc_redir_rewr'),
0 => self::lang('arc_keep')
), $selopt);
$opts .= self::display_option_formats('arcroot_desc', 'VBSEO_ARCHIVE_ROOT', 
array(
'/archive/index.php/',
'/archive/',
'/sitemap/',
)
);
$opts .= self::display_option_yesno('arcorder_desc', 'VBSEO_ARCHIVE_ORDER_DESC');
$ttlitem = 'cp_gen_vbseo';
break;
case 'cdn_opt':
$ttlitem = 'cp_gen_vbseo';
$repl['mc_cdntype'.intval(vBSEO_Storage::setting('VBSEO_CDN_SINGLE'))] = 'checked';
$repl['cdn_text'] = vBSEO_Storage::setting('cdn_custom_text');
if(vBSEO_Storage::setting('VBSEO_CDN_JS')) $repl['mc_cdn_js'] = 'checked';
if(vBSEO_Storage::setting('VBSEO_CDN_IMAGES')) $repl['mc_cdn_images'] = 'checked';
if(vBSEO_Storage::setting('VBSEO_CDN_AVATARS')) $repl['mc_cdn_avatars'] = 'checked';
if(vBSEO_Storage::setting('VBSEO_CDN_ATTACH')) $repl['mc_cdn_attach'] = 'checked';
break;
case 'cache_opt':
if(class_exists('Memcache'))
$memc = new Memcache;
$repl['memcopt_visible'] = (VBSEO_CACHE_TYPE==1) ? '' : 'none';
$repl['options'] = 
self::display_option_text('vbseo_cache_var_defin', 'VBSEO_CACHE_VAR') .
self::display_option_radio('cachetype_desc', 'VBSEO_CACHE_TYPE', 
array(
0 => self::lang('none'),
1 => 'memcached '.self::lang($memc ? 'supported' : 'unsupported'),
2 => 'APC Cache '.self::lang(function_exists('apc_store') ? 'supported' : 'unsupported'),
3 => 'XCache '.self::lang(function_exists('xcache_get') ? 'supported' : 'unsupported'),
4 => 'eAccelerator '.self::lang(function_exists('eaccelerator_get') ? 'supported' : 'unsupported'),
),
'<script type="text/javascript">
$("input[type=radio]").click(function(){
$("#memcache_opt").css("display", $("#setting_VBSEO_CACHE_TYPE2").is(":checked") ? "" : "none");
});
</script>'
)
;
$repl['mc_pers'.vBSEO_Storage::setting('VBSEO_MEMCACHE_PERS')] = 'checked';
$ttlitem = 'cp_gen_vbseo';
break;
case 'tb_opt':
$opts = self::display_option_yesno('vbseo_tweetboard', 'VBSEO_TWEETBOARD',
vBSEO_CP::lang('twitter_user').':<br />'.
self::display_option_textfield('VBSEO_TWEETBOARD_USER')
);
$ttlitem = 'cp_gen_vbseo';
break;
case 'like_opt':
$opts .= self::display_option_yesno('likesys_forum', 'VBSEO_LIKE_POST');
$opts .= self::display_option_yesno('likesys_blog', 'VBSEO_LIKE_BLOG');
if(VBSEO_VB4)
{		
$opts .= self::display_option_yesno('likesys_cms', 'VBSEO_LIKE_CMS');
}
$opts .= self::display_option_yesno('likesys_vislink', 'VBSEO_LIKE_LINK_VISIBLE');
$ttlitem = 'cp_gen_vbseo';
break;
case 'url_gen':
$opts = self::display_option('gen_opt_description');
$opts .= self::display_option_select('sep_desc', 'VBSEO_SPACER', 
array('-' => '[ - ]', '_' => '[ _ ]', '.' => '[ . ]')
);
$opts .= self::display_option_text('limurl_desc', 'VBSEO_URL_PART_MAX');
$ttlitem = 'url_opt';
$tpl     = 'options';
break;
case 'url_forum':
$ftype = 'forum';
$aopt = array(
'path_opt',
array('pathbits_desc', 'VBSEO_FORUM_TITLE_BIT', array(
'[forum_id]',
'[forum_title]',
'forum[forum_id]',
)),
'urls_forum',
array('rw_forum_desc', 'VBSEO_REWRITE_FORUM'),
array('forum_format_desc', 'VBSEO_URL_FORUM', array(
'[forum_path]/',
'[forum_title]/',
'forum[forum_id]/',
'[forum_title].html',
)),
array('forum_pformat_desc', 'VBSEO_URL_FORUM_PAGENUM', array(
'[forum_path]/index[forum_page].html',
'[forum_title]/index[forum_page].html',
'forum[forum_id]/index[forum_page].html',
'[forum_title]-[forum_page].html',
)),
'urls_ann',
array('rw_ann_desc', 'VBSEO_REWRITE_ANNOUNCEMENT'),
array('ann_format_desc', 'VBSEO_URL_FORUM_ANNOUNCEMENT', array(
'[forum_path]/announcement-[announcement_title].html',
'[forum_title]/announcement-[announcement_title].html',
'forum[forum_id]/announcement[announcement_id].html',
'[forum_title]-announcement-[announcement_title].html',
)),
array('ann_pformat_desc', 'VBSEO_URL_FORUM_ANNOUNCEMENT_ALL', array(
'[forum_path]/announcements.html',
'[forum_title]/announcements.html',
'forum[forum_id]/announcements.html',
'[forum_title]-announcements.html',
)),
'urls_thread',
array('rw_thread_desc', 'VBSEO_REWRITE_THREADS'),
array('thread_format_desc', 'VBSEO_URL_THREAD', array(
'[forum_path]/[thread_id]-[thread_title].html',
'[forum_title]/[thread_id]-[thread_title].html',
'forum[forum_id]/thread[thread_id].html',
'[thread_id]-[thread_title].html',
)),
array('thread_pformat_desc', 'VBSEO_URL_THREAD_PAGENUM', array(
'[forum_path]/[thread_id]-[thread_title]-[thread_page].html',
'[forum_title]/[thread_id]-[thread_title]-[thread_page].html',
'forum[forum_id]/thread[thread_id]-[thread_page].html',
'[thread_id]-[thread_title]-[thread_page].html',
)),
((VBSEO_ENABLE_GARS && VBSEO_VB4) ?
array('thread_gformat_desc', 'VBSEO_URL_THREAD_GARS_PAGENUM', array(
'[forum_path]/[thread_id]-[thread_title]-gars[thread_page].html',
'[forum_title]/[thread_id]-[thread_title]-gars[thread_page].html',
)) : ''),
array('thread_lpformat_desc', 'VBSEO_URL_THREAD_LASTPOST', array(
'[forum_path]/[thread_id]-[thread_title]-last-post.html',
'[forum_title]/[thread_id]-[thread_title]-last-post.html',
'forum[forum_id]/thread[thread_id]-last-post.html',
'[thread_id]-[thread_title]-last-post.html',
)),
array('thread_npformat_desc', 'VBSEO_URL_THREAD_NEWPOST', array(
'[forum_path]/[thread_id]-[thread_title]-new-post.html',
'[forum_title]/[thread_id]-[thread_title]-new-post.html',
'forum[forum_id]/thread[thread_id]-new-post.html',
'[thread_id]-[thread_title]-new-post.html',
)),
array('thread_gpformat_desc', 'VBSEO_URL_THREAD_GOTOPOST', array(
'[forum_path]/[thread_id]-[thread_title]-post[post_id].html',
'[forum_title]/[thread_id]-[thread_title]-post[post_id].html',
'forum[forum_id]/thread[thread_id]-post[post_id].html',
'[thread_id]-[thread_title]-post[post_id].html',
)),
array('thread_gppformat_desc', 'VBSEO_URL_THREAD_GOTOPOST_PAGENUM', array(
'[forum_path]/[thread_id]-[thread_title]-post[post_id]-[thread_page].html',
'[forum_title]/[thread_id]-[thread_title]-post[post_id]-[thread_page].html',
'forum[forum_id]/thread[thread_id]-post[post_id]-[thread_page].html',
'[thread_id]-[thread_title]-post[post_id]-[thread_page].html',
)),
array('thread_ptformat_desc', 'VBSEO_URL_THREAD_PREV', array(
'[forum_path]/[thread_id]-[thread_title]-prev-thread.html',
'[forum_title]/[thread_id]-[thread_title]-prev-thread.html',
'forum[forum_id]/thread[thread_id]-prev-thread.html',
'[thread_id]-[thread_title]-prev-thread.html',
)),
array('thread_ntformat_desc', 'VBSEO_URL_THREAD_NEXT', array(
'[forum_path]/[thread_id]-[thread_title]-next-thread.html',
'[forum_title]/[thread_id]-[thread_title]-next-thread.html',
'forum[forum_id]/thread[thread_id]-next-thread.html',
'[thread_id]-[thread_title]-next-thread.html',
)),
array('vbseo_thread_prefix_desc', 'VBSEO_URL_THREAD_PREFIX'),
array('vbseo_thread_prefix_name_desc', 'VBSEO_URL_THREAD_PREFIX_NAME'),
'urls_prnthread',
array('rw_pthread_desc', 'VBSEO_REWRITE_PRINTTHREAD'),
array('pthread_rel_desc', 'VBSEO_NOFOLLOW_PRINTTHREAD'),
array('pthread_format_desc', 'VBSEO_URL_THREAD_PRINT', array(
'[forum_path]/[thread_id]-[thread_title]-print.html',
'[forum_title]/[thread_id]-[thread_title]-print.html',
'forum[forum_id]/thread[thread_id]-print.html',
'[thread_id]-[thread_title]-print.html',
)),
array('pthread_pformat_desc', 'VBSEO_URL_THREAD_PRINT_PAGENUM', array(
'[forum_path]/[thread_id]-[thread_title]-[thread_page]-print.html',
'[forum_title]/[thread_id]-[thread_title]-[thread_page]-print.html',
'forum[forum_id]/thread[thread_id]-[thread_page]-print.html',
'[thread_id]-[thread_title]-[thread_page]-print.html',
)),
'urls_post',
array('rw_post_desc', 'VBSEO_REWRITE_SHOWPOST'),
(VBSEO_VB4 ? '' :
array('post_rel_desc', 'VBSEO_NOFOLLOW_SHOWPOST', array(
'1' => vBSEO_CP::lang('yes'),
'0' => vBSEO_CP::lang('no'),
'2' => vBSEO_CP::lang('smart'),
), 1)),
array('post_format_desc', 'VBSEO_URL_POST_SHOW', array(
'[post_id]-post[post_count].html',
)),
'urls_poll',
array('rw_poll_desc', 'VBSEO_REWRITE_POLLS'),
array('poll_format_desc', 'VBSEO_URL_POLL', array(
'[forum_path]/poll-[poll_id]-[poll_title].html',
'[forum_title]/poll-[poll_id]-[poll_title].html',
'forum[forum_id]/poll[poll_id].html',
'[forum_title]-poll[poll_id]-[poll_title].html',
)),
'urls_mlist',
array('rw_mlist_desc', 'VBSEO_REWRITE_MEMBER_LIST'),
array('mlist_format_desc', 'VBSEO_URL_MEMBERLIST', array(
'members/list/',
'memberlist.html',
)),
array('mlist_pformat_desc', 'VBSEO_URL_MEMBERLIST_PAGENUM', array(
'members/list/index[page].html',
'memberlist-[page].html',
)),
array('mlist_lformat_desc', 'VBSEO_URL_MEMBERLIST_LETTER', array(
'members/list/[letter][page].html',
'memberlist-[letter][page].html',
)),
'urls_avatar',
array('rw_avatar_desc', 'VBSEO_REWRITE_AVATAR'),
array('avatar_format_desc', 'VBSEO_URL_AVATAR', array(
'[user_name].gif',
'[user_id].gif',
)),
'urls_navbul',
array('rw_navbul_desc', 'VBSEO_REWRITE_TREE_ICON'),
array('navbul_format_desc', 'VBSEO_URL_FORUM_TREE_ICON', array(
'[forum_path].gif',
'[forum_title].gif',
'forum[forum_id].gif',
'[forum_title].gif',
)),
array('navbul_tformat_desc', 'VBSEO_URL_THREAD_TREE_ICON', array(
'[forum_path]/[thread_title].gif',
'[forum_title]/[thread_title].gif',
'forum[forum_id]/thread[thread_id].gif',
'[thread_title].gif',
)),
'urls_attach',
array('rw_attach_desc', 'VBSEO_REWRITE_ATTACHMENTS'),
array('attach_format_desc', 'VBSEO_URL_ATTACHMENT', array(
'[forum_path]/[attachment_id]-[thread_title]-[original_filename]',
'[forum_title]/[attachment_id]-[thread_title]-[original_filename]',
'forum[forum_id]/[attachment_id]-[original_filename]',
'[attachment_id]-[thread_title]-[original_filename]',
)),
array('rw_attach_alt_desc', 'VBSEO_REWRITE_ATTACHMENTS_ALT'),
array('attach_alt_format_desc', 'VBSEO_URL_ATTACHMENT_ALT', array(
'[thread_title]-[original_filename]',
'[original_filename]',
)),
'urls_tags',
array('rw_tags_desc', 'VBSEO_REWRITE_TAGS'),
array('tagshome_format_desc', 'VBSEO_URL_TAGS_HOME', array(
'tags/',
)),
array('tags_format_desc', 'VBSEO_URL_TAGS_ENTRY', array(
'tags/[tag]/',
'tags/[tag].html',
)),
array('tagspage_format_desc', 'VBSEO_URL_TAGS_ENTRYPAGE', array(
'tags/[tag]/index[page].html',
'tags/[tag]-page[page].html',
))
);
break;
//-----------------------------------
case 'url_member':
$ftype = 'member';
$aopt = array(
'urls_member',
array('rw_memb_desc', 'VBSEO_REWRITE_MEMBERS'),
array('memb_format_desc', 'VBSEO_URL_MEMBER', array(
'members/[user_name]/',
'members/[user_name].html',
'members/[user_id].html',
'member-[user_name].html',
)),
array('memb_msg_format_desc', 'VBSEO_URL_MEMBER_MSGPAGE', array(
'members/[user_name]/index[page].html',
'members/[user_name]-page[page].html',
'members/[user_id]-[page].html',
'member-[user_name]-page[page].html',
)),
array('memb_conv_format_desc', 'VBSEO_URL_MEMBER_CONV', array(
'members/[user_name]/[visitor_name]/',
'members/[user_name]-with-[visitor_name].html',
'members/[user_id]-with-[visitor_id].html',
'member-[user_name]-with-[visitor_name].html',
)),
array('memb_convpage_format_desc', 'VBSEO_URL_MEMBER_CONVPAGE', array(
'members/[user_name]/[visitor_name]/index[page].html',
'members/[user_name]-with-[visitor_name]-page[page].html',
'members/[user_id]-with-[visitor_id]-page[page].html',
'member-[user_name]-with-[visitor_name]-page[page].html',
)),
array('memb_friends_format_desc', 'VBSEO_URL_MEMBER_FRIENDSPAGE', array(
'members/[user_name]/friends/index[page].html',
'members/[user_name]-friends-page[page].html',
'members/[user_id]-friends-[page].html',
'member-[user_name]-friends-page[page].html'
)),
'url_albums',
array('rw_albums_desc', 'VBSEO_REWRITE_MALBUMS'),
array('memb_albums_home_format_desc', 'VBSEO_URL_MEMBER_ALBUM_HOME', array(
'members/albums/',
'members/albums.html',
'albums.html',
)),
array('memb_albums_home_page_format_desc', 'VBSEO_URL_MEMBER_ALBUM_HOME_PAGE', array(
'members/albums/index[page].html',
'members/albums-[page].html',
'albums-[page].html',
)),
array('memb_albums_format_desc', 'VBSEO_URL_MEMBER_ALBUMS', array(
'members/[user_name]/albums/',
'members/[user_name]-albums.html',
'members/[user_id]-albums.html',
'member-[user_name]-albums.html'
)),
array('memb_albumspage_format_desc', 'VBSEO_URL_MEMBER_ALBUMS_PAGE', array(
'members/[user_name]/albums/page[page].html',
'members/[user_name]-albums-page[page].html',
'members/[user_id]-albums-page[page].html',
'member-[user_name]-albums-page[page].html'
)),
array('memb_album_format_desc', 'VBSEO_URL_MEMBER_ALBUM', array(
'members/[user_name]/albums/[album_title]/',
'members/[user_name]-albums-[album_title].html',
'members/[user_id]-albums[album_id].html',
'member-[user_name]-albums-[album_title].html'
)),
array('memb_albumpage_format_desc', 'VBSEO_URL_MEMBER_ALBUM_PAGE', array(
'members/[user_name]/albums/[album_title]/index[page].html',
'members/[user_name]-albums-[album_title]-page[page].html',
'members/[user_id]-albums[album_id]-page[page].html',
'member-[user_name]-albums-[album_title]-page[page].html'
)),
array('memb_pic_format_desc', 'VBSEO_URL_MEMBER_PICTURE', array(
'members/[user_name]/albums/[album_title]/[picture_id]-[picture_title]/',
'members/[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].html',
'members/[user_id]-albums[album_id]-picture[picture_id].html',
'member-[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].html'
)),
array('memb_picpage_format_desc', 'VBSEO_URL_MEMBER_PICTURE_PAGE', array(
'members/[user_name]/albums/[album_title]/[picture_id]-[picture_title]/index[page].html',
'members/[user_name]-albums-[album_title]-picture[picture_id]-[picture_title]-page[page].html',
'members/[user_id]-albums[album_id]-picture[picture_id]-page[page].html',
'member-[user_name]-albums-[album_title]-picture[picture_id]-[picture_title]-page[page].html'
)),
array('memb_picimg_format_desc', 'VBSEO_URL_MEMBER_PICTURE_IMG', array(
'members/[user_name]/albums/[album_title]/[picture_id]-[picture_title].[original_ext]',
'members/[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].[original_ext]',
'members/[user_id]-albums[album_id]-picture[picture_id].[original_ext]',
'member-[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].[original_ext]'
))
);
break;
//-----------------------------------
case 'url_groups':
$ftype = 'groups';
$aopt = array(
'urls_groups',
array('rw_groups_desc', 'VBSEO_REWRITE_GROUPS'),
array('groupshome_format_desc', 'VBSEO_URL_GROUPS_HOME', array(
'groups/',
)),
array('groupsall_format_desc', 'VBSEO_URL_GROUPS_ALL', array(
'groups/all/',
'groups/all.html',
)),
array('groupsallpage_format_desc', 'VBSEO_URL_GROUPS_ALL_PAGE', array(
'groups/all/index[page].html',
'groups/all-[page].html',
)),
array('groups_format_desc', 'VBSEO_URL_GROUPS', array(
'groups/[group_name]/',
'groups/[group_name].html',
'groups/[group_id]/',
'groups/[group_id]-[group_name].html',
)),
array('groupspage_format_desc', 'VBSEO_URL_GROUPS_PAGE', array(
'groups/[group_name]/index[page].html',
'groups/[group_name]-page[page].html',
'groups/[group_id]-page[page].html',
'groups/[group_id]-[group_name]-page[page].html',
)),
array('group_discussion_desc', 'VBSEO_URL_GROUPS_DISCUSSION', array(
'groups/[group_name]/[discussion_id]-[discussion_name]/',
'groups/[group_name]-d[discussion_id]-[discussion_name].html',
'groups/[group_id]-[discussion_id].html',
'groups/[group_id]-[group_name]-d[discussion_id]-[discussion_name].html',
)),
array('group_discussion_page_desc', 'VBSEO_URL_GROUPS_DISCUSSION_PAGE', array(
'groups/[group_name]/[discussion_id]-[discussion_name]/index[page].html',
'groups/[group_name]-d[discussion_id]-[discussion_name]-page[page].html',
'groups/[group_id]-[discussion_id]-page[page].html',
'groups/[group_id]-[group_name]-d[discussion_id]-[discussion_name]-page[page].html',
)),
array('group_discussion_last_post_desc', 'VBSEO_URL_GROUPS_DISCUSSION_LAST_POST', array(
'groups/[group_name]/[discussion_id]-[discussion_name]-last-post/',
'groups/[group_name]-d[discussion_id]-[discussion_name]-last-post.html',
'groups/[group_id]-[discussion_id]-last-post.html',
'groups/[group_id]-[group_name]-d[discussion_id]-[discussion_name]-last-post.html',
)),
array('groupmembers_format_desc', 'VBSEO_URL_GROUPS_MEMBERS', array(
'groups/[group_name]/members/',
'groups/[group_name]-members.html',
'groups/[group_id]-members.html',
'groups/[group_id]-[group_name]-members.html',
)),
array('groupmemberspage_format_desc', 'VBSEO_URL_GROUPS_MEMBERS_PAGE', array(
'groups/[group_name]/members/index[page].html',
'groups/[group_name]-members-page[page].html',
'groups/[group_id]/members-page[page].html',
'groups/[group_id]-[group_name]-members-page[page].html'
)),
array('grouppic_format_desc', 'VBSEO_URL_GROUPS_PIC', array(
'groups/[group_name]/pictures/',
'groups/[group_name]-pictures.html',
'groups/[group_id]-pictures.html',
'groups/[group_id]-[group_name]-pictures.html'
)),
array('grouppicpage_format_desc', 'VBSEO_URL_GROUPS_PIC_PAGE', array(
'groups/[group_name]/pictures/index[page].html',
'groups/[group_name]-pictures-page[page].html',
'groups/[group_id]-pictures-page[page].html',
'groups/[group_id]-[group_name]-pictures-page[page].html'
)),
array('grouppicture_format_desc', 'VBSEO_URL_GROUPS_PICTURE', array(
'groups/[group_name]/pictures/[picture_id]-[picture_title]/',
'groups/[group_name]-picture[picture_id]-[picture_title].html',
'groups/[group_id]-picture[picture_id].html',
'groups/[group_id]-[group_name]-picture[picture_id]-[picture_title].html'
)),
array('grouppicturepage_format_desc', 'VBSEO_URL_GROUPS_PICTURE_PAGE', array(
'groups/[group_name]/pictures/[picture_id]-[picture_title]/index[page].html',
'groups/[group_name]-picture[picture_id]-[picture_title]-page[page].html',
'groups/[group_id]-picture[picture_id]-page[page].html',
'groups/[group_id]-[group_name]-picture[picture_id]-[picture_title]-page[page].html'
)),
array('grouppictureimg_format_desc', 'VBSEO_URL_GROUPS_PICTURE_IMG', array(
'groups/[group_name]/pictures/[picture_id]-[picture_title].[original_ext]',
'groups/[group_name]-picture[picture_id]-[picture_title].[original_ext]',
'groups/[group_id]-picture[picture_id].[original_ext]',
'groups/[group_id]-[group_name]-picture[picture_id]-[picture_title].[original_ext]'
)),
array('group_category_list_desc', 'VBSEO_URL_GROUPS_CATEGORY_LIST', array(
'groups/categories/',
'groups/categories.html',
)),
array('group_category_list_page_desc', 'VBSEO_URL_GROUPS_CATEGORY_LIST_PAGE', array(
'groups/categories/index[page].html',
'groups/categories-page[page].html',
)),
array('group_category_desc', 'VBSEO_URL_GROUPS_CATEGORY', array(
'groups/categories/[cat_name]/',
'groups/category-[cat_name].html',
'groups/category[cat_id].html',
'groups/category[cat_id]-[cat_name].html',
)),
array('group_category_page_desc', 'VBSEO_URL_GROUPS_CATEGORY_PAGE', array(
'groups/categories/[cat_name]/index[page].html',
'groups/category-[cat_name]-page[page].html',
'groups/category[cat_id]-page[page].html',
'groups/category[cat_id]-[cat_name]-page[page].html',
))
);
break;
case 'url_cms':
$ftype = 'cms';
$aopt = array(
array('rw_cms_desc', 'VBSEO_REWRITE_CMS'),
array('cmsdom_desc', 'VBSEO_URL_CMS_DOMAIN', -1),
array('cmshome_format_desc', 'VBSEO_URL_CMS_HOME', array(
'content/',
'content.html',
)),
array('cmssec_format_desc', 'VBSEO_URL_CMS_SECTION', array(
'content/section/[section_id]-[section_title]/',
'section/[section_id]-[section_title]/',
)),
array('cmsseclist_format_desc', 'VBSEO_URL_CMS_SECTION_LIST', array(
'content/section/[section_id]-[section_title]/list.html',
'section/[section_id]-[section_title]/list.html',
)),
array('cmsseclistpage_format_desc', 'VBSEO_URL_CMS_SECTION_PAGE', array(
'content/section/[section_id]-[section_title]/index[page].html',
'section/[section_id]-[section_title]/index[page].html',
)),
array('cmscat_format_desc', 'VBSEO_URL_CMS_CATEGORY', array(
'content/[category_id]-[category_title]/',
'category/[category_id]-[category_title]/',
)),
array('cmscatpage_format_desc', 'VBSEO_URL_CMS_CATEGORY_PAGE', array(
'content/[category_id]-[category_title]/index[page].html',
'category/[category_id]-[category_title]/index[page].html',
)),
array('cmsauthor_format_desc', 'VBSEO_URL_CMS_AUTHOR', array(
'authors/[user_id]-[user_name]/',
'authors/[user_id]/',
)),
array('cmsauthorpage_format_desc', 'VBSEO_URL_CMS_AUTHOR_PAGE', array(
'authors/[user_id]-[user_name]/index[page].html',
'authors/[user_id]/index[page].html',
)),
array('cmsentry_format_desc', 'VBSEO_URL_CMS_ENTRY', array(
'content/[entry_id]-[entry_title].html',
'content/[section_id]-[section_title]/[entry_id]-[entry_title].html',
'content/[section_title]/[entry_id]-[entry_title].html',
)),
array('cmsentrypage_format_desc', 'VBSEO_URL_CMS_ENTRY_PAGE', array(
'content/[entry_id]-[entry_title]-page[page].html',
'content/[section_id]-[section_title]/[entry_id]-[entry_title]/index[page].html',
'content/[section_title]/[entry_id]-[entry_title]/index[page].html',
)),
array('cmsentrycompage_format_desc', 'VBSEO_URL_CMS_ENTRY_COMPAGE', array(
'content/[entry_id]-[entry_title]-comments[page].html',
'content/[section_id]-[section_title]/[entry_id]-[entry_title]/comments[page].html',
'content/[section_title]/[entry_id]-[entry_title]/comments[page].html',
)),
array('cmsattach_format_desc', 'VBSEO_URL_CMS_ATTACHMENT', array(
'attachments/cms/[attachment_id]-[original_filename]',
'content/attachments/[attachment_id]-[original_filename]',
)),
);
break;
case 'url_blog':
$ftype = 'blog';
$aopt = array(
array('rw_blog_desc', 'VBSEO_REWRITE_BLOGS'),
array('blogdom_desc', 'VBSEO_URL_BLOG_DOMAIN', -1),
array('bloghome_format_desc', 'VBSEO_URL_BLOG_HOME', array(
'members/blogs.html',
'blogs/',
)),
array('bloguser_format_desc', 'VBSEO_URL_BLOG_USER', array(
'members/[user_name]/blog.html',
'blogs/[user_name]/',
'blogs/[user_id]/',
'blogs/[user_id]-[user_name].html',
)),
array('bloguserpage_format_desc', 'VBSEO_URL_BLOG_USER_PAGE', array(
'members/[user_name]/blog-page[page].html',
'blogs/[user_name]/index[page].html',
'blogs/[user_id]/index[page].html',
'blogs/[user_id]-[user_name]-page[page].html',
)),
'urls_blog_entry',
array('rw_blogent_desc', 'VBSEO_REWRITE_BLOGS_ENT'),
array('blogind_format_desc', 'VBSEO_URL_BLOG_ENTRY', array(
'members/[user_name]/[blog_id]-[blog_title].html',
'blogs/[user_name]/[blog_id]-[blog_title].html',
'blogs/[user_id]/blog[blog_id].html',
'blogs/blog[blog_id]-[blog_title].html',
)),
array('blogindpage_format_desc', 'VBSEO_URL_BLOG_ENTRY_PAGE', array(
'members/[user_name]/[blog_id]-[blog_title]-page[page].html',
'blogs/[user_name]/[blog_id]-[blog_title]-page[page].html',
'blogs/[user_id]/blog[blog_id]-page[page].html',
'blogs/blog[blog_id]-[blog_title]-page[page].html',
)),
array('blogindredir_format_desc', 'VBSEO_URL_BLOG_ENTRY_REDIR', array(
'members/comments/comment[comment_id].html',
'blogs/comments/comment[comment_id].html',
'blogs/comment[comment_id].html',
)),
array('blognext_format_desc', 'VBSEO_URL_BLOG_NEXT', array(
'members/[user_name]/[blog_id]-[blog_title]-next.html',
'blogs/[user_name]/[blog_id]-[blog_title]-next.html',
'blogs/[user_id]/blog[blog_id]-next.html',
'blogs/blog[blog_id]-[blog_title]-next.html',
)),
array('blogprev_format_desc', 'VBSEO_URL_BLOG_PREV', array(
'members/[user_name]/[blog_id]-[blog_title]-prev.html',
'blogs/[user_name]/[blog_id]-[blog_title]-prev.html',
'blogs/[user_id]/blog[blog_id]-prev.html',
'blogs/blog[blog_id]-[blog_title]-prev.html',
)),
'urls_blog_cst_page',
array('rw_blog_cst_page_desc', 'VBSEO_REWRITE_BLOGS_CUSTOM'),
array('blog_cst_page_desc', 'VBSEO_URL_BLOG_CUSTOM', array(
'members/[user_name]/custom[page_id]-[page_title].html',
'blogs/[user_name]/custom[page_id]-[page_title].html',
'blogs/[user_id]/custom[page_id].html',
'blogs/custom[page_id]-[page_title].html',
)),
'urls_blog_cat',
array('rw_blogcat_desc', 'VBSEO_REWRITE_BLOGS_CAT'),
array('blogglobcat_format_desc', 'VBSEO_URL_BLOG_GLOB_CAT', array(
'members/categories/[category_title]/',
'blogs/categories/[category_title]/',
'blogs/categories/category[category_id]/',
'blogs/category[category_id]-[category_title].html',
)),
array('blogglobcatpage_format_desc', 'VBSEO_URL_BLOG_GLOB_CAT_PAGE', array(
'members/categories/[category_title]/index[page].html',
'blogs/categories/[category_title]/index[page].html',
'blogs/categories/category[category_id]/index[page].html',
'blogs/category[category_id]-[category_title]-page[page].html',
)),
array('blogcat_format_desc', 'VBSEO_URL_BLOG_CAT', array(
'members/[user_name]/[category_title]/',
'blogs/[user_name]/[category_title]/',
'blogs/[user_id]/category[category_id]/',
'blogs/[user_id]-[user_name]-category[category_id]-[category_title].html'
)),
array('blogcatpage_format_desc', 'VBSEO_URL_BLOG_CAT_PAGE', array(
'members/[user_name]/[category_title]/index[page].html',
'blogs/[user_name]/[category_title]/index[page].html',
'blogs/[user_id]/category[category_id]/index[page].html',
'blogs/[user_id]-[user_name]-category[category_id]-[category_title]-page[page].html'
)),
'urls_blog_att',
array('rw_blogatt_desc', 'VBSEO_REWRITE_BLOGS_ATT'),
array('blogatt_format_desc', 'VBSEO_URL_BLOG_ATT', array(
'members/[user_name]/attachments/[attachment_id]-[blog_title]-[original_filename]',
'blogs/[user_name]/attachments/[attachment_id]-[blog_title]-[original_filename]',
'blogs/[user_id]/attachments/[attachment_id]-[original_filename]',
'blogs/attachment[attachment_id]-[blog_title]-[original_filename]',
)),
'urls_blog_feed',
array('rw_blogfeed_desc', 'VBSEO_REWRITE_BLOGS_FEED'),
array('blogfeeduser_format_desc', 'VBSEO_URL_BLOG_FEEDUSER', array(
'members/[user_name]/feed.rss',
'blogs/[user_name]/feed.rss',
'blogs/[user_id]/feed.rss',
'blogs/[user_id]-[user_name]-feed.rss',
)),
array('blogfeed_format_desc', 'VBSEO_URL_BLOG_FEED', array(
'members/feed.rss',
'blogs/feed.rss',
)),
'urls_blog_list',
array('rw_bloglist_desc', 'VBSEO_REWRITE_BLOGS_LIST'),
array('bloguday_format_desc', 'VBSEO_URL_BLOG_UDAY', array(
'members/[user_name]/[year]/[month]/[day]/',
'blogs/[user_name]/[year]/[month]/[day]/',
'blogs/[user_id]/[year]/[month]/[day]/',
'blogs/[user_id]-[user_name]-[year]-[month]-[day].html',
)),
array('blogday_format_desc', 'VBSEO_URL_BLOG_DAY', array(
'members/[year]/[month]/[day]/',
'blogs/[year]/[month]/[day]/',
'blogs/[year]-[month]-[day].html',
)),
array('blogdaypage_format_desc', 'VBSEO_URL_BLOG_DAY_PAGE', array(
'members/[year]/[month]/[day]/index[page].html',
'blogs/[year]/[month]/[day]/index[page].html',
'blogs/[year]-[month]-[day]-page[page].html',
)),
array('blogumonth_format_desc', 'VBSEO_URL_BLOG_UMONTH', array(
'members/[user_name]/[year]/[month]/',
'blogs/[user_name]/[year]/[month]/',
'blogs/[user_id]/[year]/[month]/',
'blogs/[user_id]-[user_name]-[year]-[month].html',
)),
array('blogmonth_format_desc', 'VBSEO_URL_BLOG_MONTH', array(
'members/[year]/[month]/',
'blogs/[year]/[month]/',
'blogs/[year]-[month].html',
)),
array('blogmonthpage_format_desc', 'VBSEO_URL_BLOG_MONTH_PAGE', array(
'members/[year]/[month]/index[page].html',
'blogs/[year]/[month]/index[page].html',
'blogs/[year]-[month]-page[page].html',
)),
array('blogblist_format_desc', 'VBSEO_URL_BLOG_BLIST', array(
'members/blogs/',
'blogs/all/',
'blogs/all.html',
)),
array('blogblistpage_format_desc', 'VBSEO_URL_BLOG_BLIST_PAGE', array(
'members/blogs/index[page].html',
'blogs/all/index[page].html',
'blogs/all-[page].html',
)),
array('bloglist_format_desc', 'VBSEO_URL_BLOG_LIST', array(
'members/recent-entries/',
'blogs/recent-entries/',
'blogs/recent-entries.html',
)),
array('bloglistpage_format_desc', 'VBSEO_URL_BLOG_LIST_PAGE', array(
'members/recent-entries/index[page].html',
'blogs/recent-entries/index[page].html',
'blogs/recent-entries-[page].html',
)),
array('bloglatestentries_format_desc', 'VBSEO_URL_BLOG_LAST_ENT', array(
'members/latest-entries/',
'blogs/latest-entries/',
'blogs/latest-entries.html',
)),
array('bloglatestentriespage_format_desc', 'VBSEO_URL_BLOG_LAST_ENT_PAGE', array(
'members/latest-entries/index[page].html',
'blogs/latest-entries/index[page].html',
'blogs/latest-entries-[page].html',
)),
array('blogbestentry_format_desc', 'VBSEO_URL_BLOG_BEST_ENT', array(
'members/best-entries/',
'blogs/best-entries/',
'blogs/best-entries.html',
)),
array('blogbestentpage_format_desc', 'VBSEO_URL_BLOG_BEST_ENT_PAGE', array(
'members/best-entries/index[page].html',
'blogs/best-entries/index[page].html',
'blogs/best-entries-[page].html',
)),
array('blogbestblogs_format_desc', 'VBSEO_URL_BLOG_BEST_BLOGS', array(
'members/best-blogs/',
'blogs/best-blogs/',
'blogs/best-blogs.html',
)),
array('blogbestblogspage_format_desc', 'VBSEO_URL_BLOG_BEST_BLOGS_PAGE', array(
'members/best-blogs/index[page].html',
'blogs/best-blogs/index[page].html',
'blogs/best-blogs-[page].html',
)),
array('blogclist_format_desc', 'VBSEO_URL_BLOG_CLIST', array(
'members/comments/',
'blogs/comments/',
'blogs/comments.html',
)),
array('blogclistpage_format_desc', 'VBSEO_URL_BLOG_CLIST_PAGE', array(
'members/comments/index[page].html',
'blogs/comments/index[page].html',
'blogs/comments-[page].html',
)),
'urls_blog_tags',
array('rw_blog_tags_desc', 'VBSEO_REWRITE_BLOGS_TAGS_ENTRY'),
array('blog_tags_home_desc', 'VBSEO_URL_BLOG_TAGS_HOME', array(
'members/tags/',
'blogs/tags/',
)),
array('blog_tags_desc', 'VBSEO_URL_BLOG_TAGS_ENTRY', array(
'members/tags/[tag]/',
'blogs/tags/[tag]/',
'blogs/tags/[tag].html',
)),
array('blog_tags_desc_page', 'VBSEO_URL_BLOG_TAGS_ENTRY_PAGE', array(
'members/tags/[tag]/index[page].html',
'blogs/tags/[tag]/index[page].html',
'blogs/tags/[tag]-page[page].html',
)),
);
break;
case 'linkbacks':
$ttlitem = 'linkbacks';
$opts1 .= self::display_option_yesno('in_pingback_desc', 'VBSEO_IN_PINGBACK');
$opts1 .= self::display_option_yesno('in_trackback_desc', 'VBSEO_IN_TRACKBACK');
$opts1 .= self::display_option_yesno('in_refback_desc', 'VBSEO_IN_REFBACK');
$opts1 .= self::display_option_yesno('ignore_linkback_dupe_desc', 'VBSEO_LINKBACK_IGNOREDUPE');
$opts1 .= self::display_option_yesno('pingback_notify_desc', 'VBSEO_PINGBACK_NOTIFY',
vBSEO_CP::lang('pingback_notify_bcc_desc').':<br />'.
self::display_option_textfield('VBSEO_PINGBACK_NOTIFY_BCC')
);
$opts1 .= self::display_option_text('pingback_hits_desc', 'VBSEO_LINKBACK_SHOWHITS_UG',
'', vBSEO_CP::lang('pingback_hits_top').'<br />');
$opts1 .= self::display_option_area('pingback_stopwords_desc', 'VBSEO_PINGBACK_STOPWORDS', '|');
$opts1 .= self::display_option_area('linkback_black_desc', 'linkback_bl_skip', 'l');
$opts2 .= self::display_option_yesno('pingback_desc', 'VBSEO_EXT_PINGBACK');
$opts2 .= self::display_option_yesno('trackback_desc', 'VBSEO_EXT_TRACKBACK');
$opts2 .= self::display_option_area('pingback_service_desc', 'VBSEO_PINGBACK_SERVICE', '|');
$repl['options'] = $opts1;
$repl['options2'] = $opts2;
break;
case 'permalinks':
$ttlitem = 'permalinks';
if(!VBSEO_VB4)
{	
$opts .= self::display_header('permalink_forum');
$opts .= self::display_option_select('postbit_pingback_desc', 'VBSEO_POSTBIT_PINGBACK', 
array(
1 => self::lang('ping_postbit_1'),
3 => self::lang('ping_postbit_3'),
2 => self::lang('ping_postbit_2'),
0 => self::lang('ping_postbit_0')
));
}
$opts .= self::display_header('permalink_profile');
$opts .= self::display_option_select('permalink_profile_desc', 'VBSEO_PERMALINK_PROFILE', 
array(
1 => self::lang('pl_profile_1'),
2 => self::lang('pl_profile_2'),
0 => self::lang('pl_profile_0')
));
$opts .= self::display_header('permalink_album');
$opts .= self::display_option_select('permalink_pic_desc', 'VBSEO_PERMALINK_ALBUM', 
array(
1 => self::lang('pl_profile_1'),
2 => self::lang('pl_profile_2'),
0 => self::lang('pl_profile_0')
));
$opts .= self::display_header('permalink_blog');
$opts .= self::display_option_select('permalink_blog_desc', 'VBSEO_PERMALINK_BLOG', 
array(
1 => self::lang('pl_blog_1'),
2 => self::lang('pl_blog_2'),
0 => self::lang('pl_blog_0')
));
$opts .= self::display_header('permalink_groups');
$opts .= self::display_option_select('permalink_groups_desc', 'VBSEO_PERMALINK_GROUPS', 
array(
1 => self::lang('pl_groups_1'),
2 => self::lang('pl_groups_2'),
0 => self::lang('pl_groups_0')
));
$opts .= self::display_header('permalink_groupspic');
$opts .= self::display_option_select('permalink_groupspic_desc', 'VBSEO_PERMALINK_GROUPS_PIC', 
array(
1 => self::lang('pl_groupspic_1'),
2 => self::lang('pl_groupspic_2'),
0 => self::lang('pl_groupspic_0')
));
break;
case 'crrs':
$ttlitem = 'thecrrs';
$repl['crrs'] = vBSEO_Storage::setting('custom_rules_text');
break;
case 'custom301':
$ttlitem = 'thec301';
$repl['c301'] = vBSEO_Storage::setting('custom_301_text');
break;
case 'rel_replacements':
$ttlitem = 'rel_replacements';
$repl['rr'] = vBSEO_Storage::setting('relev_repl');
$repl['rr_t'] = vBSEO_Storage::setting('relev_repl_t');
$repl['rr_cms'] = vBSEO_Storage::setting('relev_repl_cms');
$repl['rr_div'] = vBSEO_Storage::setting('VBSEO_RELEV_REPLACE_DIV') ? 'checked="checked" ' : '';
break;
case 'dyn_metas':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('metakw_desc', 'VBSEO_REWRITE_META_KEYWORDS');
$opts .= self::display_option_yesno('metadesc_desc', 'VBSEO_REWRITE_META_DESCRIPTION');
$opts .= self::display_option_text('metadesc_len_desc', 'VBSEO_META_DESCRIPTION_MAX_CHARS');
$opts .= self::display_option_text('metadesc_members_desc', 'VBSEO_META_DESCRIPTION_MEMBER');
break;
case 'dir_links':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('nextprev_desc', 'VBSEO_URL_THREAD_NEXT_DIRECT');
$opts .= self::display_option_yesno('flinks_desc', 'VBSEO_FORUMLINK_DIRECT');
break;
case 'add_ttitle':
$ttlitem = 'seo_func';
$opts .= self::display_option_select('addttl_desc', 'VBSEO_REWRITE_THREADS_ADDTITLE', 
array(
0 => self::lang('attl_1'),
1 => self::lang('attl_2'),
2 => self::lang('attl_3'),
3 => self::lang('attl_4')
));
$opts .= self::display_option_yesno('addttlp_desc', 'VBSEO_REWRITE_THREADS_ADDTITLE_POST');
break;
case 'add_extttitle':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('addttlext_desc', 'VBSEO_REWRITE_EXT_ADDTITLE');
$opts .= self::display_option_area('addttl_black_desc', 'VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST', '|');
break;
case 'stopwords':
$ttlitem = 'seo_func';
$o1 = vBSEO_Storage::setting('VBSEO_FILTER_STOPWORDS');
$o2 = vBSEO_Storage::setting('VBSEO_KEEP_STOPWORDS_SHORT');
$selopt = $o1 ? ($o2 ? 2 : 1) : 0;
$opts .= self::display_option_select('remstop_desc', 'combo_stopwords', 
array(
1 => self::lang('yes'),
0 => self::lang('no'),
2 => self::lang('smart'),
), $selopt);
$opts .= self::display_option_area('stopdef_desc', 'VBSEO_STOPWORDS', '|');
break;
case 'acronym':
$ttlitem = 'seo_func';
$opts .= self::display_option_select('acronym_desc', 'VBSEO_ACRONYM_SET', 
array(
0 => self::lang('acro_opt1'),
1 => self::lang('acro_opt2'),
2 => self::lang('acro_opt3'),
3 => self::lang('acro_opt4'),
));
$opts .= self::display_option_area('acronym_def_desc', 'acronyms');
$opts .= self::display_option_yesno('acro_content_desc', 'VBSEO_ACRONYMS_IN_CONTENT');
$opts .= self::display_option_yesno('acro_url_desc', 'VBSEO_REWRITE_KEYWORDS_IN_URLS');
break;
case 'hp_set':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('forceroot_desc', 'VBSEO_HP_FORCEINDEXROOT');
$opts .= self::display_option_area('hpalias_desc', 'VBSEO_HOMEPAGE_ALIASES', '|');
break;
case 'int_rel':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('intrel_desc', 'VBSEO_NOFOLLOW_SORT');
$opts .= self::display_option_yesno('reldyn_desc', 'VBSEO_NOFOLLOW_DYNA');
$opts .= self::display_option_yesno('relmemb_desc', 'VBSEO_NOFOLLOW_MEMBER_POSTBIT');
$opts .= self::display_option_yesno('relmemb_home_desc', 'VBSEO_NOFOLLOW_MEMBER_FORUMHOME');
break;
case 'ext_rel':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('extrel_desc', 'VBSEO_NOFOLLOW_EXTERNAL');
$opts .= self::display_option_area('extwhite_desc', 'VBSEO_DOMAINS_WHITELIST', '|');
$opts .= self::display_option_area('extblack_desc', 'VBSEO_DOMAINS_BLACKLIST', '|');
break;
case 'canonical_link':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('canonical_link_desc', 'VBSEO_CANONIC_LINK_TAG');
break;
case 'virt_html':
$ttlitem = 'seo_func';
$o1 = vBSEO_Storage::setting('VBSEO_VIRTUAL_HTML');
$o2 = vBSEO_Storage::setting('VBSEO_VIRTUAL_HTML_GUESTS_ONLY');
$selopt = $o1 ? ($o2 ? 2 : 1) : 0;
$opts .= self::display_option_select('virt_html_desc', 'combo_canonical', 
array(
0 => self::lang('vhtml_opt0'),
1 => self::lang('vhtml_opt1'),
2 => self::lang('vhtml_opt2'),
), $selopt);
break;
case 'replace_tag_title':
$ttlitem = 'seo_func';
$opts .= self::display_option_yesno('replace_tag_title_desc', 'VBSEO_REPLACE_TAG_TITLE');
break;
case 'cleanup_html':
$ttlitem = 'oth_enh';
$opts .= self::display_option_yesno('clean_html_desc', 'VBSEO_CODE_CLEANUP');
break;
case 'footer_arc':
$ttlitem = 'oth_enh';
$opts .= self::display_option_select('rw_archive_foot_desc', 'VBSEO_ARCHIVE_LINKS_FOOTER', 
array(
0 => self::lang('arc_opt0'),
1 => self::lang('arc_opt1'),
2 => self::lang('arc_opt2'),
3 => self::lang('arc_opt3'),
4 => self::lang('arc_opt4'),
));
break;
case 'catlinks':
$ttlitem = 'oth_enh';
$opts .= self::display_option_yesno('catlinks_desc', 'VBSEO_CATEGORY_ANCHOR_LINKS');
break;
case 'guests_only':
$ttlitem = 'oth_enh';
$opts .= self::display_option_yesno('forum_jump_desc', 'VBSEO_FORUMJUMP_OFF');
$opts .= self::display_option_yesno('rem_prev_desc', 'VBSEO_CODE_CLEANUP_PREVIEW');
$opts .= self::display_option_yesno('dir_links_threads_desc', 'VBSEO_DIRECTLINKS_THREADS');
$opts .= self::display_option_yesno('rem_member_dropdown_desc', 'VBSEO_CODE_CLEANUP_MEMBER_DROPDOWN');
$opts .= self::display_option_select('cleanup_lastpost_desc', 'VBSEO_CODE_CLEANUP_LASTPOST', 
array(
1 => self::lang('cleanup_lastpost_links'),
2 => self::lang('cleanup_lastpost_column'),
0 => self::lang('cleanup_lastpost_none'),
));
break;
case 'bookmark':
$ttlitem = 'oth_enh';
$bm = self::display_option_cb_field('VBSEO_BOOKMARK_DIGG', '<a rel="bookmark" href="http://www.digg.com" target="_blank">digg.com</a>');
$bm .= self::display_option_cb_field('VBSEO_BOOKMARK_DELICIOUS', '<a rel="bookmark" href="http://del.icio.us" target="_blank">del.icio.us</a>');
$bm .= self::display_option_cb_field('VBSEO_BOOKMARK_TECHNORATI', '<a rel="bookmark" href="http://technorati.com" target="_blank">technorati.com</a>');
$bm .= self::display_option_cb_field('VBSEO_BOOKMARK_FURL', '<a rel="bookmark" href="http://twitter.com" target="_blank">twitter.com</a>');
$bm .= self::display_option_cb_field('VBSEO_BOOKMARK_CUSTOM', self::lang('bookmark_custom'), 
'onclick="$bmc=$(\'#bmcustom\');if(this.checked)$bmc.show();else $bmc.hide();"');
$bm .= '<div id="bmcustom"'.(vBSEO_Storage::setting('VBSEO_BOOKMARK_CUSTOM') ? '' : ' style="display:none"').'>' . 
self::display_option_area('', 'VBSEO_BOOKMARK_SERVICES', '|', vBSEO_CP::lang('bookmark_services_desc'))
.'</div>';
$o1 = vBSEO_Storage::setting('VBSEO_BOOKMARK_THREAD');
$o2 = vBSEO_Storage::setting('VBSEO_BOOKMARK_POST');
$selopt = $o1 ? ($o2 ? 1 : 2) : 0;
$opts .= self::display_option_select('bookmark_desc', 'combo_bookmark', 
array(
0 => self::lang('bookmark_none'),
1 => self::lang('bookmark_threadpost'),
2 => self::lang('bookmark_thread'),
), $selopt,  $bm);
$opts .= self::display_option_yesno('blog_bookm_desc', 'VBSEO_BOOKMARK_BLOG');
if(VBSEO_VB4)
$opts .= self::display_option_yesno('cms_bookm_desc', 'VBSEO_BOOKMARK_CMS');
break;
case 'img_size':
$ttlitem = 'oth_enh';
$opts .= self::display_option_yesno('img_size_desc', 'VBSEO_IMAGES_DIM');
$opts .= self::display_option_area('img_def_desc', 'images_dim', 'x', self::lang('img_note'));
break;
}
$repl['class_vb4'] = VBSEO_VB4 ? '' : 'none';
if($ftype)
{
$ttlitem = 'url_opt';
$repl['ftype'] = $ftype;
$repl['urls_desc'] = vBSEO_CP::lang($ftype.'_opt_description');
$presetmatch = self::check_preset_match($presets_def, $ftype);
$presets = $presets_def['presets'];
array_unshift($presets, vBSEO_CP::lang('none'));
$opts = '';
foreach($aopt as $op)
if($op)
{
if(!is_array($op))
$opts .= self::display_header($op);
else
if($op[2] == -1)
$opts .= self::display_option_text($op[0], $op[1]);
else
if($op[2])
{
if($op[3] == 1)
$opts .= self::display_option_radio($op[0], $op[1], $op[2]);
else
$opts .= self::display_option_formats($op[0], $op[1], $op[2]);
}else
$opts .= self::display_option_yesno($op[0], $op[1]);
}
$repl['presets'] = self::display_option_select('', 'preset', $presets, $presetmatch);
$repl['preset_color'] = $presetmatch? 'green' : 'red';
$repl['preset_text'] = $presetmatch ? $presets[$presetmatch] : vBSEO_CP::lang('custom_settings');
}
if($opts)
{
$repl['subheader'] = self::lang($litem);
$repl['options'] = $opts;
if($tpl == $litem)
$tpl = strstr($litem, 'url_') ?  'urls' : 'options';
if($opts2)
{        
$repl['options2'] = $opts2;
$tpl = 'options2';
}
}
$result['title']  = self::lang($ttlitem);
$result['desc']   = self::lang( $ttlitem . '_desc');
if(!$result['title'])
{
$result['title'] = self::lang('loading_error');
}
$result['output'] = self::output_content($tpl, $repl);
return $result;
}
}
