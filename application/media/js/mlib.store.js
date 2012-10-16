/**
*	store crossbrowser session/cookie storage micro-lib
*		
*		Session data does NOT persist if the tab/window is closed.
*
*		Store session data cross-browser using the later/better sessionStorage
*		or cookies with an easy to handle API instead of roughin up the cookie
*		every time.

*		interface mlib storage component {
*			object storage();
*			mixed stored(in DOMString key);
*			void store(in DOMString key, in mixed data);
*			void unstore(in DOMString key);
*			void clear();
*		};
*/

if (!window.mlib) {
	window.mlib = {};
}

var u = mlib;

mlib.store = (function () {

	var canSessionStore = (window.sessionStorage) ? true : false,

		storageblock = function (data, exp) {

			var that = {};
			
			that.data = data || '';
			that.expires = exp || '';

			that.toString = function () {return that.data + '|' + that.expires; }
			
			return that;
		
		},

		storage = {},

		renew = function () {

			var cookiesstrings = document.cookie.split(';'),
				ttime = (new Date()).getTime(),
				data = null,
				temp = null;

			if (canSessionStore) {
				for (var i = window.sessionStorage.length; i--;) {
					
					temp = window.sessionStorage.key(i);
					storage[temp] = window.sessionStorage.getItem(temp);
					data = storage[temp].split('|');
					storage[temp] = storageblock(data[0].trim(), data[1].trim());
					
					if (storage[temp].expires < ttime) {
						mlib.unstore(temp);
					}

				}
			} else {

				for (var i = cookiesstrings.length; i--;) {

					if (cookiesstrings[i]) {
						
						temp = cookiesstrings[i].split('=');
						data = temp[1].split('|');

						storage[temp[0].trim()] = storageblock(data[0].trim(), data[1].trim());

					}
				}
			}

		};

	mlib.storage = function () {

		return storage;

	};

	mlib.stored = function (key) {

		return storage[key];

	};

	mlib.unstore = function (key) {
		
		/**
		*	Remove the data stored under the given key
		*
		* Use key "MLIB_UNSTORE_ALL" to remove all session data
		*
		*	@param DOMString key
		*/

		var prop = null;

		if (key === 'MLIB_UNSTORE_ALL') {
			
			if (canSessionStore) {
				window.sessionStorage.clear();
			} else {
				for (prop in storage) {
					mlib.unstore(prop);
				}
			}

			renew();
			storage = {};	//	Safety since there might be a delay in cookies/storage
		
		} else {
			if (canSessionStore) {
				window.sessionStorage.removeItem(key);
			} else {
				document.cookie = key + '=;' + 'expires=Thu, 01 Jan 1970 00:00:00 GMT';
			}
			delete storage[key];
		}

	};

	renew();

	return function store (key, data, life) {
		
		/**
		*	Add some data to the storage under a key with an optional lifetime,
		*	defaults to 1 hour
		*
		*	@param DOMString key - The key used to access the data
		*	@param Mixed data - String or Number data that should be stored
		*	@param Number life - Time in seconds the data should last
		*/
		
		var type = (typeof(data) === 'object') ? (Array.isArray(data) ? 'array':'object') : typeof(data),
			time = (life || 60 * 60) * 1000,
			d = new Date();

		d = d.getTime() + time;

		if (type == 'object' || type == 'array') {
			throw new Error('Your browser only supports cookies; You cannot set variables into storage of the types Array and Object');
		}

		if (canSessionStore) {
			window.sessionStorage.setItem(key, storageblock(data, d));
		} else {

			if ((key + '=' + storageblock(data, d)).length > 4090) {
				throw new Error('Your browser only supports cookies; Keep the key-value pairs shorter than 4090 bytes for full browser support');
			}

			document.cookie = key + '=' + storageblock(data, d);

		}

		renew();

	};

}());
