$(function(){
	$.fn.EasyWidgets({
		behaviour : {
			useCookies : true
		},
		i18n : {
			editText : '<img src="/ninja/application/views/themes/default/images/icons/box-config.png" alt="Settings" style="margin: -12px 30px 0px auto; display: block" />',
			closeText : '<img src="/ninja/application/views/themes/default/images/icons/box-close.png" alt="Close widget"   style="margin: -12px 0px 0px auto; display: block" />',
			collapseText : '<img src="/ninja/application/views/themes/default/images/icons/box-maximize.png" alt="Collapse"  style="margin: -12px 15px 0px auto; display: block" />',
			cancelEditText : '<img src="/ninja/application/views/themes/default/images/icons/box-config.png" alt="Cancel" style="margin: -12px 30px 0px auto; display: block" />',
			extendText : '<img src="/ninja/application/views/themes/default/images/icons/box-mimimize.png" alt="Extend" style="margin: -12px 15px 0px auto; display: block" />'
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