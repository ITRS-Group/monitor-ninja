
(function () {

	function ucwords (str) {
	  return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
	    return $1.toUpperCase();
	  });
	}

	function arrayRemoveValue(arr){
	    var what, a= arguments, L= a.length, ax;
	    while(L> 1 && arr.length){
	        what= a[--L];
	        while((ax= arr.indexOf(what))!= -1){
	            arr.splice(ax, 1);
	        }
	    }
	    return arr;
	}

	$(window).bind('load', function onmainload () {

		/* Static */

		var parser = new DOMParser(),
			basepath = _site_domain + _index_page;

		/* Private */

		var pages = [],
			index = -1,
			timeout = null,
			paused = true,
			optsstick = false,

			addedscripts = [],
			addedcss = [],
			
			optstimeout = null,

			max_interval = 30000,				// Maximum time between swaps
			min_interval = 5000,				// Minimum time between swaps
			interval_increment = 1000,			// How much one click in the GUI increments/decrements from interval
			interval = 10000;					// The current time between swaps

		$('#page-rotation-fields').find('input').each(function () {
			if (this.checked) {
				pages.push(this.value);
			}
		});

		$('#page-rotation-fields').change(function(e) {

			if (e.target.checked) {
				pages.push(e.target.value);
			} else {
				pages = arrayRemoveValue(pages, e.target.value);
			}

			if (pages.length >= 1) {
				$('#page-rotation-play').css('opacity','1.0');
			}
		});

		function page_rotation_note (message, ref) {
			if (typeof(ref) == 'function') {
				$('#page-rotation-note').html(message).fadeIn(500).delay(2000).fadeOut(1000, ref);
			} else {
				$('#page-rotation-note').html(message).fadeIn(500).delay(2000).fadeOut(1000);
			}
		}

		function valueToName (value) {

			var name = '';

			if (value[0] != '/') {
				value = '/' + value;
			}

			name = (value.substr(1).split('?')[0]).split('/').join(' - ');
			return ucwords(name.split('_').join(' '));

		}

		function page_rotation_endswap (xhr, reponse) {

			if (xhr.status != 200) {
				page_rotation_note('The requested frame did not return status 200, jumping to next in 3 seconds', page_rotation_startswap);
			}

			var tmp = document.implementation.createHTMLDocument("tmp"),
					doc = null;

			// Create a virtual document from the response data

			tmp.documentElement.innerHTML = xhr.responseText;
			doc = $(tmp);

			// ************************
			// It's time for some magic
			// ************************

			// Remove all scripts and links that were hoisted from iframes on the last frame

			for (var i = 0; i < addedscripts.length; i += 1)
				addedscripts[i].remove();

			for (var i = 0; i < addedcss.length; i += 1)
				addedcss[i].remove();

			addedscripts = [];
			addedcss = [];

			// Clean the rotation frame before we add anything new, the frame should have faded out
			// so there shouldn't be any stutter

			$('#rotation-frame').html('');

			// If the requested frame is a widget we handle it quite differently, and quite poorly
			// ..... but it works, 

			if (pages[index].indexOf('widget') > 0) {

				// Hoist the content of a widget from the tac based on id and insert it into the rotation frame

				var name = pages[index].split(' ')[0];
						widg = doc.find("[id*=" + name + "]");
				if (widg) {
					$('#rotation-frame').append(widg.children());
				} else {
					$('#rotation-frame').html('Could not resolve widget ' + name);
				}
			} else {

				// We append the iframe so that jquery can resolve the context, can't do an iframe contents 
				//	check on a virtual documents.
				// It it is not an iframe this is enough for the view to display relatively well.

				$('#rotation-frame').append(doc.find('.content').children());

				var frame = $('#rotation-frame').find('iframe');

				if (frame.length > 0) {

					// Let the funtime begin.... just after we wait for the iframe to load... promise

					frame.load(function () {

						var contents = frame.contents().find('body').children(),		// Hoise the body from the iframe in the virtual document
								scripts = frame.contents().find('script'),							// Hoist all script tags  -''-
								css = frame.contents().find('link[rel="stylesheet"]'),	// Hoist all css tags  -''-
								cscripts = $('head').find('script'),										// Get all our current scripts (to use as reference points)
								ccss = $('head').find('link[rel="stylesheet"]');				// Get all our current css (to use as reference points)

						scripts.each(function () {

							// Insert all hoisted scripts if they:
							//		1. Have a source, we don't care for inline, not important that it functions, only that it displays
							//		2. Are not already included by default in the rotational view
							// Note that they are prepended so that in case of collision we use the ones designated by rotational view

							var i = cscripts.length,
									insert = true;
							if (this.src) {

								for (i; i--;) {
									if (this.src == cscripts[0].src) {
										insert = false;
									}
								}

								if (insert) {
									$('head').prepend($(this));
									addedscripts.push($(this));
								}
							}

						});

						css.each(function () {

							// Insert all hoisted links if they:
							//		1. Have a href
							//		2. Are not already included by default in the rotational view
							// Note that they are prepended so that in case of collision we use the ones designated by rotational view

							var i = ccss.length,
									insert = true;
							if (this.href) {
								
								for (i; i--;) {
									if (this.href == ccss[0].href) {
										insert = false;
									}
								}

								if (insert) {
									$('head').prepend($(this));
									addedcss.push($(this));
								}
							}
						});

						$('#rotation-frame').html('');		// We have everything we want from the iframe, lets scrap it

						$('#rotation-frame').append(contents);	// Append the hoisted content into the rotation frame
						$('#rotation-frame').css({'margin': '1% auto'});	// Fiddle with the css to display it better

						$('#rotation-frame').animate({'opacity': 'show'}, 1000);	// Show the user what we got

						clearTimeout(timeout);
						timeout = setTimeout(page_rotation_startswap, interval);	// Wait for the next frame to play again :(

					});

				} else {
					
					$('#rotation-frame').css({'margin': '0'});
					$('#rotation-frame').animate({'opacity': 'show'}, 1000);

					clearTimeout(timeout);
					timeout = setTimeout(page_rotation_startswap, interval);

				}

			}

		};

		function page_rotation_startswap () {

			$('#page-rotation-initial').css('display', 'none');
			index = (index === pages.length - 1) ? 0 : index + 1;

			$('#rotation-frame').animate({'opacity': 'hide'}, 400);

			if (pages[index].indexOf('widget') > 0) {

				$.ajax(basepath + '/tac', {
					crossDomain: true,
					complete: page_rotation_endswap
				});

			} else {

				$.ajax(basepath + pages[index], {
					crossDomain: true,
					complete: page_rotation_endswap
				});

			}

		};

		function page_rotation_restart() {
			clearTimeout(timeout);
			page_rotation_startswap();
		}

		function page_rotation_load () {

			$.ajax(basepath + '/ajax/get_setting', {
				data: {
					'type': 'rotation_queue',
					'page': 'page_rotation'
				},
				type: 'POST',
				complete: function (xhr) {

					pages = JSON.parse(JSON.parse(xhr.responseText)['rotation_queue']) || [];

					if (pages.length >= 1) {
						$('#page-rotation-play').css('opacity','1.0');
					}

					for (var i = 0; i < pages.length; i += 1) {

						var value = pages[i],
							name = name = valueToName(value);;

						$('#page-rotation-fields-list').append(
							$('<li><input type="checkbox" checked="true" value="' + value + '" /> ' + name + '</li>')
						);

					}

					paused = false;
					if (pages.length > 0) {
						page_rotation_startswap();
					}

				}
			});

		}

		function page_rotation_save () {

			var button = $('#page-rotation-save');
			button.css('opacity', '0.4');

			$.ajax(basepath + '/ajax/save_page_setting', {
				data: {
					'type': 'rotation_queue',
					'page': 'page_rotation',
					'setting': JSON.stringify(pages)
				},
				type: 'POST',
				complete: function (xhr) {
					button.css('opacity', '1.0');
				}
			});

		};

		$('#page-rotation-save').click(page_rotation_save);

		function page_rotation_show_gui () {
			$('#page-rotation-opts').fadeIn(400);
			$('#page-rotation-views').fadeIn(400);
			$('#content').animate({'margin-top': '48px'}, {'duration': 200, 'queue': false});
			$('#header').fadeIn(400);
		};

		function page_rotation_hide_gui () {
			$('#page-rotation-opts').fadeOut(400);
			$('#page-rotation-views').fadeOut(400);
			$('#content').animate({'margin-top': '0px'}, {'duration': 800, 'queue': false});
			$('#header').fadeOut(400, function () {
				optsstick = false;
			});
		};

		function hold () {

			/*
			*	Resets the timeout if any action is taken, usefull if someone notces something they want to investigate,
			*	we do this on click and mousemove.
			*/

			if (!optsstick) {

				page_rotation_show_gui();

				if (optstimeout) {
					clearTimeout(optstimeout);
				}

				optstimeout = setTimeout(function () {
					page_rotation_hide_gui();
				}, 1000);
			}

			if (!paused) {
				clearTimeout(timeout);
				timeout = setTimeout(page_rotation_startswap, interval);
			}

		};

		$(document).click(hold).mousemove(hold);

		/* Tons of options bindings, think about making better... */

			$('#page-rotation-opts, #page-rotation-views').hover(function () {

				/* Hovering the GUI will make it not hide after X milliseconds. */

				clearTimeout(optstimeout);
				optsstick = true;
			}, function () {
				page_rotation_hide_gui();
			});

			$('#page-rotation-faster').click(function () {
				if (paused) {
					if (interval < max_interval) {
						interval += interval_increment;
						$('#page-rotation-speed').attr('value', interval / 1000);
					}
				}
			});

			$('#page-rotation-slower').click(function () {
				if (paused) {
					if (interval > min_interval) {
						interval -= interval_increment;
						$('#page-rotation-speed').attr('value', interval / 1000);
					}
				}
			});

			$('#page-rotation-add').click(function () {

				var value = $('#page-rotation-new').attr('value');

				if (value.indexOf('widget') > 0) {

					// Holder for widgets

				} else {

					if (value.indexOf('index.php') > 0)
						value = value.split('index.php')[1];

					if (value[0] != '/')
						value = '/' + value;

					var name = valueToName(value);

					$.ajax(basepath + value, {
						type: 'HEAD',
						crossDomain: true,
						complete: function (xhr) {
							if (xhr.status == 200) {

								$('#page-rotation-fields-list').append(
									$('<li><input type="checkbox" checked="true" value="' + value + '" /> ' + name + '</li>')
								);
								pages.push(value);

							} else {

								page_rotation_note('HEAD test of the given URI could not be resolved, try copying URI\'s from existing pages');

							}
						}
					});
				}
				
			});

			$(document).bind('keypress',function (e) {
				if (e.which == 32) {
					if (pages[index].indexOf('widget') > 0) {
						window.location.href = basepath + '/tac';
					} else {
						window.location.href = basepath + pages[index];
					}
				}
			});

			$('#page-rotation-goto').click(function () {
				if (pages[index].indexOf('widget') > 0) {
					window.location.href = basepath + '/tac';
				} else {
					window.location.href = basepath + pages[index];
				}
			});

			$('#page-rotation-pause').click(function () {
				
				clearTimeout(timeout);

				$('#page-rotation-pause').css('display','none');
				$('#page-rotation-play').css('display','inline-block');

				$('#page-rotation-next').css('opacity','0.5');
				$('#page-rotation-prev').css('opacity','0.5');

				$('#page-rotation-faster').css('opacity','1.0');
				$('#page-rotation-slower').css('opacity','1.0');

				paused = true;
			});

			$('#page-rotation-play').click(function () {
				
				page_rotation_startswap();

				$('#page-rotation-play').css('display','none');
				$('#page-rotation-pause').css('display','inline-block');

				$('#page-rotation-next').css('opacity','1.0');
				$('#page-rotation-prev').css('opacity','1.0');

				$('#page-rotation-faster').css('opacity','0.5');
				$('#page-rotation-slower').css('opacity','0.5');

				paused = false;
			});

			$('#page-rotation-next').click(function () {
				if (!paused) {
					page_rotation_restart();
				}
			});

			$('#page-rotation-prev').click(function () {
				if (!paused) {

					index -= 2;
					if (index <= -2) {
						index = pages.length - 2;
					}

					page_rotation_restart();
				}
			});

		//	Load current settings, it there are saved pages it will 
		//	start the rotation automatically

		page_rotation_load();

	});

}());