<?php

/**
 * Nagios FIFO command helper
 */
class cmd
{
	/**
	 * @param $object Object_Model
	 * @param $command string
	 * @param $text string
	 * @return string|null
	 */
	static function cmd_link(Object_Model $object, $command, $text) {
		return html::anchor(
			sprintf(
				"cmd?command=%s&table=%s&object=%s",
				urlencode($command),
				urlencode($object->get_table()),
				urlencode($object->get_key())
			),
			html::specialchars($text)
		);
	}
}
