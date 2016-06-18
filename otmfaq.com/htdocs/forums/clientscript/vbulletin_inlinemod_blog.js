/*!======================================================================*\
|| #################################################################### ||
|| # vBulletin 1.0.5
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################
// vB_Inline_Mod_Blog
// #############################################################################

/**
* Inline Moderation Class - extension of vB_Inline_Mod
* Handles Selection of Blog Comments / Trackbacks
*
* @param	string	Name of the instance of this class
* @param	string	Type of system (comment/trackback)
* @param	string	ID of the form containing all checkboxes
* @param	string	Phrase for use on Go button
* @param	string	Name of cookie
*/
function vB_Inline_Mod_Blog(varname, type, formobjid, go_phrase, cookieprefix)
{
	vB_Inline_Mod_Blog.baseConstructor.call(this, varname, type, formobjid, go_phrase, cookieprefix);
	this.id = this;
}

vBulletin.extend(vB_Inline_Mod_Blog, vB_Inline_Mod);

vB_Inline_Mod_Blog.prototype.highlight_comment = function(checkbox)
{
	this.highlight_table(checkbox);
}

vB_Inline_Mod_Blog.prototype.highlight_trackback = function(checkbox)
{
	this.highlight_table(checkbox);
}

vB_Inline_Mod_Blog.prototype.highlight_blog = function(checkbox)
{
	this.highlight_table(checkbox);
}

vB_Inline_Mod_Blog.prototype.highlight_pcomment = function(checkbox)
{
	// only td.inlinemod is defined in the CSS so we need to highlight a table or add a div.inlinemod definition
	this.highlight_table(checkbox);
}

vB_Inline_Mod_Blog.prototype.highlight_table = function(checkbox)
{
	if (table = fetch_object(this.type + checkbox.id.substr(this.type.length + 5)))
	{
		tds = fetch_tags(table, 'td');
		for (var i = 0; i < tds.length; i++)
		{
			this.toggle_highlight(tds[i], checkbox);
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:15, Tue Dec 9th 2008
|| # CVS: $RCSfile$ - $Revision: 25812 $
|| ####################################################################
\*======================================================================*/