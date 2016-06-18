/************************************************************************************
* vBSEO 3.6.0 PL2 for vBulletin v3.x & v4.x by Crawlability, Inc.                       *
*-----------------------------------------------------------------------------------*
*                                                                                   *
* vBSEO AJAX Functions (LinkBacks Moderation)                                       *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license.html                                    *
************************************************************************************/

var vbseoLinkbackEditor = null;

function vBSEO_linkback_mod_Init(linkback_id, action)
{
    if(AJAX_Compatible)
    {
		new vBSEO_linkback(linkback_id, action)
	}
	return false
}

function vBSEO_linkback(linkback_id, action)
{
	this.link_ajax = null
	this.linkback_id = linkback_id
	this.action = action
	var me = this;

    this.linkback_change = function(elem, linkback_approved)
    {
   		var c1 = 'highlight', c2 = ('alt'+( (i%2) ? '2' : '1'));
   		if(linkback_approved)
   		{
   			c3 = c1; c1 = c2; c2 = c3;
   		}
   		return elem.className.replace(c1, '')+' '+c2;
    }

    this.linkback_mod = function()
    {
       	this.link_ajax = new vB_AJAX_Handler(true);
       	this.link_ajax.onreadystatechange(this.linkback_mod_ready);
       	var par = 'do=linkbackmod&id=' + this.linkback_id + '&action=' + this.action + '&';
       	this.link_ajax.send('ajax.php?' + par, par);
  		if(this.action == 'mod')
  		{
			var linkback_img = fetch_object('linkbackimg_' + this.linkback_id);
  			var linkback_approved = ( linkback_img.title == vbphrase['vbseo_mod_unapprove'] );
			linkback_img.title = linkback_approved ? vbphrase['vbseo_mod_approve'] : vbphrase['vbseo_mod_unapprove'];

  			var linkback_row = fetch_object('linkback_' + this.linkback_id);
  	     	var linkback_cells = fetch_tags(linkback_row, 'td');
  	     	linkback_row.className = this.linkback_change(linkback_row, linkback_approved);
           	for (var i = 0; i < linkback_cells.length; i++)
           	{
	  	     	linkback_cells[i].className = this.linkback_change(linkback_cells[i], linkback_approved);
  	     	}

  		}else
  		if(this.action == 'del' || this.action == 'ban')
  		{
  			var linkback_row = fetch_object('linkback_' + this.linkback_id);
  			linkback_row.style.display = 'none'
  		}

        return false;
    }

    this.linkback_mod_ready = function()
    {
    	if (me.link_ajax.handler.readyState == 4 && me.link_ajax.handler.status == 200)
    	{
    		if (me.link_ajax.handler.responseText)
    		{
    		}

    		if (is_ie)
    		{
    			me.link_ajax.handler.abort();
    		}
    	}
    }

    return this.linkback_mod ()
}


function vbseo_linkback_ondblclick(e)
{
	if (vbseoLinkbackEditor && vbseoLinkbackEditor.obj == e)
	{
		return false;
	}
	else
	{
		if(vbseoLinkbackEditor)
		try
		{
			vbseoLinkbackEditor.restore();
		}
		catch(e) {}

		vbseoLinkbackEditor = new vbseo_Linkback_Edit(e);
	}
}

function vbseo_linkback_onblur(e)
{
	vbseoLinkbackEditor.restore();
}

function vbseo_linkback_onkeypress(e)
{
	e = e ? e : window.event;
	switch (e.keyCode)
	{
		case 13:
		{
			vbseoLinkbackEditor.inputobj.blur();
			return false;
		}
		case 27:
		{
			vbseoLinkbackEditor.inputobj.value = vbseoLinkbackEditor.origtitle;
			vbseoLinkbackEditor.inputobj.blur();
			return true;
		}
	}
}

function vbseo_Linkback_Edit(obj)
{
	this.obj = obj;
	this.linkbackid = this.obj.id.substr(this.obj.id.lastIndexOf('_') + 1);
	this.linkobj = fetch_object('linkback_title_' + this.linkbackid);
	this.container = this.linkobj.parentNode;
	this.editobj = null;
	this.xml_sender = null;

	this.origtitle = '';
	this.editstate = false;

	this.edit = function()
	{
		if (this.editstate == false)
		{
			this.inputobj = document.createElement('input');
			this.inputobj.type = 'text';
			this.inputobj.size = 50;

			this.inputobj.maxLength = ((typeof(titlemaxchars) == "number" && titlemaxchars > 0) ? titlemaxchars : 85);
			this.inputobj.style.width = Math.max(this.linkobj.offsetWidth, 250) + 'px';
			this.inputobj.className = 'smallfont';
			this.inputobj.value = PHP.unhtmlspecialchars(this.linkobj.innerHTML);
			this.inputobj.title = this.inputobj.value;

			this.inputobj.onblur = vbseo_linkback_onblur
			this.inputobj.onkeypress = vbseo_linkback_onkeypress

			this.editobj = this.container.insertBefore(this.inputobj, this.linkobj);
			this.editobj.select();

			this.origtitle = this.linkobj.innerHTML;

			this.linkobj.style.display = 'none';

			this.editstate = true;
		}
	}

	this.restore = function()
	{
		if (this.editstate == true)
		{
			if (this.editobj.value != this.origtitle)
			{
				this.linkobj.innerHTML = PHP.htmlspecialchars(this.editobj.value);
				this.save(this.editobj.value);
			}
			else
			{
				this.linkobj.innerHTML = this.editobj.value;
			}

			this.container.removeChild(this.editobj);

			this.linkobj.style.display = '';

			this.editstate = false;
			this.obj = null;
		}
	}

	this.save = function(titletext)
	{
		this.xml_sender = new vB_AJAX_Handler(true);
		this.xml_sender.onreadystatechange(this.onreadystatechange);
		var par = 'do=updatelinkback&linkid=' + this.linkbackid + '&title=' + PHP.urlencode(titletext) + '&';
		this.xml_sender.send('ajax.php?' + par, par);
	}

	var me = this;

	this.onreadystatechange = function()
	{
		if (me.xml_sender.handler.readyState == 4 && me.xml_sender.handler.status == 200)
		{
			if (me.xml_sender.handler.responseXML)
			{
				me.linkobj.innerHTML = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'linkhtml')[0]);
			}

			if (is_ie)
			{
				me.xml_sender.handler.abort();
			}

			vbseoLinkbackEditor.obj = null;
		}
	}

	this.edit();
}