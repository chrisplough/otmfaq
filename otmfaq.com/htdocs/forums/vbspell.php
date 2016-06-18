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
define('THIS_SCRIPT', 'vbspell');

// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array('vbspell');

// pre-cache templates used by specific actions
$actiontemplates = array();

include('global.php');

if ($vbulletin->options['vbspell_allow_all'] OR is_member_of($vbulletin->userinfo, explode(',', $vbulletin->options['vbspell_use_groups'])))
        $vbulletin->userinfo['can_use_vbspell'] = TRUE;

if (!$vbulletin->userinfo['can_use_vbspell'] OR $vbulletin->userinfo['usergroupid'] == 0) print_no_permission();

$PersonalWords = array();

if (!empty($_COOKIE['vbspell_words']))
        $PersonalWords = explode(',', urldecode(strtolower($_COOKIE['vbspell_words'])));

require_once('./includes/searchwords.php'); // get search engine stop words, to save on queries.

function MisSpelled($word)
{
        global $db, $badwords, $PersonalWords;

        $word = trim($word, "'");

        $partial  = explode('\'', strtolower($word));

        if (count($partial) > 1) $CheckPartial = TRUE;
        else $CheckPartial = FALSE;

        if (in_array($partial[0], $PersonalWords)) return FALSE; // User have "learned" this word
        elseif ($CheckPartial AND in_array(strtolower($word), $PersonalWords)) return FALSE; // User have "learned" this word

        elseif ($word === strtoupper($word)) return FALSE; // All uppercase, treat as abbriviation, might want to make this an admincp option..

        elseif (in_array($partial[0], $badwords)) return FALSE; // Very common word
        elseif ($CheckPartial AND in_array($partial[0] . $partial[1], $badwords)) return FALSE; // Very common word

        elseif ($db->query_first("SELECT word FROM vbspell WHERE word = '" . addslashes($partial[0]) . "'")) return FALSE; // Found In dictionary

        else return TRUE;  // probably mis-spelled
}

function Suggest($for)
{
        global $db, $vbulletin;

        $for = trim($for, "'");

        $WordList = array();
        $SuggestedList = array();

        $Suggestions = $db->query_read("SELECT word FROM vbspell WHERE sound = '" . addslashes(metaphone($for)) . "'");

        while ($Suggestion = $db->fetch_array($Suggestions))
                $WordList[$Suggestion['word']] = levenshtein($for, $Suggestion['word']);

        asort($WordList);
        reset($WordList);

        foreach ($WordList as $word => $distance)
                if ($distance <= $vbulletin->options['vbspell_levenshtein_distance']) $SuggestedList[] = $word;

        if ($for === ucfirst($for)) {

                foreach ($SuggestedList as $key => $word)
                        $SuggestedList[$key] = ucfirst($word);
        }

        elseif ($for === strtoupper($for)) { // not possible yet, but might be allowed in admincp.

                foreach ($SuggestedList as $key => $word)
                        $SuggestedList[$key] = strtoupper($word);
        }

        return $SuggestedList;
}

function strip_attributes ($html, $attrs) {
  if (!is_array($attrs)) {
    $array= array( "$attrs" );
    unset($attrs);
    $attrs= $array;
  }

  foreach ($attrs AS $attribute) {
    // once for ", once for ', s makes the dot match linebreaks, too.
    $search[]= "/".$attribute.'\s*=\s*".+"/Uis';
    $search[]= "/".$attribute."\s*=\s*'.+'/Uis";
    // and once more for unquoted attributes
    $search[]= "/".$attribute."\s*=\s*\S+/i";
  }
  $html= preg_replace($search, "", $html);

  // do another pass and strip_tags() if matches are still found
  foreach ($search AS $pattern) {
    if (preg_match($pattern, $html)) {
      $html= strip_tags($html);
      break;
    }
  }

  return $html;
}

