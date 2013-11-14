<?php defined('SYSPATH') OR die('No direct access allowed.');

	/**
	 * Ninja notification facility PHP interface
	 */
	class Notify {

		private static $settings = array(
			/* Can be info, warning, critical. Will grant the
				notification-body the classes: jq-notify-type-<type> */
			"type" => "info",

			/* true/false, A sticky notification does not fade out and
				must be removed manually */
			"sticky" => false,

			/* true/false, Should the notification have a remove button
				in the upper right corner */
			"removable" => true,

			/* Milliseconds before a non-sticky notification fades out,
				set to string "auto" and it will fade out depending on the
				length of the message. */
			"fadetime" => "auto",

			/* Should the notification be configurable, i.e. should
				it be possible to remove this type of notification for
				the "duration of the session"/"this user" */
			"configurable" => false,

			/* Buttons can be added to the notification, set the buttons
				property of the options to an object where each key will
				be used as title and the value used as the callback */
			"buttons" => false,

			/**/
			"signature" => false
		);

		private static $notifications = array();

		/**
		 *  Creates a new notification in the User-interface with the
		 *  provided message and options.
		 */
		public function __construct ( $message, $options = false ) {

			$this->message = $message;
			$this->options = self::$settings;

			if ( $options != false && gettype( $options ) == "array" ) {
				foreach ( $this->options as $key => $val ) {
					if ( isset( $options[ $key ] ) ) {
						$this->options[ $key ] = $options[ $key ];
					}
				}
			}

			self::$notifications[] = $this;

		}

		/**
		 *  Renders all notifications provided with PHP to the jquery
		 *  notification facility
		 */
		public static function render () {

			echo '<script>$(window).load( function () {';
			foreach ( self::$notifications as $notification ) {
				echo '$.notify( \'' . $notification->message . '\', ' . json_encode( $notification->options ) . ' );';
			}
			echo '});</script>';

		}

	}