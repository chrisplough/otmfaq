//
// vB Spell v0.9.7 Copyrights 2005 LowCarber.org @ http://forum.lowcarber.org
// vB Spell is licensed under the GPL v2 or later
// License terms are available at http://www.gnu.org/licenses/gpl.txt
//
// Based on: Pungo Spell Copyright (c) 2003 Billy Cook, Barry Johnson Licensed under the MIT License
// And phpSpell: phpSpell 1.06o (beta) Spelling Engine (c)Copyright 2002, 2003, Team phpSpell. Licensed under the GPL License
//

// Public functions --------------------------------

var ie = (document.all) ? 1:0;
var ns = (navigator.userAgent.indexOf('compatible') >= -1) ? 1:0;

function spellCheck( formName, fieldName, editmode ) {

        var h_spellform = document.forms['spell_form'];

        h_spellform.spell_formname.value = formName;
        h_spellform.spell_fieldname.value = fieldName;

        if (document.getElementById(fieldName + '_iframe') == undefined)
        {
                h_spellform.spellstring.value = document.forms[formName]["message"].value;
        }
        else
        {
                var iframe = document.getElementById(fieldName + '_iframe');
                var iframedoc = iframe.contentWindow.document;
                var oHtml = iframedoc.body;
                h_spellform.spellstring.value = oHtml.innerHTML;
        }

        openSpellWin(640, 480);
        h_spellform.submit();
        return true;
}

// Private functions -------------------------------

// globals
var wordindex = -1;
var offsetindex = 0;
var ignoredWords = Array();


// mispelled word object
//
function misp(word, start, end, suggestions) {

        this.word = word;               // the word
        this.start = start;             // start index
        this.end = end;                 // end index
        this.suggestions = suggestions; // array of suggestions
}

// replace the word in the misps array at the "wordindex" index.  The
// misps array is generated by a PHP script after the string to be spell
// checked is evaluated with pspell
//
function replaceWord() {

        var frm = document.fm1;
        var strstart = '';
        var strend;

        // if this isn't the beginning of the string then get all of the string
        // that is before the word we are replacing
        if ( misps[ wordindex ].start != 0 )
        strstart = mispstr.slice( 0, misps[ wordindex ].start + offsetindex);

        // get the end of the string after the word we are replacing
        strend = mispstr.slice( misps[ wordindex ].end + 1 + offsetindex);

        // rebuild the string with the new word
        mispstr = strstart +  frm.changeto.value  + strend;

        // update offsetindex to compensate for replacing a word with a word
        // of a different length.
        offsetindex += frm.changeto.value.length - misps[ wordindex ].word.length;

        // update the word so future replaceAll calls don't change it
        misps[ wordindex ].word = frm.changeto.value;

        nextWord(false);
}


function thesarusWord()
{
        var frm = document.fm1;
        window.open("http://www.m-w.com/cgi-bin/thesaurus?book=Thesaurus&va="+frm.changeto.value, "dictionary", "width=630,resizable=yes,scrollbars=yes,height=500");

}

function lookupWord()
{
        var frm = document.fm1;
        window.open("http://www.m-w.com/cgi-bin/dictionary?book=Dictionary&va="+frm.changeto.value, "dictionary", "width=630,resizable=yes,scrollbars=yes,height=500");
}

function Get_Cookie(sName)
{
        var aCookie = document.cookie;
        if (aCookie == null) return (null);
        aCookie = aCookie.split("; ");
        for (var i=0; i < aCookie.length; i++)
        {
                var aCrumb = aCookie[i].split("=");
                if (sName == aCrumb[0])
                return unescape(aCrumb[1]);
        }
        return null;
}

function Set_Cookie(Cookie_Name, Cookie_Value)
{
        var Expires = new Date();
        Expires.setDate(Expires.getDate() + 365);
        var NewCookie = Cookie_Name + "=" + escape(Cookie_Value) + "; expires="+Expires.toGMTString()+";";
        document.cookie = NewCookie;
}
function learnWord()
{
        Word = misps[ wordindex ].word;
        Cookie = Get_Cookie("vbspell_words");
        if (Cookie == null) Cookie = Word;
        else Cookie = Cookie + "," + Word;
        Set_Cookie("vbspell_words",Cookie);
        nextWord(true);
}

function exitWord()
{
        window.close();
}

