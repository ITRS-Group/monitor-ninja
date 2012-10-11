

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
		limit_for_collapsed_double_col: 8		// Includes list separators
	};	

	var menu = null,
		menuCurrent = null,
		btnCurrent = null,
		btnSub = null,
		tmpmenukey = null,
		content = u.get('#content'),
		menu = u.get('#navigation');

	function toggleCollapse (e) {

		e = e || window.event;

		var menu = document.getElementById('navigation');

		if (menu.className.indexOf('navigation-collapsed') >= 0) {
			u.store('menu-fold', 'unfolded');
			menu.className = 'navigation';
			content.style.marginLeft = menu.offsetWidth + 'px';
		} else {
			u.store('menu-fold', 'folded');
			menu.className = (menuCurrent.children.length > config.limit_for_collapsed_double_col) ?
				'navigation-collapsed-double' : 'navigation-collapsed';
			content.style.marginLeft = menu.offsetWidth + 'px'; 
		}

		fixContentOffset(e);

	};

	function fixContentOffset (e) {
		
		e = e || window.event;

		if (e.type === 'load' && menuCurrent == null) {

				if (!u.stored('menu')) u.store('menu', 'monitoring');
				if (!u.stored('button')) u.store('button', u.get('#' + u.stored('menu').data + '-menu').children[0].id);
				if (u.stored('menu').data == '') u.store('menu', 'monitoring');

				var config = {
					limit_for_collapsed_double_col: 8		// Includes list separators
				};

				menuCurrent = u.get('#' + u.stored('menu').data + '-menu');
				btnCurrent = u.get('#' + u.stored('menu').data + '-button');
				btnSub = u.get('#' + u.stored('button').data);
				tmpmenukey = menuCurrent.title;

				// Set onload styles

				var tmp = btnSub.children[0].children[0];
				tmp.src = tmp.src.replace('menu', 'menu-dark');
				btnSub.className = 'active';
				menuCurrent.style.display = 'block';
				btnCurrent.style.boxShadow = 'inset 0 0 8px #ccc';

				if (u.stored('menu-fold') && u.stored('menu-fold').data === 'folded') {
					menu.className = (menuCurrent.children.length > config.limit_for_collapsed_double_col) ?
						'navigation-collapsed-double' :'navigation-collapsed';
					fixContentOffset(e);
				}

		}

		var mmenu = u.get('#main-menu'),
			hheight = u.get('#header').offsetHeight,
			body = document.body,
			highest = (mmenu.offsetHeight < (body.offsetHeight - hheight)) ? 
				(body.offsetHeight - hheight) : mmenu.offsetHeight;

		content.style.marginLeft = menu.offsetWidth + 'px';
		menu.style.height = (document.body.offsetHeight - hheight) + 'px';
		u.get('#slider').style.height = (highest) + 'px';

	}

	function displayMenuStyle (e) {
		
		e = e || window.event;

		var key = e.target.parentNode.id,
			bimg = btnSub.children[0].children[0];

		u.store('menu', tmpmenukey);
		bimg.src = bimg.src.replace('menu-dark', 'menu');
		e.target.children[0].src = e.target.children[0].src.replace('menu', 'menu-dark');

		btnSub.className = '';
		u.store('button', key);
		btnSub = e.target.parentNode;
		btnSub.className = 'active';

	}

	u.bind('resize', fixContentOffset, true, window);
	u.bind('load', fixContentOffset, true, window);

	u.bind('.slider', 'click', toggleCollapse);
	u.bind('.slide-button', 'click', toggleCollapse);

	u.bind('li .nav-seg', 'click', displayMenuStyle);
	
	u.bind('.supermenu-button', 'click', function (e) {
		
		e = e || window.event;

		var key = e.target.title.toLowerCase();

		tmpmenukey = key;

		menuCurrent.style.display = 'none';
		btnCurrent.style.boxShadow = 'none';

		menuCurrent = u.get('#' + key + '-menu');
		btnCurrent = u.get('#' + key + '-button');

		menuCurrent.style.display = 'block';
		btnCurrent.style.boxShadow = 'inset 0 0 8px #ccc';

		if (menu.className.indexOf('navigation-collapsed') >= 0) {
			menu.className = (menuCurrent.children.length > config.limit_for_collapsed_double_col) ?
				'navigation-collapsed-double' : 'navigation-collapsed';
		}

		fixContentOffset(e);

	});

}());
