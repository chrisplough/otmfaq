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

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

// display the credits table for use in admin/mod control panels

print_form_header('index', 'home');
print_table_header($vbphrase['vbulletin_developers_and_contributors']);
print_column_style_code(array('white-space: nowrap', ''));
print_label_row('<b>' . $vbphrase['software_developed_by'] . '</b>', '
	<a href="http://www.vbulletin.com/" target="vbulletin">vBulletin Solutions, Inc.</a>,
	<a href="http://www.internetbrands.com/" target="vbulletin">Internet Brands, Inc.</a>
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['business_development'] . '</b>', '
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=226054" target="vbulletin">Adrian Harris</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=290027" target="vbulletin">Fabian Schonholz</a>
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['engineering'] . '</b>', '
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=214190" target="vbulletin">Kevin Sours</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=224" target="vbulletin">Freddie Bingham</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=250802" target="vbulletin">Edwin Brown</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=260819" target="vbulletin">Andrew Elkins</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=23871" target="vbulletin">Xiaoyu Huang</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=48331" target="vbulletin">Colin Frei</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=264173" target="vbulletin">Joan Gauna</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=216095" target="vbulletin">David Grove</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=297944" target="vbulletin">Eric Johney</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=299765" target="vbulletin">Zoltan Szalay</a>
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['product_manager'] . '</b>', '
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=259494" target="vbulletin">Don Kuramura</a>
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['graphics_development'] . '</b>', '
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=268290" target="vbulletin">Sophie Xie</a>
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['qa'] . '</b>', '
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=255358" target="vbulletin">Allen Lin</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=255355-fleung" target="vbulletin">Fei Leung</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=287901-Kevin-Connery" target="vbulletin">Kevin Connery</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=255359" target="vbulletin">Meghan Sensenbach</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=284061" target="vbulletin">Michael Lavaveshkul</a>
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['support'] . '</b>', '
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=656" target="vbulletin">Steve Machol</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=868" target="vbulletin">Wayne Luke</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=30801" target="vbulletin">Carrie Anderson</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=245" target="vbulletin">George Liu</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=2026" target="vbulletin">Jake Bunce</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=19405" target="vbulletin">Zachery Woods</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=60067" target="vbulletin">Marco van Herwaarden</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=168289" target="vbulletin">Marlena Machol</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=19738" target="vbulletin">Trevor Hannant</a>,
	<a href="http://www.vbulletin.com/forum/member.ph' . 'p?u=57702" target="vbulletin">Lynne Sands</a>
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['special_thanks_to'] . '</b>', '
	Adrian Sacchi, Ajinkya Apte, Andreas Kirbach, Andy Huang, Aston Jay, Bob Pankala,
	Brian Swearingen, Brian Gunter, Chen Avinadav, Chevy Revata, Chris Holland, Christopher Riley,
	Daniel Clements, David Bonilla, David Webb, David Yancy, Dominic Schlatter, Don T. Romrell,
	Doron Rosenberg, Elmer Hernandez, Fernando Munoz, Floris Fiedeldij Dop,
	Giovanni Martinez, Hanafi Jamil, Hanson Wong, Hartmut Voss, Ivan Anfimov, Jacquii Cooke,
	Jan Allan Zischke, Jelle Van Loo, Jen Rundell, Jeremy Dentel, Joe Rosenblum, Joe Velez, Joel Young, John Jakubowski,
	John Percival, John Simpson, Jonathan Javier Coletta, Joseph DeTomaso, Kevin Schumacher,
	Kevin Wilkinson, Kier Darby, Kira Lerner, Kolby Bothe, Lisa Swift, Mark James,
	Martin Meredith, Matthew Gordon, Mert Gokceimam, Michael Biddle, Michael Henretty,
	Michael Kellogg, Michael \'Mystics\' K&ouml;nig, Michael Pierce, Mike Sullivan,
	Milad Kawas Cale, Nathan Wingate, Nawar Al-Mubaraki, Ole Vik, Overgrow, Paul Marsden,
	Peggy Lynn Gurney, Prince Shah, Pritesh Shah, Robert Beavan White, Ryan Royal,
	Sal Colascione III, Scott MacVicar, Scott Molinari, Scott William, Sebastiano Vassellatti,
	Shawn Vowell, Stephan \'pogo\' Pogodalla, Sven Keller, Tom Murphy, Tony Phoenix,
	Torstein H&oslash;nsi, Tully Rankin, Vinayak Gupta, Yves Rigaud
	', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['other_contributions_from'] . '</b>', '
	<a href="http://www.vikjavev.com/hovudsida/umtestside.ph' . 'p" target="vbulletin">Torstein H&oslash;nsi</a>,
	<a href="http://www.famfamfam.com/lab/icons/silk/" target="vbulletin">Mark James</a>
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['copyright_enforcement_by'] . '</b>', '
	<a href="http://www.vbulletin.com/" target="vbulletin">vBulletin Solutions, Inc.</a>
', '', 'top', NULL, false);
print_table_footer();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 38640 $
|| ####################################################################
\*======================================================================*/
?>