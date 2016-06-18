<?php
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
if (!VB_API) die;

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'activeusers' => $VB_API_WHITELIST_COMMON['activeusers'],
		'announcebits' => array(
			'*' => array(
				'announcement' => array(
					'announcementid', 'title', 'views', 'username', 'userid', 'usertitle',
					'postdate', 'posttime'
				),
				'announcementidlink',
				'foruminfo' => array(
					'forumid'
				)
			)
		),
		'daysprune', 'daysprunesel',
		'forumbits' => array(
			'*' => $VB_API_WHITELIST_COMMON['forumbit']
		),
		'foruminfo' => $VB_API_WHITELIST_COMMON['foruminfo'],
		'forumrules', 'limitlower',
		'limitupper',
		'moderatorslist' => array(
			'*' => array(
				'moderator' => $VB_API_WHITELIST_COMMON['moderator']
			)
		),
		'numberguest', 'numberregistered',
		'order', 'pageinfo_flastpost', 'pageinfo_postusername', 'pageinfo_replycount',
		'pageinfo_title', 'pageinfo_views', 'pageinfo_voteavg', 'pagenumber',
		'perpage', 'prefix_options', 'prefix_selected', 'sort',
		'threadbits' => array(
			'*' => $VB_API_WHITELIST_COMMON['threadbit']
		),
		'threadbits_sticky' => array(
			'*' => $VB_API_WHITELIST_COMMON['threadbit']
		),
		'totalmods', 'totalonline', 'totalthreads',
		'pagenav' => $VB_API_WHITELIST_COMMON['pagenav'],
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/