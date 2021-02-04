
if (typeof Object.assign != 'function') {
	Object.assign = function(target) {
		'use strict';
		if (target == null) {
			throw new TypeError('Cannot convert undefined or null to object');
		}

		target = Object(target);
		for (var index = 1; index < arguments.length; index++) {
			var source = arguments[index];
			if (source != null) {
				for (var key in source) {
					if (Object.prototype.hasOwnProperty.call(source, key)) {
						target[key] = source[key];
					}
				}
			}
		}
		return target;
	};
}

function LightboxError(message) {
	this.name = 'LightboxError';
	this.message = message;
	this.stack = (new Error()).stack;
}

LightboxError.prototype = new Error;

/**
 * Manages instances of Lightbox, in order to "close the last one that was
 * opened" and probably other use cases as well :)
 *
 * In order to keep track of every currently opened Lightbox, the Lightbox
 * class is private and stored within LightboxManager.
 */
var LightboxManager = (function() {

	/**
	 * Instantiates a new lightbox, the new lightbox is not automatically
	 * visible, running <Lightbox>.show will reveal its modal.
	 *
	 * @param object options
	 * @return Lightbox
	 */
	var Lightbox = (function () {

		/**
		 * Default options for the lightbox, can be overridden by the options
		 * object passed to the constructor
		 */
		var DEFAULT_OPTIONS = {
		};

		var element = function (type, attributes) {

			var node = document.createElement(type);
			attributes = (attributes) ? attributes : {};

			Object.keys(attributes).forEach(function (attr) {
				node.setAttribute(attr, attributes[attr]);
			});

			return node;

		}

		var LB = function (input_options) {

			input_options = (input_options) ? input_options : {};

			var box = Object.create(null);
			var options = Object.assign({}, DEFAULT_OPTIONS, input_options);

			var overlay = element('div', {'class': 'lightbox-overlay'});
			var node = element('div', {'class': 'lightbox'});
			var loading = element('div', {'class': 'lightbox-loading'});
			var content = element('div', {'class': 'lightbox-content'});
			var header = element('div', {'class': 'lightbox-header'});
			var footer = element('div', {'class': 'lightbox-footer'});

			loading.textContent = "LOADING LOADING LOADING";
			node.appendChild(header);
			node.appendChild(content);
			node.appendChild(footer);
			overlay.appendChild(node);
			overlay.appendChild(loading);

			/**
			 * Make query selection of Lightbox content available
			 */
			box.querySelector = content.querySelector.on(node);
			box.node = node;

			/**
			 * Sets the content of the Lightbox, if a Node is given it will be appended
			 * is given it will wait for the result and then append the content
			 * returned.
			 *
			 * @throws LightboxError If the source passed is not a Node
			 * @param mixed source
			 * @return Lightbox self
			 */
			box.content = function (source) {
				if (source instanceof Node) {
					content.appendChild(source);
				} else if (source instanceof NodeList) {
					Array.prototype.slice.call(source, 0).forEach(box.content);
				} else throw new LightboxError("Source passed to content must be an instanceof Node or NodeList");
				return box;
			};


			/**
			 * Sets the footer of the Lightbox, if a Node is given it will be appended
			 * is given it will wait for the result and then append the content
			 * returned.
			 *
			 * @throws LightboxError If the source passed is not a Node
			 * @param mixed source
			 * @return Lightbox self
			 */
			box.footer = function (source) {
				if (source instanceof Node) {
					footer.appendChild(source);
				} else if (source instanceof NodeList) {
					Array.prototype.slice.call(source, 0).forEach(box.footer);
				} else throw new LightboxError("Source passed to footer must be an instanceof Node or NodeList");
				return box;
			};

			/**
			 * Sets the header of the Lightbox, if a Node is given it will be appended
			 * is given it will wait for the result and then append the content
			 * returned.
			 *
			 * @throws LightboxError If the source passed is not a Node
			 * @param mixed source
			 * @return Lightbox self
			 */
			box.header = function (source) {
				var close_button = element("a", {
					"class": "icon-cancel link-icon",
					"title": "Close this window",
					"href": "#"
				});
				close_button.addEventListener("click", function(ev) {
					LightboxManager.remove_topmost();
					ev.preventDefault();
					return false;
				});

				if (source instanceof Node) {
					source.appendChild(close_button);
					header.appendChild(source);
				} else if (source instanceof NodeList) {
					Array.prototype.slice.call(source, 0).forEach(box.header);
					source.appendChild(close_button);
				} else throw new LightboxError("Source passed to footer must be an instanceof Node or NodeList");
				return box;
			};

			box.hide = function () {
				overlay.style.display = 'none';
				return box;
			};

			/**
			 * Shows the Lightbox to the user, if the Lightbox is not already in the
			 * DOM it will be appended.
			 *
			 * @return Lightbox self
			 */
			box.show = function () {

				overlay.style.display = 'flex';
				node.style.display = 'flex';
				return box;

			};

			box.loading = function (toggle) {
				if (toggle) {
					overlay.style.display = 'flex';
					node.style.display = 'none';
					loading.style.display = 'flex';
				} else {
					loading.style.display = 'none';
				}
			};

			box.button = function (label, callback) {
				var button = document.createElement('button');
				var proxy = function () {
					if (callback(box)) {
						button.removeEventListener('click', proxy, false);
					}
				};

				button.className = 'info state-background';
				button.textContent = label;
				button.setAttribute('title', label);

				button.addEventListener('click', proxy, false);
				box.footer(button);
				return button;
			};

			box.buttons = function (setup) {
				setup = (setup) ? setup : {};
				Object.keys(setup).forEach(function (label) {
					box.button(label, setup[label]);
				});
				return box;
			};

			/**
			 * Removes the Lightbox from the DOM, should you want to not use it
			 * anymore simply leave it unreferenced and out of scope and the GC will
			 * take care of it for you
			 *
			 * @return Lightbox self
			 */
			box.remove = function () {
				overlay.parentNode.removeChild(overlay);
				return box;
			};

			/**
			 * Apply the options using above declared functions
			 */
			if (options.title) box.header(options.title);
			if (options.content) box.content(options.content);

			document.body.appendChild(overlay);

			return box;

		};

		return LB;

	})();

	var escape_handler = function(event) {
		var tag_name = (event.target || event.srcElement).tagName;
		if(tag_name == 'INPUT' || tag_name == 'SELECT' || tag_name == 'TEXTAREA') {
			return;
		}
		if(event.key && event.key == "Escape") {
			LightboxManager.remove_topmost();
		} else if(event.keyCode && event.keyCode == 27) {
			// escape was pressed
			LightboxManager.remove_topmost();
		}
	};

	var boxes = [];
	var api = function() {
		return {
			/**
			 * Helper function to generate a Lightbox with the result of a GET request, use
			 * when generating forms are generated in backend as an ajax service.
			 *
			 * @param string title_text Title to set on the Lightbox
			 * @param string url To fetch
			 * @return Lightbox The created lightbox
			 */
			"ajax_form_from_href": function (title_text, source) {
				var lightbox = LightboxManager.create();
				var title = document.createElement('h1');
				title.textContent = title_text;

				lightbox.loading(true);
				lightbox.header(title);

				$.ajax({
					url : source,
					type : 'GET',
					success : function(data) {

						if (data && data.redirect) {
							window.location.href = data.redirect;
							return;
						}

						var fragment = document.createElement('div');
						fragment.innerHTML = data;

						var forms = fragment.querySelectorAll('form');
						var buttons;
						for(var i = 0; i < forms.length; i++) {
							if(i == forms.length - 1) {
								// the last form is by convention the
								// one with the buttons that we will
								// place in the lightbox's footer
								button_fieldset = forms[i].querySelector('.nj-form-buttons');
								if(button_fieldset) {
									forms[i].removeChild(button_fieldset);
									lightbox.footer(button_fieldset);
								}
							}
							FormModule.add_form($(forms[i]));
						}

						lightbox.content(fragment.childNodes);
						lightbox.loading(false);
						lightbox.show();

					},
					error : function(data) {
						Notify.message(data.responseText, {type: 'error', sticky: true});
						lightbox.remove();
					}
				});

				return lightbox;

			},
			"alert": function(text) {
				var lightbox = LightboxManager.create();

				var heading = document.createElement("h1");
				heading.textContent = "For your information";
				lightbox.header(heading);

				var text_node = document.createElement("p");
				text_node.textContent = text;
				lightbox.content(text_node);
				lightbox.button("OK", function() {
					LightboxManager.remove_topmost();
				});
				lightbox.show();
				return lightbox;
			},
			/**
			 * Almost drop-in replacement for window.confirm() (you
			 * get callbacks instead of a return value).
			 *
			 * @param string question Question
			 * @param object options:
			 *  @param string yes text or {text: x, cb: y}
			 *  @param string no text or {text: x, cb: y}
			 * @throws LightboxError if options is malformed
			 */
			"confirm": function(question, options) {
				var lb = LightboxManager.create();

				lb.content(document.createTextNode(question));

				var heading = document.createElement("h1");
				heading.textContent = "Confirm";
				lb.header(heading);

				var lbm = LightboxManager;
				var yes_cb = lbm.remove_topmost;
				var no_cb = lbm.remove_topmost;
				var yes_text = "OK";
				var no_text = "Cancel";

				if(typeof options.yes === "string") {
					yes_text = options.yes;
				} else if(typeof options.yes === "object") {
					if(!options.yes.text || typeof options.yes.text !== "string") {
						throw new LightboxError("Options must be a string or {text: x, cb: y}");
					}
					if(options.yes.cb && typeof options.yes.cb === "function") {
						yes_cb = function() {
							options.yes.cb();
							lbm.remove_topmost();
						};
					} else {
						yes_cb = function() {
							lbm.remove_topmost();
						};
					}
					yes_text = options.yes.text;
				}
				if(typeof options.no === "string") {
					no_text = options.no;
				} else if(typeof options.no === "object") {
					if(!options.no.text || typeof options.no.text !== "string") {
						throw new LightboxError("Options must be a string or {text: x, cb: y}");
					}
					if(options.no.cb && typeof options.no.cb === "function") {
						no_cb = function() {
							options.no.cb();
							lbm.remove_topmost();
						};
					} else {
						no_cb = function() {
							lbm.remove_topmost();
						};
					}
					no_text = options.no.text;
				}

				var button_to_focus = "";
				if(typeof options.focus === "string") {
					button_to_focus = options.focus;
				}

				// in order to .trigger('focus') below, we need to have
				// the lb visible. this should be ok, since
				// lb.button() is really cheap.
				lb.show();

				if(button_to_focus === "no") {
					lb.button(no_text, no_cb).trigger('focus');
				} else {
					lb.button(no_text, no_cb);
				}
				if(button_to_focus === "yes") {
					lb.button(yes_text, yes_cb).trigger('focus');
				} else {
					lb.button(yes_text, yes_cb);
				}
			},
			"create": function() {
				if(!boxes.length) {
					document.addEventListener('keydown', escape_handler, false);
				}
				var lb = new Lightbox();
				boxes.push(lb);
				return lb;
			},
			"remove_all": function() {
				for(var i = 0; i < boxes.length; i++) {
					boxes[i].remove();
				}
				boxes.length = 0;
			},
			"remove_topmost": function() {
				var topmost = LightboxManager.topmost();
				if(topmost) {
					topmost.remove();
					delete boxes[boxes.length-1];
					boxes.splice(boxes.length-1, 1);
				}
			},
			"topmost": function() {
				if(boxes.length) {
					return boxes[boxes.length-1];
				}
				return undefined;
			}
		};
	}();
	return api;
})();
