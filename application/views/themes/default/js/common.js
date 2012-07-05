var _interval = 0;
var _save_page_interval = 0;
var current_interval = 0;
var _save_scroll = true;

var loadimg_sml = new Image(16,16);
loadimg_sml.src = _site_domain + 'application/media/images/loading_small.gif';

/**
*	cache the progress indicator image to show faster...
*/
var Image1 = new Image(16,16);
Image1.src = _site_domain + 'application/media/images/loading.gif';

/**
*	Show a progress indicator to inform user that something
*	is happening...
*/
function show_progress(the_id, info_str, size_str) {
	switch (size_str) {
		case "small": case "tiny":
			size_str = loadimg_sml.src;
			break;
		case "large": case "big":
			size_str = Image1.src;
			break;
		default:
			size_str = loadimg_sml.src;
			break;
	}
	$("#" + the_id).html('<img id="progress_image_id" src="' + size_str + '"> <em>' + info_str +'</em>').show();
}

function show_message(the_id, info_str) {
	$("#" + the_id).html('<em>' + info_str +'</em>').show();
}
	

function switch_image(html_id, src)
{
	$('#' + html_id).attr('src', src);
}

function object_action(action,the_id)
{
	var parts = the_id.split('|');
	var type = false;
	var name = false;
	var service = false;
	switch(parts.length) {
		case 0: case 1: return false;
			break;
		case 2: // host or groups
			name = parts[1];
			break;
		case 3: // service
			name = parts[1];
			service = parts[2];
			break;
		case 4: // service
			name = parts[1];
			service = parts[3];
			break;
	}

	type = parts[0];

	var cmd = false;
	switch(action) {
		case 'schedule_host_downtime':
		case 'schedule_svc_downtime':
		case 'del_host_downtime':
		case 'del_svc_downtime':
		case 'acknowledge_host_problem':
		case 'acknowledge_svc_problem':
		case 'disable_host_svc_notifications':
		case 'disable_host_check':
		case 'disable_svc_check':
		case 'enable_host_check':
		case 'enable_svc_check':
		case 'schedule_host_check':
		case 'schedule_host_svc_checks':
		case 'schedule_svc_check':
		case 'add_host_comment':
		case 'add_svc_comment':
			cmd = action.toUpperCase();
			break;
		case 'remove_acknowledgement':
			cmd = type == 'host' ? 'REMOVE_HOST_ACKNOWLEDGEMENT' : 'REMOVE_SVC_ACKNOWLEDGEMENT';
			break;
		case 'disable_notifications':
			cmd = type == 'host' ? 'DISABLE_HOST_NOTIFICATIONS' : 'DISABLE_SVC_NOTIFICATIONS';
			break;
		case 'enable_notifications':
			cmd = type == 'host' ? 'ENABLE_HOST_NOTIFICATIONS' : 'ENABLE_SVC_NOTIFICATIONS';
			break;
	}

	// return if we couldn't figure out what command to run
	if (cmd == false) {
		return false;
	}

	var target = _site_domain + _index_page + '/command/submit?cmd_typ=' + cmd + '&host_name=' + name;
	if (service != false) {
		target += '&service=' + service;
	}
	self.location.href = target;
}

/**
*	Handle multi select of different actions
*/
function multi_action_select(action, type)
{
	// start by enabling all checkboxes in case
	// they have been previously disabled
	var field = 'item_select';
	var prop_field = 'obj_prop';
	if (type == 'service') {
		$(".item_select_service input[type='checkbox']").attr('disabled', false);
		field = 'item_select_service';
		prop_field = 'obj_prop_service';
	} else {
		$(".item_select input[type='checkbox']").attr('disabled', false);
	}

	if (action == '')
		return false;

	var ACKNOWLEDGED = 1;
	var NOTIFICATIONS_ENABLED = 2;
	var CHECKS_ENABLED = 4;
	var SCHEDULED_DT = 8;

	$('.' + prop_field).each(function() {
		var that = $(this);
		var test = false;
		switch (action) {
			case 'ACKNOWLEDGE_HOST_PROBLEM':
			case 'ACKNOWLEDGE_SVC_PROBLEM':
				test = that.text() & ACKNOWLEDGED || !(that.text() & 16);
				break;
			case 'REMOVE_HOST_ACKNOWLEDGEMENT':
			case 'REMOVE_SVC_ACKNOWLEDGEMENT':
				test = !(that.text() & ACKNOWLEDGED);
				break;
			case 'DISABLE_HOST_NOTIFICATIONS':
			case 'DISABLE_SVC_NOTIFICATIONS':
				test = that.text() & NOTIFICATIONS_ENABLED;
				break;
			case 'ENABLE_HOST_NOTIFICATIONS':
			case 'ENABLE_SVC_NOTIFICATIONS':
				test = !(that.text() & NOTIFICATIONS_ENABLED);
				break;
			case 'ENABLE_HOST_CHECK':
			case 'ENABLE_SVC_CHECK':
				test = !(that.text() & CHECKS_ENABLED);
				break;
			case 'DISABLE_HOST_CHECK':
			case 'DISABLE_SVC_CHECK':
				test = that.text() & CHECKS_ENABLED;
				break;
			case 'DEL_HOST_DOWNTIME':
			case 'DEL_SVC_DOWNTIME':
				test = !(that.text() & SCHEDULED_DT);
				break;
		}
		if (test) {
			that.closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true).attr('checked', false);
		}
	});
}

