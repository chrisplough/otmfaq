<?php
//
// vB Spell v0.9.7 Copyrights 2005 LowCarber.org @ http://forum.lowcarber.org
// vB Spell is licensed under the GPL v2 or later
// License terms are available at http://www.gnu.org/licenses/gpl.txt
//
// Based on: Pungo Spell Copyright (c) 2003 Billy Cook, Barry Johnson Licensed under the MIT License
// And phpSpell: phpSpell 1.06o (beta) Spelling Engine (c)Copyright 2002, 2003, Team phpSpell. Licensed under the GPL License
//

error_reporting(E_ALL & ~E_NOTICE);

define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'vbspell_iframe');

// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

include('global.php');

if (REFERRER != '' AND strpos(REFERRER, "vbspell") === false) {
        $url = REFERRER;
        eval(print_standard_redirect("vB Spell cannot be accessed directly", 0));
}
echo <<<html
<html>
<head>
$style[css]
<script>
function assignSelf() {
   window.parent.iFrameBody = window.document.getElementById("theBody");
}
</script>
<title>vB Spell</title>
</head>
<body class="wysiwyg" onLoad="assignSelf(); window.parent.startsp();" id="theBody">
vB Spell installation problem, please notify the webmaster.
</body>
</html>
html;
?>