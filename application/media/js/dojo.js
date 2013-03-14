(function () {

	var menu = null,

		mc = null,		// The current menu list
		bc = null,		// The current supermenu-button
		tmk = null,		// The temporary menu list key

		content = $('#content')[0],
		menu = $('#navigation')[0];

	var showMenu = function (e) {

		e = e || window.event;

		var target = e.currentTarget,
			key = target.title.toLowerCase().replace(' ','-');

		if (menu.style.display === 'block' && e.type === 'click') {

			menu.style.display = 'none';

		} else {

			menu.style.left = (target.offsetLeft - 2) + 'px';

			menu.style.display = 'block';

			if(mc) {
				mc.style.display = 'none'; // @todo fix
			}
			if(bc) {
				bc.style.boxShadow = 'none';
			}
			mc = $('#' + key + '-menu')[0];
			bc = $('#' + key + '-button')[0];

			if(mc) {
				mc.style.display = 'block'; // @todo fix
			}
			if(bc){
				bc.style.boxShadow = 'inset 0 0 8px #ccc';
			}
		}
	};

	var hideMenu = function (e) {

		menu.style.display = 'none';
		if(mc) {
			mc.style.display = 'none'; // @todo fix
		}
		if (bc) {
			bc.style.boxShadow = 'none';
		}
	};

	$('.supermenu-button').mouseover(showMenu);
	$('.supermenu-button').click(showMenu);

	$('.content').mouseover(hideMenu);
	$('.headercontent').mouseover(hideMenu);

	$(document).ready(function (e) {

		mc = $('.current-sup-menu');
		mc.removeClass('.current-sup-menu');

		// Set onload styles

		menu.style.display = 'none';

		mc = mc[0];

		if (!mc && !bc) {
			mc = $('#monitoring-menu')[0];
			bc = mc.firstChild;
		} else {
			bc = $('#' + mc.id.replace('-menu', '') + '-button');
			bc = bc[0];
		}

	});

	/* QUICKLINK EXTENSION */

	var uh_prob_title = "Unhandled Problems",
		uh_prob_state_element = null;
	function query_for_states () {
	
		var basepath = _site_domain + _index_page,
			link = $('.image-link[title="' + uh_prob_title + '"]'),
			query = link.attr('href');

		query = query.split('q=')[1];

		$.ajax({
			url : basepath + "/listview/fetch_ajax",
			dataType : 'json',
			data : {
				"query" : query,
				"limit" : 100,
				"columns": ['description']
			},
			success : function(data) {

				if (data && data.totals.service_all[1] > 0) {
					
					uh_prob_title = data.totals.service_all + ' unacknowledged services in Critical/Warning state!';
					link.attr('title', uh_prob_title);

					if (uh_prob_state_element)
						uh_prob_state_element.remove();
					uh_prob_state_element = $("<span style='margin: 0; position: absolute; color: #000; text-shadow: 0 0 2px #fff; font-weight: bold; font-size: 10px; padding: 1px 1px 0 0; right: 0px; bottom: 0px;' />").html(data.totals.service_all[1]);
					link.parent().append(uh_prob_state_element);
					
					if (data.totals.service_state_critical[1] > 0) {
						link.find(':first-child').removeClass().addClass('icon-16 x16-shield-critical');
					} else if (data.totals.service_state_warning[1] > 0) {
						link.find(':first-child').removeClass().addClass('icon-16 x16-shield-warning');
					}
				}
			}
		});
	}

	setInterval(query_for_states, 10000);
	query_for_states();

	var global_quicklinks = [];

	function quicklinks_save_all () {
		$.ajax(_site_domain + _index_page + '/ajax/save_page_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'setting': JSON.stringify(global_quicklinks)
			},
			type: 'POST',
			complete: function (xhr) {
				$('#dojo-add-quicklink-menu').fadeOut(300);
				$('#dojo-add-quicklink-href').attr('value','');
				$('#dojo-add-quicklink-title').attr('value','');
				$('#dojo-add-quicklink-icon').attr('value','');
			}
		});
	};

	function quicklinks_add(href, title, icon, internal) {

	};

	$('#dojo-add-quicklink').hover(function () {
		this.style.opacity = '1.0';
	}, function () {
		this.style.opacity = '0.5';
	});

	$('#dojo-add-quicklink').click(function () {
		
		$('#dojo-add-quicklink-menu').css({
			'display': 'block',
			'width': '50%',
			'left': '25%',
			'position': 'fixed',
			'top': '15%',
			'background': '#f5f5f5',
			'border': '1px solid #aaa',
			'box-shadow': '1px 1px 5px rgba(0,0,0,0.5)',
			'border-radius': '2px',
			'color': '#222',
			'padding': '8px'
		});
		
		$('#dojo-quicklink-remove').html('');

		for (var i = 0; i < global_quicklinks.length; i += 1) {

			var l = global_quicklinks[i],
					vid = l.title + ':'+ l.href;
			

			$('#dojo-quicklink-remove').append($(
				'<li><input type="checkbox" title="'+l.title+'" value="' + vid +'" id="' + vid + '" /><span class="icon-16 x16-'+l.icon+'"></span><label for="' + vid + '">' + l.title + '</label></li>'
			));

		}

	});

	$('#dojo-add-quicklink-menu option').hover(function () {
		$('#dojo-add-quicklink-preview').attr('class', 'icon-16 x16-' + this.value);
	}, function () {
		return;
	});

	$('#dojo-add-quicklink-menu option').click(function () {
		$('#dojo-add-quicklink-preview').attr('class', 'icon-16 x16-' + this.value);
	});

	$('#dojo-add-quicklink-close').click(function () {
		$('#dojo-add-quicklink-menu').fadeOut(300);
		$('#dojo-add-quicklink-href').attr('value','');
		$('#dojo-add-quicklink-title').attr('value','');
		$('#dojo-add-quicklink-icon').attr('value','');
	});	

	$('#dojo-add-quicklink-submit').click(function () {
		
		var href = $('#dojo-add-quicklink-href').attr('value'),
				title = $('#dojo-add-quicklink-title').attr('value'),
				icon = $('#dojo-add-quicklink-icon').attr('value'),
				target = $('#dojo-add-quicklink-target').attr('value'),
				changed = false;

		if (href && title && icon) { 

			var i = global_quicklinks.length,
					error = '';
			
			for (i; i--;) {
				
				if (global_quicklinks[i].href == href) {
					error += 'This href is already used in a quicklink. <br />';
				}

				if (global_quicklinks[i].title == title) {
					error += 'This title is already in use, titles must be unique. <br />';
				}

			}

			if (error.length == 0) {
				global_quicklinks.push({'href': href,'title': title,'icon': icon,'target': target})		
				$('#dojo-quicklink-external').append($('<li><a target="' + target + '" class="image-link" href="' + href + '"><span title="' + title + '" class="icon-16 x16-' + icon + '"></span></a></li>'));
				$('#dojo-add-quicklink-menu').fadeOut(500);
				changed = true;
			} else {
				$.jGrowl(error);
			}
		}
		
		var removal = $('#dojo-quicklink-remove input[type="checkbox"]').each(function () {
			var i = global_quicklinks.length,
					vid = '';
			if (this.checked) {
				for (i; i--;) {
					vid = global_quicklinks[i].title + ':' + global_quicklinks[i].href;
					if (this.value == vid) {
						$('#dojo-quicklink-external li a span[title="'+this.title+'"]').parent().parent().remove()
						global_quicklinks.splice(i, 1);
						changed = true;
					}
				}
			}

		});

		if (changed) 
			quicklinks_save_all();
		
	})

	$.ajax(_site_domain + _index_page + '/ajax/get_setting', {
			data: {
				'type': 'dojo-quicklinks',
					'page': 'tac'
			},
			type: 'POST',
			success: function (obj) {

				var links = [];

				if (obj['dojo-quicklinks']) {
					links = obj['dojo-quicklinks'];
					for (var i = 0; i < links.length; i += 1) {
						$('#dojo-quicklink-external').append($('<li><a target="' + links[i].target + '" class="image-link" href="' + links[i].href + '"><span title="'+links[i].title+'" class="icon-16 x16-'+links[i].icon+'"></span></a></li>'));
					}
				}

				global_quicklinks = links;

			}
		});

}());

