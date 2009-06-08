$(document).ready(function() {
	$.fn.EasyWidgets({
		behaviour : {
			useCookies : false
		},
		i18n : {
			editText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-config.png" alt="Settings" />',
			closeText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-close.png" alt="Close widget" style="float: right; margin-top: -14px" />',
			collapseText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-maximize.png" alt="Collapse" style="float: right; margin-top: -14px; margin-right: 16px" />',
			cancelEditText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-config.png" alt="Cancel" />',
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
	});
});

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

function widget_status(){
	cookie_parts = document.cookie.split('; ');
	for (j = 0; j < cookie_parts.length; j++) {
		content = cookie_parts[j].split('=');
		if (content[0] == 'ew-close') {
			widgets = content[1];
			closed_widgets = widgets.split('%2C');
			for(i =0; i < closed_widgets.length; i++) {
				if (closed_widgets[i] != '') {
					document.getElementById('li_'+closed_widgets[i]).className = 'unselected';
				}
			}
		}
	}
}


window.onload = function(){
 widget_status();
 collapse_menu('');
}