function doneWord() {

        var frm = document.fm1;
        var sug = document.fm1.suggestions;
        var sugidx = 0;
        var newopt;
        var isselected = 0;

        iFrameBody.innerHTML = mispstr;


        if (window.opener.document.getElementById(spell_fieldname + '_iframe') == undefined)
        {
                iFrameBody.innerHTML = iFrameBody.innerHTML.replace(/_\|_/g, "<br>");
        }
        else
        {
                iFrameBody.innerHTML = iFrameBody.innerHTML.replace(/_\|_/g, " ");
        }

        clearBox( sug );

        frm.change.disabled = true;
        frm.changeall.disabled = true;
        frm.ignore.disabled = true;
        frm.ignoreall.disabled = true;

        // put line feeds back
        mispstr = mispstr.replace(/_\|_/g, "\n");

        if (window.opener.document.getElementById(spell_fieldname + '_iframe') == undefined)
        {
                if (window.opener.document.getElementById(spell_fieldname + '_textarea').innerHTML == '')
                {
                        window.opener.document.forms[spell_formname]["message"].value = mispstr;
                }
                else
                {
                        var oHtml = window.opener.document.getElementById(spell_fieldname + '_textarea');
                        oHtml.innerHTML = mispstr;
                        window.opener.document.forms[spell_formname]["message"].value = mispstr;
                }
        }
        else
        {
                var iframe = window.opener.document.getElementById(spell_fieldname + '_iframe');
                var iframedoc = iframe.contentWindow.document;
                var oHtml = iframedoc.body;

                oHtml.innerHTML = mispstr;
        }

        window.close();
        return true;
}

// replaces all instances of currently selected word with contents chosen by user.
// note: currently only replaces words after hilighted word.  I think we can re-index
// all words at replacement or ignore time to have it wrap to the beginning if we want
// to.
//
function replaceAll() {

        var frm = document.fm1;
        var strstart = '';
        var strend;
        var idx;
        var origword;
        var localoffsetindex = offsetindex;

        origword = misps[ wordindex ].word;

        // reindex everything past the current word
        for (idx = wordindex; idx < misps.length; idx++) {
                misps[ idx ].start += localoffsetindex;
                misps[ idx ].end += localoffsetindex;
        }

        // testing
        localoffsetindex = 0;

        for (idx = 0; idx < misps.length; idx++) {

                if (misps[ idx ].word == origword) {
                        if ( misps[ idx ].start != 0 )
                        strstart = mispstr.slice( 0, misps[ idx ].start + localoffsetindex);


                        // get the end of the string after the word we are replacing
                        strend = mispstr.slice( misps[ idx ].end + 1 + localoffsetindex);

                        // rebuild the string with the new word
                        mispstr = strstart +  frm.changeto.value  + strend;

                        // update offsetindex to compensate for replacing a word with a word
                        // of a different length.
                        localoffsetindex += frm.changeto.value.length - misps[ idx ].word.length;

                }
                // we have to re-index everything after replacements
                misps[ idx ].start += localoffsetindex;
                misps[ idx ].end += localoffsetindex;
        }

        // add the word to the ignore array
        ignoredWords[ origword ] = 1;

        // reset offsetindex since we reindexed
        offsetindex = 0;

        nextWord(false);
}

// hilight the word that was selected using the nextWord function
//
function hilightWord() {

        var strstart = '';
        var strend;

        // if this isn't the beginning of the string then get all of the string
        // that is before the word we are replacing

        if ( misps[ wordindex ].start != 0 )
                strstart = mispstr.slice( 0, misps[ wordindex ].start + offsetindex);

        // get the end of the string after the word we are replacing

        strend = mispstr.slice( misps[ wordindex ].end + 1 + offsetindex);

        // rebuild the string with a span wrapped around the misspelled word
        // so we can hilight it in the div the user is viewing the string in

        //var divptr = document.getElementById("strview");
        var divptr = iFrameBody;

        divptr.innerHTML = '';
        divptr.innerHTML = strstart;

        divptr.innerHTML +=  "<a name='comehere'> </a=> <span class='highlight' id='h1' name='hl'> " + misps[ wordindex ].word + " </span>" + htmlToText(strend);

        if (window.opener.document.getElementById(spell_fieldname + '_iframe') == undefined)
        {
                divptr.innerHTML = divptr.innerHTML.replace(/_\|_/g, "<br>");
        }
        else
        {
                divptr.innerHTML = divptr.innerHTML.replace(/_\|_/g, " ");
        }
}

