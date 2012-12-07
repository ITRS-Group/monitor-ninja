<?php

class LSFilterPP_Core extends LSFilterPreprocessor_Core {
	public function preprocess_string( $value ) {
		return stripslashes( substr( $value, 1, -1 ) );
	}
	public function preprocess_integer($value) {
		return intval($value);
	}
}