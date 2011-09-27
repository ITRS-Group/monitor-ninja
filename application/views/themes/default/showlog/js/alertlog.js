$(document).ready(function() {
	$('#showlog').tablesorter({});

	$('#clearfilterbox').click(function() {
		$('#filterbox').val('').trigger('keyup').trigger('blur');
		control_page_refresh('0', old_refresh);
		return false;
	});

	$('#filterbox').keyup(function(){
		filter_table(this, 'showlog');})
		.focus(function(){
			if(this.value==_filter_label) {
				this.value='';
			}
	})
	.blur(function() {
		if (this.value == '') {
			this.value = _filter_label;
		}
	});

	$('.filterboxfield').focus(function() {
		if (!$('#ninja_refresh_control').attr('checked')) {
			// save previous refresh rate
			// to be able to restore it later
			old_refresh = current_interval;
			$('#ninja_refresh_lable').css('font-weight', 'bold');
			ninja_refresh(0);
			$("#ninja_refresh_control").attr('checked', true);
		}
	});

	$('.filterboxfield').blur(function() {
		if ($(this).val() != '') {
			// don't do anything if we have a current filter value
			return;
		}
		if ($('#ninja_refresh_control').attr('checked')) {
			// restore previous refresh rate
			ninja_refresh(old_refresh);
			$("#ninja_refresh_control").attr('checked', false);
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});

});

function control_page_refresh(state, old_refresh)
{
	switch (state) {
		case '0': // turn refresh on
			if ($('#ninja_refresh_control').attr('checked')) {
				// restore previous refresh rate
				ninja_refresh(old_refresh);
				$("#ninja_refresh_control").attr('checked', false);
				$('#ninja_refresh_lable').css('font-weight', '');
			}
			break;
		case '1': // turn refresh off
		if (!$('#ninja_refresh_control').attr('checked')) {
			// save previous refresh rate
			// to be able to restore it later
			old_refresh = current_interval;
			$('#ninja_refresh_lable').css('font-weight', 'bold');
			ninja_refresh(0);
			$("#ninja_refresh_control").attr('checked', true);
		}
	}
}

function filter_table (phrase, _id){
	var words = phrase.value.toLowerCase().split(" ");
	var table = document.getElementById(_id);
	var ele;

	for (var r = 1; r < table.rows.length; r++){
		ele = table.rows[r].innerHTML.replace(/<[^>]+>/g,"");
		var displayStyle = 'none';
		if (table.rows[r].className.indexOf('submit') == -1) {
			for (var i = 0; i < words.length; i++) {
				if (ele.toLowerCase().indexOf(words[i])>=0)
					displayStyle = '';
				else {
					displayStyle = 'none';
					break;
				}
			}
		}
		table.rows[r].style.display = displayStyle;
	}
};
