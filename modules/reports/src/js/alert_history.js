$(function() {
	var show_all = $('#show_all');
	if(show_all.length === 0) {
		return;
	}

	var show_hide_obj_sel = function() {
		if (this.checked)
			$('.obj_selector').hide();
		else
			$('.obj_selector').show();
	};
	show_all.on('change', show_hide_obj_sel);
	show_hide_obj_sel.call(show_all.get(0));
});
