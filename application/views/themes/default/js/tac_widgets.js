$(function(){
	$.fn.EasyWidgets({
		behaviour : {
			useCookies : true
		},
		i18n : {
			editText : '<img src="/ninja/application/views/themes/default/images/icons/box-config.png" alt="Settings" />',
			closeText : '<img src="/ninja/application/views/themes/default/images/icons/box-close.png" alt="Close widget" style="float: right; margin-top: -14px" />',
			collapseText : '<img src="/ninja/application/views/themes/default/images/icons/box-maximize.png" alt="Collapse" style="float: right; margin-top: -14px; margin-right: 16px" />',
			cancelEditText : '<img src="/ninja/application/views/themes/default/images/icons/box-config.png" alt="Cancel" />',
			extendText : '<img src="/ninja/application/views/themes/default/images/icons/box-mimimize.png" alt="Extend" style="float: right; margin-top: -14px; margin-right: 16px" />'
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