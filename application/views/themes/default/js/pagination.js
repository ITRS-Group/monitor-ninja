$(document).ready(function() {
	var focused_field = false;
	$('.show_pagination').each(function() {
		$(this).bind('click', function() {
			set_items_per_page();
		});
	});

	$('#pagination_id_1').bind('focus', function() {
		focused_field = $(this).attr('id');
	});
	$('#pagination_id_2').bind('focus', function() {
		focused_field = $(this).attr('id');
	});

	$('#pagination_id_1').bind('change', function() {
		propagate_val($(this).val());
	});
	$('#pagination_id_2').bind('change', function() {
		focused_field = $(this).attr('id');
		propagate_val($(this).val());
	});

	// check if we have a customized items_per_page
	var url = location.search;
	var cust_val = $.query.get('custom_pagination_field');
	if (cust_val) {
		// do we have an option with this value?
		value_exists(cust_val); // added if not
	}

	$('.pagination_form').bind('submit', function() {
		preserve_get_params($('#' + focused_field).attr('value'));
	});

});

function set_items_per_page()
{
	// submit form
	$('.pagination_form').trigger('submit');
}

function preserve_get_params(custom_val, sel_id)
{
	if (custom_val != false && typeof custom_val != 'undefined' && custom_val != 'sel') {
		propagate_val(custom_val);
	} else {
		if (custom_val == 'sel') {
			propagate_val($('#' + sel_id).val());
		}
	}

	// make sure we don't loose GET variables from current query string
	if ($.query.keys) {
		for (var key in $.query.keys) {
			if (key != 'items_per_page' && key!= 'custom_pagination_field' && key!='result') {
				$('.pagination_form').append('<input type="hidden" name="' + key + '" value="' + $.query.keys[key] + '">');
			}
		}
	}
}

function propagate_val(val)
{
	val = parseInt(val, '10');
	if (isNaN(val)) {
		var cust_val = $.query.get('custom_pagination_field');
		cust_val = cust_val ? cust_val : 100;
		propagate_val(cust_val);
		return false;
	}
	$('.custom_pagination_field').each(function() {
		$(this).val(val);
		value_exists(val);
	});
}

function value_exists(val)
{
	$('.items_per_page').each(function() {
		if (! $(this).containsOption(val) ) {
			pagination_add_option(val);
		}
		$(this).selectOptions(val);
	});
}

function pagination_add_option(val)
{
	val = parseInt(val, '10');
	if (isNaN(val)) {
		return false;
	}
	if (val < 0) {
		val = val*-1;
	}
	var entries_str = $('.pagination_entries_str').html();
	$('.items_per_page').each(function() {
		$(this).addOption(val, val + ' ' + entries_str);
		//$(this).sortOptions(); // this is not working as of now since sorting is string based :(
	});
}