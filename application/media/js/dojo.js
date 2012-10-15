

/* JavaScripted Session-persistent Menu
*
*	@requires		mlib.bind
*	@requires		mlib.get
* @requires		mlib.store
*/

function show_info() {

	if ($('#version_info').is(':visible')) {
		$('#version_info').hide();
	} else {
		if ($('#infobar').is(':visible')) {
			var top = 125;
			$('#version_info').css('top', (top + 3) + 'px');
		}
		$('#version_info').show();
	}
}

(function () {

	var config = {
		default_menu_cat: 'monitoring', // Always all-lowercase
		default_menu_button: 0, // Index of button in default category
		limit_for_collapsed_double_col: 20		// Includes list separators
	};	

	var menu = null,

		mc = null,		// The current menu list
		bc = null,		// The current supermenu-button
		tmk = null,		// The temporary menu list key

		content = $('#content')[0],
		menu = $('#navigation')[0];

	function toggleCollapse (e) {

		e = e || window.event;

		var menu = $('#navigation')[0];

		if (menu.className.indexOf('navigation-collapsed') >= 0) {

			u.store('menu-fold', 'unfolded');
			menu.className = 'navigation';
			content.style.marginLeft = menu.offsetWidth + 'px';

		} else {

			u.store('menu-fold', 'folded');
			menu.className = (mc.children.length > config.limit_for_collapsed_double_col) ?
				'navigation-collapsed-double' : 'navigation-collapsed';
			content.style.marginLeft = menu.offsetWidth + 'px'; 

		}

		fixContentOffset(e);

	};

	function fixContentOffset (e) {
		
		e = e || window.event;

		var mmenu = $('#main-menu')[0],
			hheight = $('#header')[0].offsetHeight,
			body = document.body,
			highest = (mmenu.offsetHeight < (body.offsetHeight - hheight)) ? 
				(body.offsetHeight - hheight) : mmenu.offsetHeight;

		content.style.width = (document.body.offsetWidth - menu.offsetWidth - 40) + 'px';

		if (menu.offsetWidth)
			content.style.marginLeft = menu.offsetWidth + 'px';
		if (document.body.offsetHeight - hheight)
			menu.style.height = (document.body.offsetHeight - hheight) + 'px';
		if (highest)
			$('#slider')[0].style.height = (highest) + 'px';

	}

	$('.supermenu-button').click(function (e) {
		
		e = e || window.event;

		var target = e.currentTarget,
			key = target.title.toLowerCase();

		mc.style.display = 'none';
		bc.style.boxShadow = 'none';

		mc = $('#' + key + '-menu')[0];
		bc = $('#' + key + '-button')[0];

		mc.style.display = 'block';
		bc.style.boxShadow = 'inset 0 0 8px #ccc';

		if (menu.className.indexOf('navigation-collapsed') >= 0) {
			menu.className = (mc.children.length > config.limit_for_collapsed_double_col) ?
				'navigation-collapsed-double' : 'navigation-collapsed';
		}

		fixContentOffset(e);

	});

	$(window).resize(fixContentOffset);

	$('.slider').click(toggleCollapse);

	$(document).ready(function (e) {

		mc = $('.current-sup-menu');
		mc.removeClass('.current-sup-menu');

		// Set onload styles

		mc = mc[0];
		bc = $('#' + mc.id.replace('-menu', '') + '-button');
		bc = bc[0];

		mc.style.display = 'block';
		bc.style.boxShadow = 'inset 0 0 8px #ccc';

		if (u.stored('menu-fold') && u.stored('menu-fold').data === 'folded') {
			menu.className = (mc.children.length > config.limit_for_collapsed_double_col) ?
				'navigation-collapsed-double' :'navigation-collapsed';
		}

		fixContentOffset(e);

	});

	$(window).load(fixContentOffset);

}());

