<?php

require_once(__DIR__."/../common/ORMObjectSetGenerator.php");

class ORMLSObjectSetGenerator extends ORMObjectSetGenerator {

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
	}

	public function generate_backend_specific_functions() {
	}
}
