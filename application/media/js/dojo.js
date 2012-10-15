

/* JavaScripted Session-persistent Menu
*
*	@requires		mlib.bind
*	@requires		mlib.get
* @requires		mlib.store
*/

(function () {

	var config = {
		default_menu_cat: 'monitoring', // Always all-lowercase
		default_menu_button: 0, // Index of button in default category
		limit_for_collapsed_double_col: 20		// Includes list separators
	};	

	var menu = null,

		mc = null,		// The current menu list
		bc = null,		// The current supermenu-button
		bsub = null,	// The current menu sub-button
		tmk = null,		// The temporary menu list key

		content = $('#content')[0],
		menu = $('#navigation')[0];

	function toggleCollapse (e) {

		e = e || window.event;

		var menu = document.getElementById('navigation');

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

	function displayMenuStyle (e) {
		
		e = e || window.event;

		var oldicon = bsub.children[0].children[0],
			icon = null,
			span = null,
			key = null;

		oldicon.className = oldicon.className.replace(/menu-dark/g, 'menu');

		if (e.target.tagName.toLowerCase() === 'span') { // Pressed text/icon
			icon = e.target.parentNode.children[0];
			span = e.target.parentNode.children[1];
			key = e.target.parentNode.parentNode.id;
		} else { // Pressed list item
			icon = e.target.children[0];
			span = e.target.children[1];
			key = e.target.parentNode.id;
		}

		u.store('menu', tmk);

		icon.className = icon.className.replace(/menu/g, 'menu-dark');

		bsub.className = 'inactive';
		u.store('button', key);

		bsub = e.target.parentNode;
		bsub.className = 'active';

	}

	$('.supermenu-button').click(function (e) {
		
		e = e || window.event;

		var target = e.currentTarget,
			key = target.title.toLowerCase();

		tmk = key;

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
	$('li, .nav-seg').click(displayMenuStyle);
	$('.slider').click(toggleCollapse);

	$(document).ready(function (e) {

		if (!u.stored('menu')) u.store('menu', 'monitoring');
		if (!u.stored('button')) u.store('button', $('#' + u.stored('menu').data + '-menu')[0].children[0].id);
		if (u.stored('menu').data == '') u.store('menu', 'monitoring');

		var config = {
			limit_for_collapsed_double_col: 8		// Includes list separators
		};

		mc = $('#' + u.stored('menu').data + '-menu')[0];
		bc = $('#' + u.stored('menu').data + '-button')[0];
		bsub = $('#' + u.stored('button').data)[0];
		tmk = mc.title;

		// Set onload styles

		var tmp = bsub.children[0].children[0];
		tmp.className = tmp.className.replace('menu', 'menu-dark');
		tmp.parentNode.className = 'active';
		mc.style.display = 'block';
		bc.style.boxShadow = 'inset 0 0 8px #ccc';

		if (u.stored('menu-fold') && u.stored('menu-fold').data === 'folded') {
			menu.className = (mc.children.length > config.limit_for_collapsed_double_col) ?
				'navigation-collapsed-double' :'navigation-collapsed';
		}

		fixContentOffset(e);
		displayMenuStyle({target: bsub.children[0]});

	});

	$(window).load(fixContentOffset);

}());

