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

$VB_API_WHITELIST = array(
	'response' => array(
		'customfieldsheader', 'first', 'last', 'leadergroup',
		'memberlistbits' => array(
			'*' => array(
				'userinfo' => array(
					'username', 'userid', 'musername', 'usertitle',
					'icq', 'aim', 'yahoo', 'skype', 'msn', 'homepage',
					'datejoined', 'posts', 'lastvisittime',
					'reputationdisplay' => array(
						'posneg',
						'post' => array(
							'username', 'level'
						)
					),
					'profilepic', 'birthday', 'age'
				),
				'customfields', 'avatarurl',
				'show' => array(
					'searchlink', 'emaillink', 'homepagelink', 'pmlink', 'avatar',
					'hideleader'
				)
			)
		),
		'pagenav', 'perpage', 'searchtime', 'totalcols', 'totalusers',
		'usergroupid', 'oppositesort'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/