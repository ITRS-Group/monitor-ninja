
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

	var STATUS_PAUSED = 0,
		STATUS_PLAYING = 1;

	var RotationalView = function () {

		var self = Object.create(RotationalView.prototype);

		self.config = {

			basepath: _site_domain + _index_page,

			max_interval: 30000,
			min_interval: 5000,
			interval_increment: 1000,
			interval: 10000

		};

		self.showing = false;
		self.status = STATUS_PAUSED;
		self.hideTimer;

		self.frame = $('#rotation-frame');
		self.help = $('#page-rotation-initial');

		self.x_css = [];
		self.x_scripts = [];

		self.pages = [];
		self.current = -1;
		self.timer;

		$.ajax(self.config.basepath + '/ajax/get_setting', {
			
			data: {
				'type': 'rotation_queue',
				'page': 'page_rotation'
			},

			type: 'POST',

			complete: function (xhr) {

				var pages = JSON.parse(JSON.parse(xhr.responseText)['rotation_queue']) || [];

				if (pages.length >= 1) {
					$('#page-rotation-play').css('opacity','1.0');
				}

				for (var i = 0; i < pages.length; i += 1) {
					self.add(pages[i]);
				}

			}
		});

		self.events();

		return self;

	};

	RotationalView.prototype = {

		page: function () {
			return this.pages[this.current];
		},

		hoisting: function hoisting (frame) {

			var self = this;

			frame.load(function () {

				var contents = frame.contents().find('body').children(),

					scripts = frame.contents().find('script'),
					css = frame.contents().find('link[rel="stylesheet"]'),

					cscripts = $('head').find('script'),
					ccss = $('head').find('link[rel="stylesheet"]');

				scripts.each(function () {

					var i = cscripts.length,
						insert = true;

					if (this.src) {

						for (i; i--;)
							insert = (this.src == cscripts[0].src) ? false : insert;

						if (insert) {
							$('head').prepend($(this));
							addedscripts.push($(this));
						}
					}

				});

				css.each(function () {

					var i = ccss.length,
						insert = true;

					if (this.href) {

						for (i; i--;)
							insert = (this.href == ccss[i].href) ? false : insert;

						if (insert) {
							$('head').prepend($(this));
							addedcss.push($(this));
						}

					}

				});

				self.frame.html('').
					append(contents).
					css({'margin': '1% auto'}).
					animate({'opacity': 'show'}, 1000);

				clearTimeout(self.timer);
				self.timer = setTimeout(function () {
					self.rotate();
				}, self.config.interval);

			});
		},

		interpret: function interpret (request) {

			var tmp = document.implementation.createHTMLDocument("tmp"),
				self = this,
				doc = null;

			tmp.documentElement.innerHTML = request.responseText;
			doc = $(tmp);

			for (var i = 0; i < this.x_scripts.length; i += 1)
				this.x_scripts[i].remove();

			for (var i = 0; i < this.x_css.length; i += 1)
				this.x_css[i].remove();

			this.x_scripts = [];
			this.x_css = [];
			this.frame.html('');

			if (this.page().indexOf('widget') > 0) {

				var name = this.page().split(' ')[0];
					widget = doc.find("[id*=" + name + "]");

				if (widget) {
					this.frame.append(widget.children());
				} else {
					this.frame.html('Could not resolve widget ' + name);
				}

			} else {

				this.frame.append(
					doc.find('.content').children()
				);

				var frame = this.frame.find('iframe');

				if (frame.length > 0) {

					this.hoisting(frame);

				} else {
					
					this.frame.css({'margin': '0'});
					this.frame.animate({'opacity': 'show'}, 1000);

					clearTimeout(this.timer);
					this.timer = setTimeout(function () {
						self.rotate();
					}, this.config.interval);

				}

			}
		},

		rotate: function rotate () {

			var self = this,
				callback = function (request) {
					if (request.status != 200) {
						self.note('The requested frame did not return status 200, ' +
								'jumping to next in 3 seconds', self.rotate);
					}
					self.interpret(request);
				},
				path;

			this.help.css('display', 'none');
			this.current = (this.current === this.pages.length - 1) ? 0 : this.current + 1;
			this.frame.animate({'opacity': 'hide'}, 400);

			if (this.page().indexOf('widget') > 0) {
				path = this.config.basepath + '/tac';
			} else {
				path = this.config.basepath + this.pages[this.current];
			}

			$.ajax(path, {
				crossDomain: true,
				complete: callback
			});

		},

		note: function note (message, ref) {
			if (typeof(ref) == 'function') {
				$('#page-rotation-note').html(message).fadeIn(500).delay(2000).fadeOut(1000, ref);
			} else {
				$('#page-rotation-note').html(message).fadeIn(500).delay(2000).fadeOut(1000);
			}
		},

		hide: function () {
			$('#page-rotation-opts').fadeOut(400);
			$('#page-rotation-views').fadeOut(400);
			$('#content').animate({'margin-top': '0px'}, {'duration': 800, 'queue': false});
			$('#header').fadeOut(400, function () {
				optsstick = false;
			});
		},

		show: function () {
			$('#page-rotation-opts').fadeIn(400);
			$('#page-rotation-views').fadeIn(400);
			$('#content').animate({'margin-top': '48px'}, {'duration': 200, 'queue': false});
			$('#header').fadeIn(400);
		},

		add: function add (uri) {

			var self = this,
				value = uri;

			if (value.indexOf('widget') > 0) {

				// Holder for widgets

			} else {

				if (value.indexOf('index.php') > 0)
					value = value.split('index.php')[1];

				if (value[0] != '/')
					value = '/' + value;

				value += ( value.indexOf('rotation_token') > 0 ) ? "" : 
					( value.indexOf('?') > 0 ) ? 
					'&rotation_token=' + (new Date()).getTime() : 
					'?rotation_token=' + (new Date()).getTime();

				$.ajax(this.config.basepath + value, {
					type: 'GET',
					crossDomain: true,
					complete: function (request) {

						var title = request.responseText,
							i1 = title.indexOf('<title>') + 7,
							i2 = title.indexOf('</title>') - i1;

						title = title.substr(i1, i2);

						if (request.status == 200) {

							$('#page-rotation-fields-list').append(
								$('<li>').append(
									$('<input type="checkbox" checked="true" value="' + value + '" />').bind('click', function (e) {
										arrayRemoveValue(self.pages, $(e.target).val());
									})
								).append(
									$('<span>').text(' ' + title)
								)
							);

							self.pages.push(value);

						} else {

							self.note('HEAD test of the given URI could not be resolved!<br /><br />' +
								'This is either due to a faulty URI or the request is targetting a ' +
								'page outside of Op5 Monitors Web Interface.<br />' +
								'Try copying URI\'s from existing pages in Op5 Monitors Web Interface.');

						}
					}
				});

			}
			
		},

		save: function save () {

			var button = $('#page-rotation-save');
			button.css('opacity', '0.4');

			$.ajax(this.config.basepath + '/ajax/save_page_setting', {
				data: {
					'type': 'rotation_queue',
					'page': 'page_rotation',
					'setting': JSON.stringify(this.pages)
				},
				type: 'POST',
				complete: function (request) {
					button.css('opacity', '1.0');
				}
			});

		},

		gotoPage: function () {
			if (this.page().indexOf('widget') > 0) {
				window.location.href = this.config.basepath + '/tac';
			} else {
				window.location.href = this.config.basepath + this.page();
			}
		},

		events: function () {

			var self = this,

				reveal = function () {

					if (!self.showing) {
						self.show();
						self.showing = true;
					}

					if (self.hideTimer)
						clearTimeout(self.hideTimer);
					self.hideTimer = setTimeout(function () {
						self.hide();
						self.showing = false;
					}, 2000);

				};

			this.show();

			$('#page-rotation-add').bind('click', function () {self.add($('#page-rotation-new').attr('value'));});
			$('#page-rotation-save').bind('click', function () {self.save();});

			$('#page-rotation-prev').bind('click', function () {
				self.current -= 2;
				self.current = (self.current < -1) ? self.pages.length - 2 : self.current;
				self.rotate();
			});

			$('#page-rotation-next').bind('click', function () {
				self.rotate();
			});

			$('#page-rotation-play').bind('click', function () {

				self.status = STATUS_PLAYING;
				self.rotate();

				clearTimeout(self.hideTimer);
				$(window).unbind('mousemove', reveal);
				$(window).bind('mousemove', reveal);

				$('#page-rotation-play').css('display', 'none');
				$('#page-rotation-pause').css('display', 'inline-block');
			});

			$('#page-rotation-pause').bind('click', function () {

				self.status = STATUS_PAUSED;
				clearTimeout(self.timer);

				clearTimeout(self.hideTimer);
				$(window).unbind('mousemove', reveal);

				$('#page-rotation-play').css('display', 'inline-block');
				$('#page-rotation-pause').css('display', 'none');
			});

			$('#page-rotation-goto').bind('click', function () {
				self.gotoPage();
			});

		}

	}

	$(window).bind('load', function onmainload () {
		RotationalView();
	});

}());