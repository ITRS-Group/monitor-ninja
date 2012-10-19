
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
			key = target.title.toLowerCase();

		if (menu.style.display === 'block' && e.type === 'click') {

			menu.style.display = 'none';

		} else {

			menu.style.left = (target.offsetLeft - 10) + 'px';

			menu.style.display = 'block';

			mc.style.display = 'none';
			bc.style.boxShadow = 'none';

			mc = $('#' + key + '-menu')[0];
			bc = $('#' + key + '-button')[0];

			mc.style.display = 'block';
			bc.style.boxShadow = 'inset 0 0 8px #ccc';

		}
	};

	var hideMenu = function (e) {

		menu.style.display = 'none';
		mc.style.display = 'none';
		bc.style.boxShadow = 'none';
		
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

}());

