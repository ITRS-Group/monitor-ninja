var sURL = unescape(window.location.pathname + location.search);
var _interval = 0;
var _save_page_interval = 0;
var current_interval = 0;
var edit_visible = 0;

$(document).ready(function() {
	/**
	*	Show the checkbox to show/hide "page header" if
	*	we find the content-header div in the current page
	*/
	if ($('#content-header').text()!='') {
		$('#noheader_ctrl').show();
		$('#settings_icon').show();

		// Let checkbox state reflect visibility of the #content-header div
		if ($('#content-header').is(':visible')) {
			// force unchecked checkbox
			$('#noheader_chbx').attr('checked', false);
		} else {
			// mark current state by checking the checkbox
			$('#noheader_chbx').attr('checked', true);
		}
	}

	/**
	*	Bind some functionality to the checkbox state change event
	*	This involves setting the correct value for the noheader GET parameter
	*	and passing the new value to the refresh script so that the value
	*	will persist between refreshes.
	*/
	$('#noheader_chbx').bind('change', function() {
		var noheader = $.query.get('noheader');
		if ($(this).attr('checked')) {
			$('#content-header').hide();
			var new_url = $.query.set('noheader', 1);
		} else {
			$('#content-header').show();
			var new_url = $.query.set('noheader', 0);
		}
		sURL = new_url.toString();
	});

	// refresh helper code
	var old_refresh = 0;
	$("#ninja_refresh_control").bind('change', function() {
		if ($("#ninja_refresh_control").attr('checked')) {
			// save previous refresh rate
			// to be able to restore it later
			old_refresh = current_interval;
			$('#ninja_refresh_lable').css('font-weight', 'bold');
			ninja_refresh(0);
		} else {
			// restore previous refresh rate
			ninja_refresh(old_refresh);
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});
	if ($('#ninja_refresh_edit').text()!='') {
		create_slider('ninja_page_refresh');
	}
	$('#ninja_refresh_edit').bind('click', function() {
		if (!edit_visible) {
			$('#ninja_page_refresh_slider').show();
			edit_visible = 1;
		} else {
			$('#ninja_page_refresh_slider').hide();
			edit_visible = 0;
		}
	});
	// -- end refresh helper code

	// ==========================
	// check menu section status
	// ==========================
	// find all menu sections identified by
	// the text in cite tags
	$('cite.menusection').each(function() {
		var section = $(this).text();
		var section_state = window['_ninja_menusection_'+ section];
		if (section_state.length) {
			// hide the sections set to 'hide'
			if (section_state=='hide') {
				// using collapse_section() from
				// collapse_menu.js
				collapse_section(section);
			}
		}
	});

	// menu scroll/slider init
	$("#menu-slider").slider({
	orientation: 'vertical',
		animate: true,
	change: handleSliderChange,
	slide: handleSliderSlide,
	min: -100,
	max: 0,
	value: -2
	});

	// check if show or hide the scroll/slider
	scroll_control();
});

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

$(window).resize(function() {
	scroll_control()
});

/**
*	Control if slider should be shown.
*	This function should be called from everywhere
*	we change the menu but with a delay of at least 100msec
*/
function scroll_control()
{
	var xtra_height = 69; // top bars etc takes up some space
	$('#menu-slider').css('height', parseInt(document.documentElement.clientHeight-67)+'px');
	$('#menu-scroll').css('height', parseInt(document.documentElement.clientHeight)+'px');
	$('#menu-scroll').css('border-right', '1px solid #d0d0d0');
	if (parseInt($('#menu ul').height()+xtra_height) <= parseInt(document.documentElement.clientHeight)) {
		$('#menu-slider').hide();
		var maxScroll = $("#menu-scroll").attr("scrollHeight") - $("#menu-scroll").height();
		$("#menu-scroll").animate({scrollTop: -100 * (maxScroll / 100) }, 1000);
	}
	else
		$('#menu-slider').show();
}

function handleSliderChange(e, ui){
	var maxScroll = $("#menu-scroll").attr("scrollHeight") - $("#menu-scroll").height();
  $("#menu-scroll").animate({scrollTop: -ui.value * (maxScroll / 100) }, 1000);
}

function handleSliderSlide(e, ui){
	var maxScroll = $("#menu-scroll").attr("scrollHeight") - $("#menu-scroll").height();
	$("#menu-scroll").attr({scrollTop: -ui.value * (maxScroll / 100) });
}