<?php

class LalrState {
	private $items; /* kernel */
	private $grammar;

	public function __construct( $items, $grammar ) {
		if( !is_array( $items ) )
			$items = array( $items );

		$this->items = $items;
		$this->grammar = $grammar;
	}

	public function next_symbols() {
		$symbols = array();
		foreach( $this->closure() as $item ) {
			$next = $item->next();
			if( $next !== false && !in_array( $next, $symbols ) ) {
				$symbols[] = $next;
			}
		}
		return $symbols;
	}

	public function take( $symbol ) {
		$next_items = array();
		foreach( $this->closure() as $item ) {
			$next_item = $item->take( $symbol );
			if( $next_item !== false ) {
				$next_items[] = $next_item;
			}
		}
		return new self( $next_items, $this->grammar );
	}

	public function closure() {
		$items = $this->items;

		/* Don't use foreach. $items can grow in the loop, and the new items needs to be handled too */
		for( $i = 0; $i<count($items); $i++ ) {
			$cur_item = $items[$i];
			$next_symbol = $cur_item->next();
			if( $next_symbol === false ) {

			} else {
				foreach( $this->grammar->productions( $next_symbol ) as $new_item ) {
					$add = true;
					foreach( $items as $test_item ) {
						if( $test_item->equals($new_item) ) {
							$add = false;
						}
					}
					if( $add ) {
						$items[] = $new_item;
					}
				}
			}
		}
		return $items;
	}

	public function errors() {
		$items = $this->items;

		$errors = array();
		/* Don't use foreach. $items can grow in the loop, and the new items needs to be handled too */
		for( $i = 0; $i<count($items); $i++ ) {
			$cur_item = $items[$i];
			$next_symbol = $cur_item->next();
			$rule = $this->grammar->errors($next_symbol);
			if($rule) {
				foreach( $rule->follow() as $follow ) {
					$errors[$follow] = $rule;
				}
			}
		}
		return $errors;
	}

	public function __toString() {
		$outp = "";
		foreach( $this->closure() as $item ) {
			$outp .= "$item\n";
		}
		return $outp;
	}

	public function equals( $state ) {
		if( count( $this->items ) != count( $state->items ) ) {
			return false;
		}
		foreach( $this->items as $itema ) {
			$exists = false;
			foreach( $state->items as $itemb ) {
				if( $itema->equals( $itemb ) ) {
					$exists = true;
				}
			}
			if( $exists == false )
				return false;
		}
		return true;
	}
}