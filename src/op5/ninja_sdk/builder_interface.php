<?php

interface builder_interface {
	public function generate($moduledir, $confdir);
	public function get_dependencies();
}