
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
 * Instantiates a new lightbox, the new lightbox is not automatically
 * visible, running <Lightbox>.show will reveal its modal.
 *
 * @param object options
 * @return Lightbox
 */
var Lightbox = (function () {

	/**
	 * Default options for the lightbox, can be overriden by the options
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
		box.querySelector = content.querySelector.bind(node);
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
			if (source instanceof Node) {
				header.appendChild(source);
			} else if (source instanceof NodeList) {
				Array.prototype.slice.call(source, 0).forEach(box.footer);
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
			return box;

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

/**
 * Helper function to generate a Lightbox with the result of a GET request, use
 * when generating forms are generated in backend as an ajax service.
 *
 * @param string title_text Title to set on the Lightbox
 * @param string url To fetch
 * @return Lightbox The created lightbox
 */
Lightbox.ajax_form_from_href = function (title_text, source) {

	var lightbox = new Lightbox();
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

			var form = fragment.querySelector('form');
			var buttons = form.querySelector('.nj-form-buttons');

			if (buttons) {
				form.removeChild(buttons);
				lightbox.footer(buttons);
			}

			lightbox.content(fragment.childNodes);
			FormModule.add_form($(form));

			lightbox.loading(false);
			lightbox.show();

		},
		error : function(data) {
			Notify.message(data.responseText, {type: 'error', sticky: true});
			lightbox.remove();
		}
	});

	return lightbox;

};

