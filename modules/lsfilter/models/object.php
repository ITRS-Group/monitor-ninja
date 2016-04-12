<?php
require_once (dirname( __FILE__ ) . '/base/baseobject.php');

/**
 * Describes a single object from livestatus
 */
abstract class Object_Model extends BaseObject_Model {
	/**
	 * Mine out rewrite columns from doctags
	 *
	 * TODO: Don't do string magic in runtime... That's slow
	 * However... in this case, it's not that often...
	 */
	static public function rewrite_columns() {
		$orm_doctags = Module_Manifest_Model::get('orm_doctags');
		$classname = strtolower(get_called_class());
		if(!isset($orm_doctags[$classname]))
			return array();

		$rewrite_columns = array();
		foreach($orm_doctags[$classname] as $field => $info) {
			if(!isset($info['depend']))
				continue;
			if(substr($field,0,4) != 'get_')
				continue;
			$field = substr($field,4);
			$rewrite_columns[$field] = $info['depend'];
		}
		return $rewrite_columns;
	}

	/**
	 * Get a list of custom variables related to the object, if possible
	 *
	 * @return array
	 */
	public function get_custom_variables() {
		return array ();
	}

	/**
	 * Get mayi resource for the current object
	 *
	 * This is a wrapper to get the resource from the set
	 */
	public function mayi_resource() {
		$pool = ObjectPool_Model::pool(static::get_table());
		return $pool->all()->mayi_resource();
	}

	/**
	 * Get a list of commands related to the object
	 * This digs out the information from orm_command_doctags, which is
	 * generated from the @ninja orm_command tags in corresponding classes.
	 *
	 * This doesn't set if the command is enabled or not, but just the enable
	 * criteria. Thus enable is depednent of the current object
	 *
	 * @param $auth_filtered bool
	 *        	true if filtered by permission, false otherwise
	 * @return array
	 */
	public static function list_commands_static($auth_filtered = true) {
		$orm_command = Module_Manifest_Model::get( 'orm_command_doctags' );
		$classname = strtolower( get_called_class() );
		if (! array_key_exists( $classname, $orm_command ))
			return array ();

		$commands = $orm_command[$classname];

		if ($auth_filtered) {
			$mayi = op5MayI::instance();
			$temp_obj = new static();
			$mayi_resource = $temp_obj->mayi_resource();
			unset($temp_obj);
			$commands = array_filter( $commands, function ($command) use($mayi, $mayi_resource) {
				return $mayi->run( $mayi_resource . ':' . $command['mayi_method'] );
			} );
		}

		/*
		 * Fill in default values, and make sure all parameters exist in the
		 * command
		 */
		$outcommands = array();
		foreach ( $commands as $cmdname => $cmdinfo ) {
			$outcommands[$cmdname] = array_merge( array (
				'name' => $cmdname,
				'category' => 'Commands',
				'icon' => 'command',
				'description' => '',
				'params' => array (),
				'mayi_method' => 'invalid',
				'enabled_if' => false,
				'view' => 'cmd/exec',
				'redirect' => 0
			), $cmdinfo );
		}

		return $outcommands;
	}

	/**
	 * Get a list of commands related to the object
	 * This digs out the information from orm_command_doctags, which is
	 * generated from the @ninja orm_command tags in corresponding classes
	 *
	 * @param $auth_filtered bool
	 *        	true if filtered by permission, false otherwise
	 * @return array
	 */
	public function list_commands($auth_filtered = true) {
		$self = $this;
		return array_map( function ($cmd) use($self) {
			if ($cmd['enabled_if'] === false) {
				$cmd['enabled'] = true;
			} else {

				$field = $cmd['enabled_if'];
				$negate = substr( $field, 0, 1 ) == '!';
				if ($negate)
					$field = substr( $field, 1 );


				$cmd['enabled'] = (bool)call_user_func( array (
					$self,
					'get_' . $field
				) );

				if ($negate)
					$cmd['enabled'] = ! $cmd['enabled'];
			}
			return $cmd;
		}, static::list_commands_static( $auth_filtered ) );
	}

	/**
	 * Get a readable name of the current object. As fallback, which is valid
	 * in many cases, is the key string. But for tables with ID-keys, it might
	 * be nice to have a better string representation.
	 *
	 * @return string
	 */
	public function get_readable_name() {
		return $this->get_key();
	}

	/**
	 * Get the current logged in username
	 *
	 * @return string
	 */
	public function get_current_user() {
		return Auth::instance()->get_user()->get_username();
	}
}
