<?php

class ExpParser_SearchFilter_Core extends ExpParser_Core {
	protected $objects = array(
			'h'  => 'hosts',
			's'  => 'services',
			'c'  => 'comments',
			'si' => 'statuss',
			'hg' => 'hostgroups',
			'sg' => 'servicegroups'
			);
	
	/* Expression entry point */
	protected function run() {
		$filters = array();
		
		do {
			list( $object, $criteria ) = $this->criteria();
			if( !isset( $filters[$object] ) )
				$filters[$object] = array();
			$filters[$object][] = $criteria;
		} while( $this->acceptKeyword(array('and') ) );
		
		$params = array(
				'filters' => $filters
				);
		while( false!==($arg=$this->acceptKeyword( array('limit') )) ) {
			$this->expectSym(array('='));
			$value = $this->expectNum();
			$params[$arg] = $value;
		}
		
		return $params;
	}
	
	protected function criteria() {
		$object = $this->expectKeyword(
				array_merge(
						array_keys($this->objects),
						array_values($this->objects)
						)
				);
		
		/* If short form is used, expand to long form */
		if( array_key_exists($object, $this->objects) )
			$object = $this->objects[$object];
		
		$this->expectSym( array(':') );
		
		$args = array();
		
		do {
			$args[] = $this->expectUnquotedUntil( array( 'and', 'or', 'limit', false ) );
		} while( $this->acceptKeyword(array('or') ) );
		
		return array( $object, $args );
	}
	
	
	/* Custom string acceptor */

	protected function acceptUnquotedUntil( $keywordlist = false ) {
		/* Peek at next keyword */
		$minpos = false;
		
		foreach( $keywordlist as $keyword ) {
			if( $keyword === false ) {
				$pos = strlen($this->expr);
			}
			else {
				$pos = false;
				if( preg_match( ':[^a-z0-9]('.$keyword.')[^a-z0-9]:', $this->expr, $matches, PREG_OFFSET_CAPTURE, $this->ptr) ) {
					$pos=$matches[1][1]; /* Second match (the keyword), second index (=position, not match) */
				}
			}
			
			if( $pos !== false ) {
				if( $minpos === false )
					$minpos = $pos;
				else
					$minpos = min( $pos, $minpos );
			}
		}

		if( $minpos !== false ) {
			$outp = substr( $this->expr, $this->ptr, $minpos-$this->ptr );
			$this->ptr = $minpos;
			return $outp;
		}
		return false;
	}
	
	protected function expectUnquotedUntil( $keywordlist = false ) {
		$sym = $this->acceptUnquotedUntil( $keywordlist );
		if( $sym === false )
			$this->error('Unexpected token, expected '.(($keywordlist===false)?('keyword'):implode(',',$keywordlist)));
		return $sym;
	}
}