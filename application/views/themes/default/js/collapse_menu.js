function collapse_menu(action) {
	if (action == '') {
		kakor = document.cookie;
		kakor_delar = kakor.split('; ');
		for (j = 0; j < kakor_delar.length; j++) {
			tva_delar = kakor_delar[j].split('=');
			if (tva_delar[0] == 'ninja_menu') {
				action = tva_delar[1];
			}
		}
	}
	if (action == 'hide') {
		document.getElementById('menu').style.width = '35px';
		document.getElementById('close-menu').style.display = 'none';
		document.getElementById('show-menu').style.display = 'block';
		var menu = document.getElementById('menu');
		for (var i = 1; i < menu.getElementsByTagName('a').length; i = i+2) {
			menu.getElementsByTagName('a')[i].setAttribute('style','display: none');
		}
		for (var j = 0; j < menu.getElementsByTagName('cite').length; j++) {
			menu.getElementsByTagName('cite')[j].setAttribute('style','display: none');
		}
		document.getElementById('content').style.marginLeft = '35px';
		var date = new Date();
		date.setTime(date.getTime()+(365*24*60*60*1000));
		document.cookie = 'ninja_menu=hide;path=/;expires='+date.toGMTString();
	}
	if (action == 'show') {
		document.getElementById('menu').style.width = '166px';
		document.getElementById('close-menu').style.display = 'block';
		document.getElementById('show-menu').style.display = 'none';
		var menu = document.getElementById('menu');
		for (var i = 1; i < menu.getElementsByTagName('a').length; i = i+2) {
			menu.getElementsByTagName('a')[i].setAttribute('style','display: inline');
		}
		for (var j = 0; j < menu.getElementsByTagName('cite').length; j++) {
			menu.getElementsByTagName('cite')[j].setAttribute('style','display: inline');
		}
		document.getElementById('content').style.marginLeft = '166px';
		var date = new Date();
		date.setTime(date.getTime()+(365*24*60*60*1000));
		document.cookie = 'ninja_menu=show;path=/;expires='+date.toGMTString();
	}

}

function settings(action) {
	if (action == 'hide') {
		document.getElementById('page_settings').style.display = 'none';
	}
	else {
		if (document.getElementById('page_settings').style.display == 'block')
			document.getElementById('page_settings').style.display = 'none';
		else
			document.getElementById('page_settings').style.display = 'block';
	}
}

window.onload = function() {
	collapse_menu('');
}