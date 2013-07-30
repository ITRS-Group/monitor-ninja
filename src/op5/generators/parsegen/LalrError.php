<?php

class LalrError {
	private $name;
	private $generate;
	private $follow;

	public function __construct( $name, $generate, $follow ) {
		$this->name = $name;
		$this->generate = $generate;
		$this->follow = $follow;
	}

	public function get_name() {
		return $this->name;
	}

	public function generates() {
		return $this->generate;
	}

	public function produces( $symbol ) {
		return $this->generate == $symbol;
	}

	public function follow() {
		return $this->follow;
	}
	public function follows( $symbol ) {
		return in_array($symbol,$this->follow);
	}

	public function equals( $item ) {
		return ($item->name == $this->name);
	}

	public function __toString() {
		$outp = sprintf( "%s: %s := error(%s)", $this->name, $this->generate, $this->follow );
		return $outp;
	}
}