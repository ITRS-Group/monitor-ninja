<?php

/**
 * Preprocessor for lsfilter, resolves strings and integers
 */
class LSFilterPP_Core extends LSFilterPreprocessor_Core {
	/**
	 * Remove quotations of a string
	 */
	public function preprocess_string( $value ) {
		return stripslashes( substr( $value, 1, -1 ) );
	}
	
	/**
	 * Convert a integer token to integer
	 */
	public function preprocess_integer($value) {
		return intval($value);
	}
}