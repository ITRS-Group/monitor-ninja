<?php

class Logger {
  static $instance = null;

  /* protected */ function __construct() {
  }

  /* public */ function log($message) {
    error_log($message);
  }

  /* static */ function get_instance() {
    if (is_null(Logger::$instance)) {
      Logger::$instance = new Logger();
    };

    return Logger::$instance;
  }

  /* static */ function set_instance($instance) {
    Logger::$instance = $instance;
  }
}

?>