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
		$command_list = $object->list_commands();
		if(!array_key_exists($command, $command_list))
			return null;
		if(!array_key_exists('mayi_method', $command_list[$command]))
			return null;
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


	/**
	 * Returns the HTML for a button which can send commands through ajax
	 * instead of page reloads!
	 *
	 * TODO Handling of command parameters
	 */
	static function command_ajax_button ( $command, $lable, $params = false, $state = false ) {

		$user = Auth::instance()->get_user();

		if ($user->authorized_for('system_commands')) {
			if ( $params != false && is_array( $params ) ) {
				$params = json_encode( $params );
				return '<button class="command-button" ' . (( $state !== false ) ? ('data-state="' . $state . '"') : "") . ' data-parameters="' . $params . '" data-command="' . $command . '"><span></span>' . $lable . '</button>';
			}
			return '<button class="command-button" ' . (( $state !== false ) ? ('data-state="' . $state . '"') : "") . ' data-command="' . $command . '"><span></span>' . $lable . '</button>';
		} else {
			return '<button class="command-button" ' . (( $state !== false ) ? ('data-state="' . $state . '"') : "") . ' disabled="disabled" title="' . _('You are not authorized for system commands.') . '"><span></span>' . $lable . '</button>';
		}

	}
}
