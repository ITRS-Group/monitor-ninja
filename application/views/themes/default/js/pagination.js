$(document).ready(function() {
	$('.show_pagination').each(function() {
		$(this).bind('click', function() {
			set_items_per_page();
		});
	});

	$('.custom_pagination_field').each(function() {
		$(this).bind('change', function() {
			propagate_val($(this).val());
		});
	});

	// check if we have a customized items_per_page
	var url = location.search;
	var cust_val = $.query.get('custom_pagination_field');
	if (cust_val) {
		// do we have an option with this value?
		value_exists(cust_val); // added if not
	}

});

function set_items_per_page()
{
	// submit form
	$('.pagination_form').trigger('submit');
}


function propagate_val(val)
{
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
	var entries_str = $('.pagination_entries_str').html();
	$('.items_per_page').each(function() {
		$(this).addOption(val, val + ' ' + entries_str);
		//$(this).sortOptions(); // this is not working as of now since sorting is string based :(
	});
}