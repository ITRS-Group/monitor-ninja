$(document).ready(function(){
	$('#config_table').tablesorter({});

	$('#filterbox')
		.keyup(function() {
			filter_table(this, 'config_table');
		})
		.focus(function() {
			if(this.value==_filter_label) {
				this.value='';
			}
		})
		.blur(function() {
			if (this.value == '') {
				this.value = _filter_label;
			}
		});

	$('#search_all_pages')
		.parents('form')
			.submit(function() {
				var filterbox = $('#filterbox');
				if(filterbox.val() == _filter_label) {
					filterbox.val('');
				}
			});
});

function filter_table (phrase, _id){
	var words = phrase.value.toLowerCase().split(" ");
	var table = document.getElementById(_id);
	var ele;

	for (var r = 1; r < table.rows.length; r++){
		ele = table.rows[r].innerHTML.replace(/<[^>]+>/g,"");
		var displayStyle = 'none';
		for (var i = 0; i < words.length; i++) {
			if (ele.toLowerCase().indexOf(words[i])>=0)
				displayStyle = '';
			else {
				displayStyle = 'none';
				break;
			}
		}
		table.rows[r].style.display = displayStyle;
	}
};
