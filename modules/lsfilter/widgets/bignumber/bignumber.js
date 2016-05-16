$(function() {
var react_to_checkbox = function(ev) {
	var checkbox = $(this);
	var fieldset = checkbox.parents('.can_be_toggled');
	var element_which_value_is_actually_saved = fieldset.find('.threshold_onoff');
	if(element_which_value_is_actually_saved.val() === '1') {
		checkbox.removeAttr('checked');
		fieldset.find('div input, div select').attr('disabled', 'disabled');
		fieldset.addClass('disabled');
		element_which_value_is_actually_saved.val(0);
	} else {
		checkbox.attr('checked', 'checked');
		fieldset.find('div input, div select').removeAttr('disabled');
		fieldset.removeClass('disabled');
		element_which_value_is_actually_saved.val(1);
	}
};
// run onload
$('.can_be_toggled .toggle_me').each(function() {
	react_to_checkbox.apply(this);
});
// run onchange
$('.dashboard').on('click', '.can_be_toggled .toggle_me', react_to_checkbox);

});
