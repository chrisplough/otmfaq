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

define('VB_API_LOADLANG', true);

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'blocks' => array(
			'visitors' => array(
				'html' => array(
					'block_data' => array(
						'visitorbits' => array(
							'*' => array(
								'user' => array(
									'userid', 'username', 'musername', 'invisiblemark',
									'buddymark'
								)
							)
						)
					)
				)
			),
			'groups' => array(
				'html' => array(
					'block_data' => array(
						'socialgroupbits' => array(
							'*' => array(
								'showgrouplink',
								'socialgroup' => array(
									'groupid', 'shortdescription', 'iconurl', 'name',
									'name_html'
								)
							)
						),
						'membergroupbits' => array(
							'*' => array(
								'usergroup' => array(
									'usergroupid', 'opentag', 'title', 'closetag'
								)
							)
						)
					)
				)
			),
			'visitor_messaging' => array(
				'block_data' => array(
					'messagebits' => array(
						'*' => array(
							'message' => array(
								'vmid', 'avatarurl', 'postuserid', 'userid', 'username',
								'postuserid', 'musername', 'profileusername', 'date',
								'time', 'message', 'hostuserid', 'guestuserid',
								'converse_description_phrase'
							),
							'show' => array(
								'profile', 'detailedtime', 'moderation', 'edit',
								'converse', 'reportlink'
							)
						)
					),
					'lastcomment',
					'pagenav', 'pagenumber', 'messagetotal'
				),
			),
			'stats' => array(
				'html' => array(
					'block_data'
				)
			),
			'aboutme' => array(
				'block_data' => array(
					'fields' => array(
						'*' => array(
							'category' => array(
								'title', 'description',
								'fields' => array(
									'*' => array(
										'profilefield' => array(
											'profilefieldid', 'title', 'value'
										)
									)
								)
							)
						)
					)
				)
			),
			'friends' => array(
				'block_data' => array(
					'start_friends', 'end_friends', 'showtotal', 'pagenav',
					'friendbits' => array(
						'*' => array(
							'remove' => array(
								'userid', 'return'
							),
							'user' => array(
								'userid', 'username', 'musername',
								'onlinestatus' => array('onlinestatus'),
								'usertitle', 'showicq', 'showmsn', 'showaim', 'showyahoo',
								'showskype', 'icq', 'msn', 'aim', 'yahoo',
								'skype', 'avatarurl'
							),
							'show' => array(
								'breakfriendship'
							)
						)
					)
				)
			),
		),
		'prepared' => array(
			'birthday', 'age', 'signature', 'userid', 'username', 'displayemail',
			'homepage', 'profileurl', 'hasimdetails', 'usertitle', 'profilepicurl',
			'onlinestatus' => array('onlinestatus'),
			'canbefriend', 'homepage', 'usernotecount', 'joindate', 'action',
			'where', 'lastactivitydate', 'posts', 'avatarurl'
		), 'selected_tab',
		'userinfo' => array(
			'icq', 'msn', 'aim', 'yahoo', 'skype'
		)
	),
	'vboptions' => array(
		'postminchars'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/