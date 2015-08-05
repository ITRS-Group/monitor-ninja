<?php
require_once ('op5/ninja_sdk/php_miner.php');
class php_miner_Test extends PHPUnit_Framework_TestCase {
	public function test_class_function() {
		$content = <<<'EOF'
<?php
class boll {
	protected $somevar = 13;
	/**
	 * docstuff
	 */
	public function stuff() {}
	/**
	 * doctjipp
	 */
	protected abstract function tjipp();
	private function secretstuff() {}
}
EOF;
		$miner = php_miner_file::parse_content( $content );
		$this->assertInstanceOf( 'php_miner_file', $miner );

		$classes = $miner->extract( 'php_miner_statement_class' );
		/* @var $classes php_miner_statement_class[] */
		$this->assertCount( 1, $classes );
		$this->assertInstanceOf( 'php_miner_statement_class', $classes[0] );
		$this->assertEquals( "boll", $classes[0]->name );
		$this->assertFalse( $classes[0]->is_abstract );

		/* Mine for functions in top level, none available */

		$functions = $miner->extract( 'php_miner_statement_function' );
		/* @var $classes php_miner_statement_function[] */
		$this->assertCount( 0, $functions );

		/* Mine for functions in the class, one should be available */

		$functions = $classes[0]->extract( 'php_miner_statement_function' );
		/* @var $classes php_miner_statement_function[] */
		$this->assertCount( 3, $functions );

		$this->assertInstanceOf( 'php_miner_statement_function', $functions[0] );
		$this->assertEquals( "stuff", $functions[0]->name );
		$this->assertFalse( $functions[0]->is_abstract );
		$this->assertEquals( "public", $functions[0]->scope );

		$this->assertInstanceOf( 'php_miner_statement_function', $functions[1] );
		$this->assertEquals( "tjipp", $functions[1]->name );
		$this->assertTrue( $functions[1]->is_abstract );
		$this->assertEquals( "protected", $functions[1]->scope );

		$this->assertInstanceOf( 'php_miner_statement_function', $functions[2] );
		$this->assertEquals( "secretstuff", $functions[2]->name );
		$this->assertFalse( $functions[2]->is_abstract );
		$this->assertEquals( "private", $functions[2]->scope );
	}
	public function test_recursion() {
		$content = <<<'EOF'
<?php
function something() {
	function stuff() {
	}
}

if(always) {
	function sometimes() {
	}
}
EOF;
		$miner = php_miner_file::parse_content( $content );
		$this->assertInstanceOf( 'php_miner_file', $miner );

		/* Mine root level, should only match one */

		$fncsa = $miner->extract( 'php_miner_statement_function' );
		/* @var $classes php_miner_statement_function[] */
		$this->assertCount( 1, $fncsa );

		$this->assertInstanceOf( 'php_miner_statement_function', $fncsa[0] );
		$this->assertEquals( "something", $fncsa[0]->name );
		$this->assertFalse( $fncsa[0]->is_abstract );
		$this->assertEquals( "public", $fncsa[0]->scope );

		/* Mine next level, should only match one */

		$fncsb = $fncsa[0]->extract( 'php_miner_statement_function' );
		/* @var $classes php_miner_statement_function[] */
		$this->assertCount( 1, $fncsb );

		$this->assertInstanceOf( 'php_miner_statement_function', $fncsb[0] );
		$this->assertEquals( "stuff", $fncsb[0]->name );
		$this->assertFalse( $fncsb[0]->is_abstract );
		$this->assertEquals( "public", $fncsb[0]->scope );

		/* Mine root level recursive, should match all three */

		$fncsa = $miner->extract( 'php_miner_statement_function', true );
		/* @var $classes php_miner_statement_function[] */
		$this->assertCount( 3, $fncsa );

		$this->assertInstanceOf( 'php_miner_statement_function', $fncsa[0] );
		$this->assertEquals( "something", $fncsa[0]->name );
		$this->assertFalse( $fncsa[0]->is_abstract );
		$this->assertEquals( "public", $fncsa[0]->scope );

		$this->assertInstanceOf( 'php_miner_statement_function', $fncsa[1] );
		$this->assertEquals( "stuff", $fncsa[1]->name );
		$this->assertFalse( $fncsa[1]->is_abstract );
		$this->assertEquals( "public", $fncsa[1]->scope );

		$this->assertInstanceOf( 'php_miner_statement_function', $fncsa[2] );
		$this->assertEquals( "sometimes", $fncsa[2]->name );
		$this->assertFalse( $fncsa[2]->is_abstract );
		$this->assertEquals( "public", $fncsa[2]->scope );
	}
	public function test_docstring() {
		$content = <<<'EOF'
<?php
/**
 * boll class things
 */
/* An ignored comment */
class boll {
/* An ignored comment */
/**
 * function stuff
 */
	function stuff() {
/* An ignored comment */
	}
/* An ignored comment */


/* An ignored comment */
/**
 * variable stuff
 */
	public $boll = true;
	/* An ignored comment */


/* An ignored comment */
/**
 * comment for things
 */
	function things() {
/* An ignored comment */
	}
/* An ignored comment */


// Missing docstring
	function nothing() {
/* An ignored comment */
	}
}

EOF;
		$miner = php_miner_file::parse_content( $content );
		$this->assertInstanceOf( 'php_miner_file', $miner );

		$classes = $miner->extract( 'php_miner_statement_class', false );
		/* @var $classes php_miner_statement_class[] */
		$this->assertCount( 1, $classes );

		$this->assertInstanceOf( 'php_miner_statement_class', $classes[0] );
		$this->assertEquals( 'boll', $classes[0]->name );
		$this->assertRegexp( '/boll class things/', $classes[0]->docstring );

		$classes = $miner->extract( 'php_miner_statement_class', true );
		/* @var $classes php_miner_statement_class[] */
		$this->assertCount( 1, $classes );

		$this->assertInstanceOf( 'php_miner_statement_class', $classes[0] );
		$this->assertEquals( 'boll', $classes[0]->name );
		$this->assertRegexp( '/boll class things/', $classes[0]->docstring );

		$funcs = $classes[0]->extract( 'php_miner_statement_function', false );
		/* @var $funcs php_miner_statement_class[] */
		$this->assertCount( 3, $funcs );

		$this->assertInstanceOf( 'php_miner_statement_function', $funcs[0] );
		$this->assertEquals( 'stuff', $funcs[0]->name );
		$this->assertRegexp( '/function stuff/', $funcs[0]->docstring );

		$this->assertInstanceOf( 'php_miner_statement_function', $funcs[1] );
		$this->assertEquals( 'things', $funcs[1]->name );
		$this->assertRegexp( '/comment for things/', $funcs[1]->docstring );

		$this->assertInstanceOf( 'php_miner_statement_function', $funcs[2] );
		$this->assertEquals( 'nothing', $funcs[2]->name );
		$this->assertFalse( $funcs[2]->docstring );

		$funcs = $miner->extract( 'php_miner_statement_function', true );
		/* @var $funcs php_miner_statement_class[] */
		$this->assertCount( 3, $funcs );

		$this->assertInstanceOf( 'php_miner_statement_function', $funcs[0] );
		$this->assertEquals( 'stuff', $funcs[0]->name );
		$this->assertRegexp( '/function stuff/', $funcs[0]->docstring );

		$this->assertInstanceOf( 'php_miner_statement_function', $funcs[1] );
		$this->assertEquals( 'things', $funcs[1]->name );
		$this->assertRegexp( '/comment for things/', $funcs[1]->docstring );

		$this->assertInstanceOf( 'php_miner_statement_function', $funcs[2] );
		$this->assertEquals( 'nothing', $funcs[2]->name );
		$this->assertFalse( $funcs[2]->docstring );
	}
	public function test_docstring_tags() {
		$content = <<<'EOF'
<?php
/**
 * boll class things
 *
 * @ninja stuff boll
 * @ninja boll hej
 * @somethingelse kakaka
 * @something something @otherthing
 */
class boll {
}

EOF;
		$miner = php_miner_file::parse_content( $content );
		$this->assertInstanceOf( 'php_miner_file', $miner );

		$classes = $miner->extract( 'php_miner_statement_class', false );
		/* @var $classes php_miner_statement_class[] */
		$this->assertCount( 1, $classes );

		$this->assertEquals( array (
			'ninja stuff boll',
			'ninja boll hej',
			'somethingelse kakaka',
			'something something @otherthing'
		), $classes[0]->get_docstring_tags() );
	}
	public function test_docstring_multiline_tags() {
		$content = <<<'EOF'
<?php
/**
 * boll class things
 *
 * @multi line
 *   new line ending
 *
 * not in tag
 * @multi line tag
 * ends with new tag
 * @singe line
 */
class boll {
}

EOF;
		$miner = php_miner_file::parse_content( $content );
		$this->assertInstanceOf( 'php_miner_file', $miner );

		$classes = $miner->extract( 'php_miner_statement_class', false );
		/* @var $classes php_miner_statement_class[] */
		$this->assertCount( 1, $classes );

		$this->assertEquals( array (
			"multi line\nnew line ending",
			"multi line tag\nends with new tag",
			"singe line"
		), $classes[0]->get_docstring_tags() );
	}
}