// called by onLoad handler to start the process of evaluating misspelled
// words
//
function startsp() {

        nextWord(false);
}

function getCorrectedText() {

        return mispstr;
}

// display the next misspelled word to the user and populate the suggested
// spellings box
//
function nextWord(ignoreall) {

        var frm = document.fm1;
        var sug = document.fm1.suggestions;
        var sugidx = 0;
        var newopt;
        var isselected = 0;

        // push ignored word onto ingoredWords array
        if (ignoreall)
                ignoredWords[ misps[ wordindex ].word ] = 1;

        // update the index of all words we have processed
        // This must be done to accomodate the replaceAll function.
        if (wordindex >= 0) {
                misps[ wordindex ].start += offsetindex;
                misps[ wordindex ].end += offsetindex;
        }

        // increment the counter for the array of misspelled words
        wordindex++;

        // draw it and quit if there are no more misspelled words to evaluate
        if (misps.length <= wordindex) {
                iFrameBody.innerHTML = mispstr;

                if (window.opener.document.getElementById(spell_fieldname + '_iframe') == undefined)
                {
                        iFrameBody.innerHTML = iFrameBody.innerHTML.replace(/_\|_/g, "<br>");
                }
                else
                {
                        iFrameBody.innerHTML = iFrameBody.innerHTML.replace(/_\|_/g, " ");
                }

                clearBox( sug );

                alert('Spell checking complete.');

                frm.change.disabled = true;
                frm.changeall.disabled = true;
                frm.ignore.disabled = true;
                frm.ignoreall.disabled = true;

                // put line feeds back
                mispstr = mispstr.replace(/_\|_/g, "\n");

                if (window.opener.document.getElementById(spell_fieldname + '_iframe') == undefined)
                {
                        if (window.opener.document.getElementById(spell_fieldname + '_textarea').innerHTML == '')
                        {
                                window.opener.document.forms[spell_formname]["message"].value = mispstr;
                        }
                        else
                        {
                                var oHtml = window.opener.document.getElementById(spell_fieldname + '_textarea');
                                oHtml.innerHTML = mispstr;
                                window.opener.document.forms[spell_formname]["message"].value = mispstr;
                        }
                }
                else
                {
                        var iframe = window.opener.document.getElementById(spell_fieldname + '_iframe');
                        var iframedoc = iframe.contentWindow.document;
                        var oHtml = iframedoc.body;

                        oHtml.innerHTML = mispstr;
                }

                window.close();
                return true;
        }


        // check to see if word is supposed to be ignored
        if (ignoredWords[ misps[ wordindex ].word ] == 1) {
                nextWord(false);
                return;
        }

        // clear out the suggestions box
        clearBox( sug );

        // re-populate the suggestions box if there are any suggested spellings for the word
        if (misps[ wordindex ].suggestions.length) {
                for (sugidx = 0; sugidx < misps[ wordindex ].suggestions.length; sugidx++) {
                        if (sugidx == 0)
                                isselected = 1;
                        else
                                isselected = 0;
                        newopt = new Option(misps[ wordindex ].suggestions[sugidx], misps[ wordindex ].suggestions[sugidx], 0, isselected);
                        sug.options[ sugidx ] = newopt;

                        if (isselected) {
                                frm.changeto.value = misps[ wordindex ].suggestions[sugidx];
                                frm.changeto.select();
                        }
                }
        }

        hilightWord();

        parent.spellbox.document.getElementById("h1").scrollIntoView(ns);
}

function htmlToText(thetext) {

        // disable for now
        return thetext;

        var re = /\</g;
        var re2 = /\>/g;
        var re3 = /\n/g;
        var re4 = /\ /g;

        thetext = thetext.replace(re, "&lt;");
        thetext = thetext.replace(re2, "&gt;");
        thetext = thetext.replace(re3, "<br>");
        thetext = thetext.replace(re4, "&nbsp;");

        return thetext;
}

// remove all items from the suggested spelling box
//
function clearBox( box ) {

        var length = box.length;

        // delete old options -- rememeber that select
        //                       boxes automatically re-index
        for (i = 0; i < length; i++) {
                box.options[0] = null;
        }
}

function openSpellWin(wx, hx) {

        window.open("", "spellWindow", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width='+wx+',height='+hx);
        window.focus;
}