function create_slider(the_id)
{
	$("#" + the_id + "_slider").slider({
		value: current_interval,
		min: 0,
		max: 500,
		step: 10,
		slide: function(event, ui) {
			$("#" + the_id + "_value").val(ui.value);
			current_interval = ui.value;
			control_save_refreshInterval();
			ninja_refresh(ui.value);
		}
	});
	// set slider position according to current_interval
	$("#" + the_id + "_slider").slider("value", current_interval);
	$('input[name=' + the_id + '_value]').val(current_interval);

}

function control_save_refreshInterval() {
	if (_save_page_interval) {
		clearTimeout(_save_page_interval);
	}
	_save_page_interval = setTimeout("save_refreshInterval()", 5000);
}

function save_refreshInterval()
{
	var url = _site_domain + _index_page + "/ajax/save_page_setting/";
	var data = {page: '*', setting: current_interval, type: _refresh_key};
	$.post(url, data);
	$.jGrowl(sprintf(_page_refresh_msg, current_interval), { header: _success_header });
}

function ninja_refresh(val)
{
	if (_interval) {
		clearInterval(_interval);
	}
	var refresh_val = (val == null) ? _refresh : val;
	current_interval = refresh_val;
	if (val>0) {
		_interval = setInterval( "refresh()", refresh_val*1000 );
	}
}

function jgrowl_message(message_str, header_str)
{
	if (message_str!='') {
		$.jGrowl(message_str, { header: header_str });
	}
}


// ===========================================================
// code for remembering scroll position between page reloads
// adapted from http://www.huntingground.freeserve.co.uk/main/mainfram.htm?../scripts/cookies/scrollpos.htm
// ===========================================================

cookieName = "page_scroll";
expdays = 5;

function setCookie(name, value, expires, path, domain, secure) {
	if (!expires) {
		expires = new Date();
	}
	document.cookie = name + "=" + escape(value) +
	((expires == null) ? "" : "; expires=" + expires.toGMTString()) +
	((path == null) ? "" : "; path=" + path) +
	((domain == null) ? "" : "; domain=" + domain) +
	((secure == null) ? "" : "; secure");
}

function getCookie(name) {
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen) {
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg){
			return getCookieVal(j);
		}
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) {
			break;
		}
	}
	return null;
}

function getCookieVal(offset) {
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1) {
		endstr = document.cookie.length;
	}
	return unescape(document.cookie.substring(offset, endstr));
}

function deleteCookie(name,path,domain) {
	document.cookie = name + "=" + ((path == null) ? "" : "; path=" + path) + ((domain == null) ? "" : "; domain=" + domain) + "; expires=Thu, 01-Jan-00 00:00:01 GMT";
}

function saveScroll() {
	var expdate = new Date();
	expdate.setTime (expdate.getTime() + (expdays*24*60*60*1000)); // expiry date

	if (!_save_scroll) {
		// reset scroll memory to top
		setCookie(cookieName,'0_0',expdate);
		return;
	}

	var x = (document.pageXOffset?document.pageXOffset:document.body.scrollLeft);
	var y = (document.pageYOffset?document.pageYOffset:document.body.scrollTop);
	Data = x + "_" + y;
	setCookie(cookieName,Data,expdate);
}

function loadScroll() { // added function
	inf = getCookie(cookieName);
	if(!inf) {
		return;
	}
	var ar = inf.split("_");
	if (ar.length == 2) {
		window.scrollTo(parseInt(ar[0]), parseInt(ar[1]));
	}
}

function trigger_cb_on_nth_call(cb, n) {
	return function() {
		if (--n <= 0)
			cb();
	};
}