// the safe_html() function
//   note, there is a special format for $allowedtags, see ~line 90
function safe_html ($html, $allowedtags="") {
/* safe_html.php

   http://chxo.com/scripts/safe_html/

   Copyright 2003 by Chris Snyder (csnyder@chxo.com)
   Free to use and redistribute, but see License and Disclaimer below

     - Huge thanks to James Wetterau for initial testing and feedback!
     - Originally posted at http://lists.nyphp.org/pipermail/talk/2003-May/003832.html

Version History:
2005-09-05 - 0.5 -- upgrade to handle cases at http://ha.ckers.org/xss.html
2005-04-24 - 0.4 -- added check for encoded ascii entities
2003-05-31 - 0.3 -- initial public release

License and Disclaimer:
Copyright 2003 Chris Snyder. All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice, this
   list of conditions and the following disclaimer in the documentation and/or other
   materials provided with the distribution.

THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;  LOSS OF USE, DATA, OR PROFITS;
OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/
  // anything with ="javascript: is right out -- strip all tags if found
  $pattern= "/=[\S\s]*s\s*c\s*r\s*i\s*p\s*t\s*:\s*\S+/Ui";
  if (preg_match($pattern, $html)) {
    $html= strip_tags($html);
    return $html;
  }

  // anything with encoded entites inside of tags is out, too
  $pattern= "/<[\S\s]*&#[x0-9]*[\S\s]*>/Ui";
  if (preg_match($pattern, $html)) {
    $html= strip_tags($html);
    return $html;
  }

  // setup -- $allowedtags is an array of $tag=>$closeit pairs,
  //   where $tag is an HTML tag to allow and $closeit is 1 if the tag
  //   requires a matching, closing tag
  if ($allowedtags=="") {
    $allowedtags= array ( "p"=>1, "br"=>1, "a"=>1, "img"=>1,
                        "li"=>1, "ol"=>1, "ul"=>1, "font"=>1,
                        "b"=>1, "i"=>1, "em"=>1, "strong"=>1,
                        "del"=>1, "ins"=>1, "u"=>1, "code"=>1, "pre"=>1,
                        "blockquote"=>1, "hr"=>1
                        );
  }
  elseif (!is_array($allowedtags)) {
    $array= array( "$allowedtags" );
  }

  // there's some debate about this.. is strip_tags() better than rolling your own regex?
  // note: a bug in PHP 4.3.1 caused improper handling of ! in tag attributes when using strip_tags()
  $stripallowed= "";
  foreach ($allowedtags AS $tag=>$closeit) {
    $stripallowed.= "<$tag>";
  }

  //print "Stripallowed: $stripallowed -- ".print_r($allowedtags,1);
  $html= strip_tags($html, $stripallowed);

  // also, lets get rid of some pesky attributes that may be set on the remaining tags...
  // this should be changed to keep_attributes($htmlm $goodattrs), or perhaps even better keep_attributes
  //  should be run first. then strip_attributes, if it finds any of those, should cause safe_html to strip all tags.
  $badattrs= array("on\w+", "style", "fs\w+", "seek\w+");
  $html= strip_attributes($html, $badattrs);

  // close html tags if necessary -- note that this WON'T be graceful formatting-wise, it just has to fix any maliciousness
  foreach ($allowedtags AS $tag=>$closeit) {
    if (!$closeit) continue;
    $patternopen= "/<$tag\b[^>]*>/Ui";
    $patternclose= "/<\/$tag\b[^>]*>/Ui";
    $totalopen= preg_match_all ( $patternopen, $html, $matches );
    $totalclose= preg_match_all ( $patternclose, $html, $matches2 );
    if ($totalopen>$totalclose) {
      $html.= str_repeat("</$tag>", ($totalopen - $totalclose));
    }
  }

  return $html;
}

$mystr = str_replace('\\', '\\\\', $_REQUEST['spellstring']);
$mystr = stripslashes($mystr);
$mystr = safe_html($mystr);

$FormName = $_POST['spell_formname'];
$FieldName = $_POST['spell_fieldname'];

// can't have newlines or carriage returns in javascript string
$mystr = str_replace("\r", "", $mystr);
$mystr = str_replace("\n", "_|_", $mystr);

$mystr = trim($mystr);

$ignore_sets = '\[QUOT.+\].*\[\/QUOTE\]|';
$ignore_sets .= '\[CODE\].*\[\/CODE\]|';
$ignore_sets .= '\[PHP\].*\[\/PHP\]|';
$ignore_sets .= '\[img\].*\[\/img\]|';
$ignore_sets .= '\[url.*\].*\[\/url\]|';
$ignore_sets .= '<a.*>.*<\/a>|';

preg_match_all ( "/" . $ignore_sets . "\[[^\]]+\]|[[:alpha:]']+|<[^>]+>|&[^;\ ]+;/im", $mystr, $alphas, PREG_OFFSET_CAPTURE|PREG_PATTERN_ORDER);
$mystr = str_replace('\\', '\\\\', $mystr);

// this has to be done _after_ the matching.  it messes up the
// indexing otherwise.  I have not figured out exactly why this
// happens but I know this fixes it.

$mystr = str_replace("\"", "\\\"", $mystr);

$js .= 'var mispstr = "'.$mystr.'";'."\n";

$js .= 'var misps = Array(';
$curindex = 0;

for($i = 0; $i < sizeof($alphas[0]); $i++) {

        // if the word is an html tag or entity then skip it
        if (preg_match("/<[^>]+>|&[^;\ ]+;/", $alphas[0][$i][0]))  continue;

        // ignore quotes
        if (preg_match("/\[QUOTE\].\[\/QUOTE\]/ism", $alphas[0][$i][0]))  continue;

        // ignore BBCODE/VBCODE tags
        if (preg_match("/\[[^\]]+/", $alphas[0][$i][0])) continue;

        if (MisSpelled($alphas[0][$i][0])) {

                $js .= "new misp('" . str_replace("'", "\\'",$alphas[0][$i][0]) . "',". $alphas[0][$i][1] . "," . (strlen($alphas[0][$i][0]) + ($alphas[0][$i][1] - 1) ) . ",[";

                $suggestions = Suggest($alphas[0][$i][0]);

                foreach ($suggestions as $suggestion) {
                        $sugs[] = "'".str_replace("'", "\\'", $suggestion)."'";
                }

                if (sizeof($sugs)) {
                        $js .= join(",", $sugs);
                }

                unset($sugs);

                $js .= "]),\n";
                $sugs_found = 1;
        }
}

if ($sugs_found)
        $js = substr($js, 0, -2);

$js .= ");";

eval('print_output("' . fetch_template('vbspell') . '");');

?>
