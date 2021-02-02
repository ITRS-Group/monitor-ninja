
var SetIterator = function ( set ) {

	set.reset();

	this.next = function () {

		var next = set.back();
		set.increase();

		if ( next != null ) return next;
		return null;

	}

};

var Set = function ( data ) {

	var iterator = 0;

	if ( typeof( data ) == "number" ) {
		data = new Array( data );
	} else if ( !data || typeof( data ) == "undefined" || data == null ) {
		data = [];
	} else data = data.sort();

	/**
	 * Increases the iterator position by 1
	 * @return {void}
	 */
	this.increase = function () {
		iterator++;
	};

	/**
	 * Returns the Set data array
	 * @return {array}   The Set data array
	 */
	this.data = function () {
		return data;
	}

	/**
	 * Returns the element of the iterators
	 * current position
	 *
	 * @return {mixed}   The current value
	 */
	this.back = function () {

		if ( data[ iterator ] != null ) {
			return data[ iterator ];
		} return null;

	}

	/**
	 * Sets the value of the current iterator
	 * position and increases the iterator.
	 *
	 * @param  {mixed} value   Value to add
	 * @return {void}
	 */
	this.push = function ( value ) {

		data[ iterator ] = value;
		this.increase();

	};

	/**
	 * Resets the iterator position
	 * @return {void}
	 */
	this.reset = function () {

		iterator = 0;

	};

	/**
	 * Slices the Set data, without arguments it
	 * slices from 0 to iterator position.
	 *
	 * This method also resets the iterator position.
	 *
	 * @param  {offset} offset   optional, slices from offset with size iterator
	 * @param  {length} length   optional, slices from offset with size length
	 * @return {void}
	 */
	this.shrink = function ( offset, length ) {

		if ( typeof(offset) == "number" && typeof(length) == "number" ) {
			data = data.slice( offset, length );
		} else if ( typeof( offset ) == "number" ) {
			data = data.slice( offset, iterator );
		} else {
			data = data.slice( 0, iterator );
		}

		this.reset();

	};

	/**
	 * Public getter of the Set length
	 * @return {number} Size   The Set length
	 */
	this.size = function () {

		return data.length;

	};

	/**
	 * Iterates over the Set and searches for
	 * input value, if found the value is returned,
	 * otherwise it returns null.
	 *
	 * @param  {mixed} value   The value to search for
	 * @return {mixed} value | null
	 */
	this.find = function ( value ) {

		var itt = new SetIterator( this ),
			index = null;

		while ( ( index = itt.next() ) != null ) {
			if ( index === value ) {
				return iterator - 1;
			}
		}

		return -1;

	};

	this.empty = function () {
		data = [];
		iterator = 0;
	};

	this.insert = function ( value ) {

		var it = new SetIterator( this ),
			index = null, inserted = false;

		while ( ( index = itt.next() ) != null ) {

			comp = Set.compare( index, value );
			if ( comp == -1 ) {
				data.splice( iterator, 0, value );
				inserted = true;
				break;
			}

		}

		if ( !inserted ) {
			data.push( value );
		}

		iterator = 0;

	};

	this.remove = function ( index ) {

		data.splice( index, 1 );
		iterator = 0;

	};

	/**
	 * Merges two sorted Sets into one sorted Set
	 * of unique values
	 *
	 * @param  {Set} target   The Set to merge this with
	 * @return {Set} merged   The resulting merged set
	 */
	this.union = function ( target ) {

		var merged = new Set( this.length + target.length );

		while ( this.back() != null || target.back() != null ) {

			comp = Set.compare( this.back(), target.back() );

			switch ( comp ) {
				case 0:
					target.increase();
					break;
				case 1:
					merged.push( target.back() );
					target.increase();
					break;
				case -1:
					merged.push( this.back() );
					this.increase();
					break;
			};

		}

		merged.shrink();
		return merged;

	};

	/**
	 * Merges two sorted Sets into one sorted Set
	 * of unique values only where both Sets have the value
	 *
	 * @param  {Set} target   The Set to merge this with
	 * @return {Set} merged   The resulting merged set
	 */
	this.intersect = function ( target ) {

		var intersect = new Set( this.length + target.length );

		while ( this.back() != null || target.back() != null ) {

			comp = Set.compare( this.back(), target.back() );

			switch ( comp ) {
				case 0:
					intersect.push( this.back() );
					target.increase();
					this.increase();
					break;
				case -1:
					this.increase();
					break;
				case 1:
					target.increase();
					break;
			};

		}

		intersect.shrink();
		return intersect;

	};

	/**
	 * Returns a new Set of items that are
	 * in this Set but not in the target Set.
	 *
	 * @param  {Set} target   The Set to diff against
	 * @return {Set}          The diff Set
	 */
	this.diff = function ( target ) {

		var diff = new Set( this.length ),
			comp = null;

		while ( this.back() != null ) {

			comp = Set.compare( this.back(), target.back() );

			switch ( comp ) {
				case 0:
					this.increase();
					break;
				case -1:
					diff.push( this.back() );
					this.increase();
					break;
				case 1:
					target.increase();
					break;
			};

		}

		diff.shrink();
		return diff;

	};

	this.reset();
	return this;

};

Set.compare = function ( v1, v2 ) {
	if ( v1 == v2 ) return 0;
	if ( v2 == null || v1 < v2 ) return -1;
	if ( v1 == null || v1 > v2 ) return 1;
};
