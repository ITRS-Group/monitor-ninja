
if (!window.mlib) {
	window.mlib = {};
}

mlib.get = (function () {
	var ID_CONDITION = '#',
		CLASS_CONDITION = '.',
		TYPE_VALUE_CONDITION = '=',
		ATTRIBUTE_CONDITION = '@',

		NLConcat = function (arr, nodelist) {
			
			var i = 0;

			for (i; i < nodelist.length; i += 1) {
				arr.push(nodelist.item(i));
			}

			return arr;

		},

		matchup = function (items, selectors) {
			
			var x, y;

			for (x = items.length; x--;) {

				for (y = selectors.length; y--;) {
					
					selector = selectors[y].replace(/^\s+|\s+$/g,'');
					type = selector[0];

					if ( 
						
						// If selector is an ID selector, '#', check if target id 
						// is the same as the selectors
						
						(type == ID_CONDITION) ? 
						(items[x].id === selector.substr(1)): 
						
						// If selector is a Class selector, '.', check if target class 
						// contains the selectors
						
						(type == CLASS_CONDITION) ? 	
						(items[x].className.indexOf(selector.substr(1)) >= 0):
						
						// If no selector type is given, assume tag selector, check if  
						// target tag is the same as the selectors
						
						(true) ? 
						(items[x].tagName.toLowerCase() === selector.toLowerCase()) : false ) {



					} else {
						items.remove(x);
						break;
					}
				}

			}

			return items;

		};

	return function get (condition, one, fromNode) {
			
			/**
			*	Check if the conditions of the event are met
			* on the triggering element.
			*
			* @param string event
			* @param string condition
			*/

			one = one || false;
			fromNode = fromNode || document;
			
			var selectors = condition.split(' '),
				results = [], 
				selector = selectors[0].replace(/^\s+|\s+$/g,''),
				type = selector[0];

			if ( 
				
				// If selector is an ID selector, '#', check if target id 
				// is the same as the selectors
				
				(type == ID_CONDITION) ? 
				(results.push(fromNode.getElementById(selector.substr(1)))): 
				
				// If selector is a Class selector, '.', check if target class 
				// contains the selectors
				
				(type == CLASS_CONDITION) ? 	
				(results = NLConcat(results, fromNode.getElementsByClassName(selector.substr(1)))):
				
				// If no selector type is given, assume tag selector, check if  
				// target tag is the same as the selectors
				
				(true) ? 
				(results = NLConcat(results, fromNode.getElementsByTagName(selector))) : false ) {

			} else {
				return false;
			}

			selectors.shift();
			
			if (selectors.length >= 1) {
				results = matchup(results, selectors);
			}

			if (one === true || type === '#') {
				return results[0];
			}

			return results;
	}

}());