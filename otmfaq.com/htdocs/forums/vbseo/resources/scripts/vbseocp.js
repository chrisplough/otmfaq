/**
 * vBSEO Control Panel
 *
 */

function vBSEOControlPanel() 
{

this.flags = {
cpscript: 'vbseocp.php',
itemname: 'dashboard',
curitem: '',
curtab: '',
noticeid: 0
};
this.lang = {
'pleasewait': 'Please wait...'
};
this.init = function()
{
	$(document).ready(function(){

	    var lhash = document.location.hash;
		if(lhash.length>0)
			vbseocp.flags['itemname'] = lhash.substr(1);

		$('#main-nav a').each(function(){
			var ln = $(this).attr('href');
			if((ln.charAt(0) == '#') && (ln != '#'))
			{     
				if(ln == '#url_cms')
				if(!vbseocp.flags['isvb4']||(vbseocp.flags['iscms'] == '0'))
					$(this).remove();

				if(ln == '#url_blog')
				{
				if(vbseocp.flags['isblog'] == '0')
					$(this).remove();
				}
				$(this).click(function(){
				  window.location.href=(this.href);
				  vbseocp.load_item(ln.substring(1), this);

				  return false;
				});
			}
		});

		vbseocp.load_item('', lhash ? $('a[href='+lhash+']') : '');
	});
};

this.c_text = function (title)
{
	return (typeof title == 'undefined') ? '' : title;
};

this.c_title = function (title)
{
	$('#main-content h2').html(this.c_text(title ? title : this.oldtitle));
	if(title && title!=this.lang['pleasewait'])this.oldtitle = title;
};

this.c_desc = function (title)
{
	if(title)
	$('#page-intro').html(this.c_text(title));
};

this.loading = function ()
{
	this.c_title(this.lang['pleasewait']);
	$('#target_area').fadeTo('normal', '0.5');
};

this.supp_msg_cookie = function (html, cset)
{
	var suppress_msg = $.cookie('vbseocp_supp');
	if(suppress_msg === null)
		suppress_msg = '';

	var hstrip = /<\S[^><]*>/g;
	html = html.replace(hstrip, '');
	var hash = $.md5(html)+'-';
	var exists = suppress_msg.indexOf(hash)>=0;
	if(cset && !exists)
		$.cookie('vbseocp_supp', suppress_msg+hash)

	return exists;
};

this.load_item = function (itemname, menuentry)
{
	this.flags['curtab'] = '';
	if(typeof menuentry != 'undefined')
	{
		$('a').removeClass('current');
		$(menuentry).addClass('current');
		if(!$(menuentry).hasClass('nav-top-item'))
		$(menuentry).parent().parent().parent().find('.nav-top-item').addClass('current');
	}
	this.loading();
	if(itemname)
		this.flags['itemname'] = itemname;

	$.post(	this.flags['cpscript'],
		{
		load: this.flags['itemname'],
		previtem: this.flags['curitem']
		},
		this.load_item_complete,
		'xml'
	);
	this.flags['curitem'] = this.flags['itemname'];
	
	return false;
};

this.load_item_complete = function (data)
{
	var adata = tb_xml2array(data);
	adata = adata['data'];
	vbseocp.c_title(adata['title']);
	vbseocp.c_desc(adata['desc']);
	$('#target_area').fadeTo('normal', '1.0');
	$('.notification').hide();

	if(vbseocp.flags['itemname'] == 'dashboard')
		$('#vbseocp_ls').show();
		else
		$('#vbseocp_ls').hide();

	if(typeof adata['messages'] != 'undefined')
	for(var mi in adata['messages'])
	{
		var msg = adata['messages'][mi];
		if(msg[mi+'0'] == 'save_success')
			$('.save_success').fadeIn().animate({opacity:1}, 5000, 'linear', function(){$(this).fadeOut();});
		else
		if(msg[mi+'0'] == 'save_warning')
			$('.save_warning').fadeIn().animate({opacity:1}, 5000, 'linear', function(){$(this).fadeOut();});
		else
		if(typeof msg[mi + '1'] != 'undefined')
		if(!vbseocp.supp_msg_cookie(msg[mi + '1'], false))
		{
		$('#msg_tpl')
			.clone()
			.attr('id', 'notice_' + (vbseocp.flags['noticeid']++))
			.addClass('notification ' + msg[mi + '0'])
			.insertBefore('#cp_form')
			.fadeIn()
			.slideDown()
			.find('div')
			.html(msg[mi + '1']);
		}
	}

	$('.attention,.info').find('.close').click(function() {
		vbseocp.supp_msg_cookie($(this).next().html(), true);
	});
	setTimeout(function(){$('.notification:.success').slideUp();}, 5000);


	if(adata['output'])
	{
		$('#target_area').html(vbseocp.c_text(adata['output']));
		simpla_content_init();
		if(vbseocp.flags['curtab'])
		$('a[href='+vbseocp.flags['curtab']+']').click();
	}
};

this.load_preset = function (type, msg)
{
	if(!confirm(msg))
		return false;
	var params = {load: this.flags['itemname'], loadpreset: $('#preset').val(), type: type};
	this.loading();

	$.post(	this.flags['cpscript'], params,
		this.load_item_complete,
		'xml'
	);
	
	return false;
};

this.submit_form = function ()
{
	this.flags['curtab'] = $('.content-box-tabs .current').attr('href');
	var params = {load: this.flags['itemname']};
	$('#target_area *').each(function(){
		if((typeof this.name != 'undefined') && (this.name.indexOf('setting') == 0))
		{
			if((this.type == 'radio') && !this.checked)
				return;
			if(this.type == 'checkbox')
			params[this.name] = $(this).is(':checked') ? 1 : 0;
			else
			params[this.name] = $(this).val();
		}
	});
	this.loading();

	$.post(	this.flags['cpscript'], params,
		this.load_item_complete,
		'xml'
	);
	
	return false;
};

this.init();

}

vbseocp = new vBSEOControlPanel();
