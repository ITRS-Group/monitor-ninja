$(document).ready(function() {
	$.fn.EasyWidgets({
		behaviour : {
			useCookies : false
		},
		i18n : {
			editText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-config.png" alt="Settings" style="float: right; margin-top: -14px; margin-right: 32px" />',
			closeText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-close.png" alt="Close widget" style="float: right; margin-top: -14px" />',
			collapseText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-maximize.png" alt="Collapse" style="float: right; margin-top: -14px; margin-right: 16px" />',
			cancelEditText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-config.png" alt="Cancel" style="float: right; margin-top: -14px; margin-right: 32px" />',
			extendText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-mimimize.png" alt="Extend" style="float: right; margin-top: -14px; margin-right: 16px" />'
		},
		effects : {
			effectDuration : 150,
			widgetShow : 'slide',
			widgetHide : 'slide',
			widgetClose : 'slide',
			widgetExtend : 'slide',
			widgetCollapse : 'slide',
			widgetOpenEdit : 'slide',
			widgetCloseEdit : 'slide',
			widgetCancelEdit : 'slide'
		},
		callbacks : {
			onChangePositions : function(str){
				save_widget_order(str);
			},
			onRefreshPositions: function() {
				fetch_widget_order();
			},
			onClose: function(link, widget) {
				var widget_name = '#' + widget[0].id;
				$('#li_' + widget[0].id).removeClass().addClass('unselected');
				save_widget_state('hide', widget_name);
			},
			onHide: function(widget) {
				var widget_name = widget.selector;
				save_widget_state('hide', widget_name);
			},
			onShow: function(widget) {
				var widget_name = widget.selector;
				save_widget_state('show', widget_name);
			}
		}
	});
	$("#widget-placeholder").bind('click', function() {
		$("#page_settings").hide();
	});
});

function save_widget_state(what, widget_name)
{
	var url = _site_domain + _index_page + "/ajax/save_widget_state/";
	var page_name = 'tac/index';
	var data = {page: escape(page_name), method: what, name: widget_name};
	$.post(url, data);
}

/*
*	Save widget order to database
*/
function save_widget_order(order_str)
{
	var url = _site_domain + _index_page + "/ajax/save_widgets_order/";
	var page_name = 'tac/index';
	var data = {page: escape(page_name), widget_str: order_str};
	$.post(url, data);
}

/*
*	Fetch saved widget order from database
*/
function fetch_widget_order()
{
	var page_name = 'tac/index';
	$.ajax({
		url: _site_domain + _index_page + "/ajax/fetch_widgets_order/?page=" + escape(page_name),
		dataType:'json',
		success: function(data) {
			if (data.widget_order) {
				$.fn.EasyWidgets({callbacks:{onRefreshPositions:function(){return data.widget_order;}}});
			}
		},
		error: function(obj, msg){alert(msg)}
	});

}

function control_widgets(id,item) {
	if (item.className == 'selected') {
		$.fn.HideEasyWidget(id);
		item.className = 'unselected';
	}
	else {
		$.fn.ShowEasyWidget(id);
		item.className = 'selected';
	}
}

function restore_widgets()
{
	$('li[id^=li_widget]').each(function() {
		var item_id = $(this).attr('id');
		var widget_id = item_id.replace('li_', '');
		$('#' + item_id).removeClass().addClass('selected');
		$('#' + widget_id).show();
	});
}
