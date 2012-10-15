
/**
*	bind polled selector bind event micro-lib
*
*		This is not an all in event system, it only supports events
*		using a DOM element target.
*	
*		Bind does not wrap your elements and store them, bind
*		does not add events upon all matching elements directly.
*		
*		Bind adds one event / type and checks the target against
*		conditional statements to know which callbacks to run.
*
*		Bind will not help you increase performance on your business 
*		website, blog or forum, use jQuery. Bind helps in heavy 
*		Rich Internet Application where event-counts begin to scale
*		through the roof.
*
*		Though a JSPerf test did result in 3 times as many itterations
*		in the same period as jQuerys event binding.
*		
*		interface mlib bind component {
*			number id bind(in DOMString selector, in DOMString event, in funtion callback);
*			number id bind(in DOMString event, in funtion callback);
*			void unbind(in DOMString event, in DOMString callback);
*			void unbind(in number id);
*		};
*/

if (!window.mlib) {
	window.mlib = {};
}

mlib.bind = (function () {
	
	var CALLBACK = 0, 
		CONDITION = 1,
		ID_CONDITION = '#',
		CLASS_CONDITION = '.',
		TYPE_VALUE_CONDITION = '=',
		ATTRIBUTE_CONDITION = '@',

		ERRORS = {
			ARG_ERR: 'mlib.bind expects 3 (string condition, string event, function listener) OR 2 (string event, string listener) arguments; you supplied: ',
			ARG_EVENT_DOM2_ERR: 'mlib.bind expects event names in the DOM 2 format e.g "click", not "onclick"!'
		}
		
		events = {},
		
		multiCall = function (e) {
			
			/**
			* Check all events of this type against conditions
			*/

			e = e || window.event;

			console.log(e.target);

			if (!e.target) {
				console.log(e.srcElement);
				e.target = e.srcElement;
			}
			
			console.log(e.target);

			var i = events[e.type].length;
			
			for (i; i--;) {
				events[e.type][i](e);
			}
			e.stopPropagation();

		},
		
		addEvent = function (node, event, callback) {
		
			/**
			*	Add event to node x-browser compatible.
			*	Since we only use one event/eventtype it is also
			* legacy compatible
			*/

			try {
				if (node.addEventListener) {
					node.addEventListener(event, callback, false);
				} else if (node.attachEvent) {
					node.attachEvent('on' + event, callback);
				} else {
					node['on' + event] = callback;
				}
			} catch (Exception) {
				throw new Error(Exception);
			}
		},
		
		removeEvent = function (node, event, callback) {
			
			/**
			*	Remove event from node x-browser compatible
			*	Since we only use one event/eventtype it is also
			* legacy compatible
			*/
			
			try {
				if (node.removeEventListener) {
					node.removeEventListener(event, callback, false);
				} else if (node.detachEvent) {
					node.detachEvent('on' + event, callback);
				} else {
					node['on' + event] = null;
				}
			} catch (Exception) {
				throw new Error(Exception);
			}
		},
		
		hook = function (e, callback, node) {

			/**
			* Hook the condition to an event and add it to the
			* highest common element in the DOM hierarchy.
			* If the event-type is already hooked; add the conditions
			* of the new selector.
			*
			* @param string event
			* @param function callback
			* @param string conditions
			*/

			node = node || document;

			if (events[e]) {
			
				events[e].push(callback);
				
			} else {

				events[e] = [];
				events[e].push(callback);
				addEvent(node, e, multiCall);
				
			}

			return events[e].length - 1;
			
		},
		
		resolveCondition = function (e, condition) {
			
			/**
			*	Check if the conditions of the event are met
			* on the triggering element.
			*
			* @param string event
			* @param string condition
			*/
			
			var selectors = condition.split(' '),
				result = true, 
				type = null,
				string = null,
				selector = null, 
				i;

			for (i = selectors.length; i--;) {
				
				selector = selectors[i].replace(/^\s+|\s+$/g,'');
				type = selector[0];
				string = selector.substr(1);
				
				if ( 
					
					// If selector is an ID selector, '#', check if target id 
					// is the same as the selectors
					
					(type == ID_CONDITION) ? 
					(e.target.id == selector.substr(1)): 
					
					// If selector is a Class selector, '.', check if target class 
					// contains the selectors
					
					(type == CLASS_CONDITION) ? 	
					( e.target.className.indexOf(selector.substr(1)) >= 0 ? true : 
						false):
					
					// If selector is a Type selector, '=', check if target type 
					// is the same as the selectors
					
					(type == TYPE_VALUE_CONDITION) ? 
					( e.target.type && 
						e.target.type.toLowerCase() == selector.substr(1) ):
					
					// If selector is an Attribute selector, '@', check if target has 
					// attribute given by selector
					
					(type == ATTRIBUTE_CONDITION) ? 
					e.target.hasAttribute(selector.substr(1)):
					
					// If no selector type is given, assume tag selector, check if  
					// target tag is the same as the selectors
					
					(true) ? 
					(e.target.tagName.toLowerCase() == selector) : false ) {
					continue;				
				} else {
					result = false;
					break;
				}
			}
			
			return result;
		
		};

	mlib.fire = function (event) {
		multiCall(event);
	};

	mlib.unbind = function () {
		
		if (arguments.length === 1) {
			delete events[arguments[0]];
		} else if (arguments.length === 2) {

		}

	};

	return function bind () {

		/**
		*	Add this event-type, condition and callback into binds
		* event delegation system
		*
		* @param string condition
		* @param string event
		* @param function callback
		*/

		var bindargs = arguments;

		var argformat = function (args) {
			var tmp = '';
			for (var i = 0; i < args.length; i += 1) {
				tmp += typeof(args[i]);
				if (i < args.length - 1) {
					tmp += ', ';
				}
			}
			return '(' + tmp + ')';
		}

		if (bindargs.length === 3) {
			if (bindargs[1].indexOf('on') === 0) throw new Error(ERRORS.ARG_EVENT_DOM2_ERR);
			return hook (bindargs[1], function conditionalCallback (e) {
				if (resolveCondition(e, bindargs[0])) {
					bindargs[2](e);
				}
			});

		} else if (bindargs.length === 2 || bindargs.length === 4) {
			
			if (bindargs[0].indexOf('on') === 0) throw new Error(ERRORS.ARG_E_DOM2_ERR);
			
			if (bindargs.length === 4) {
				return hook (bindargs[0], bindargs[1], bindargs[3]);
			} else {
				return hook (bindargs[0], bindargs[1]);
			}

		} else {
			throw new Error(ERRORS.ARG_ERR + argformat(bindargs));
		}
		
	};
	
}());