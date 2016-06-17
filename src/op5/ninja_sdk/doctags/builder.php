<?php
require_once (__DIR__ . "/../generator_lib.php");
require_once (__DIR__ . "/../class_generator.php");
require_once (__DIR__ . "/../php_miner.php");
require_once (__DIR__ . "/../builder_interface.php");

class doctags_manifest_Builder extends class_generator {
	protected $content = array ();
	public function __construct($filename, $content) {
		$this->content = $content;
		$this->classname = $filename;
		$this->set_manifest();
	}
	public function generate($skip_generated_note = false) {
		parent::generate( $skip_generated_note );

		foreach ( $this->content as $name => $value ) {
			$this->write( '$manifest[%s] = %s;', $name, $value );
		}
	}
}
class doctags_Builder implements builder_interface {
	const INCLUDES_FILENAME = "includes.txt";
	const DOCTAG_PREFIX = "ninja";
	const MANIFEST_SUFFIX = "_doctags";
	public function generate($moduledir, $confdir) {
		/* Fetch name of the includes.txt file */
		$includes_file = $confdir . DIRECTORY_SEPARATOR . self::INCLUDES_FILENAME;
		$files = $this->get_file_list( $includes_file, $moduledir );
		$tags = array ();
		foreach ( $files as $file ) {
			printf( "DocTags parsing: %s\n", $file );
			$tags = array_merge( $tags, $this->get_tags_for_file( $file ) );
		}
		$manifests = $this->process_taglist_to_manifests( $tags, self::DOCTAG_PREFIX );

		foreach ( $manifests as $manifest => $content ) {
			$this->generate_manifest( $manifest, $content, $moduledir );
		}
	}
	private function get_file_list($includes_file, $moduledir) {
		$patterns = explode( "\n", file_get_contents( $includes_file ) );
		$files = array ();
		foreach ( array_reverse( $patterns ) as $pattern ) {
			if($pattern == '')
				continue;
			$include = true;
			if (substr( $pattern, 0, 1 ) == "!") {
				$pattern = substr( $pattern, 1 );
				$include = false;
			}

			$matching_files = glob( "$moduledir/$pattern" );
			foreach ( $matching_files as $matching_file ) {
				$files[$matching_file] = $include;
			}
		}
		return array_keys( array_filter( $files ) );
	}
	private function get_tags_for_file($filename) {
		$file = php_miner_file::parse_file( $filename );
		if ($file === false) {
			return false;
		}

		$tags = array ();
		$classes = $file->extract( 'php_miner_statement_class', true );
		/* @var $classes php_miner_statement_class[] */
		foreach ( $classes as $class ) {
			$functions = $file->extract( 'php_miner_statement_function', true );
			/* @var $functions php_miner_statement_function[] */
			foreach ( $functions as $function ) {
				$doctags = $function->get_docstring_tags();
				foreach ( $doctags as $doctag ) {
					$tags[] = array (
						$class->name,
						$function->name,
						$doctag
					);
				}
			}
		}
		return $tags;
	}
	private function process_taglist_to_manifests($tags, $prefix) {
		$manifests = array ();
		$prefix .= " ";
		foreach ( $tags as $tag ) {
			list ( $class, $function, $tagline ) = $tag;
			$tagline = str_replace("\n", " ", $tagline);
			if (substr( $tagline, 0, strlen( $prefix ) ) == $prefix) {
				$tagline = trim( substr( $tagline, strlen( $prefix ) ) );
				$tagparts = explode( " ", $tagline, 3 );
				if (count( $tagparts ) == 3) {

					$value = $tagparts[2];
					$key = $tagparts[1];
					$manifest = $tagparts[0];
					/* If key is a list, thus append */
					if (substr( $key, - 2 ) == '[]') {
						$key = substr( $key, 0, - 2 );
						$value = array (
							$value
						); // array_merge_recursive appends in
						                        // this case
					}

					$key_val = array();

					$key_obj =& $key_val;
					foreach(explode('.', $key) as $keypart) {
						$key_obj[$keypart] = array();
						$key_obj =& $key_obj[$keypart];
					}
					$key_obj = $value;

					$manifests = array_merge_recursive( $manifests, array (
						$manifest => array (
							strtolower($class) => array (
								strtolower($function) => $key_val
							)
						)
					) );
				}
			}
		}
		return $manifests;
	}
	private function generate_manifest($manifest, $content, $moduledir) {
		$generator = new doctags_manifest_Builder( $manifest . self::MANIFEST_SUFFIX, $content );
		$generator->set_moduledir( $moduledir );
		$generator->generate();
	}

	public function get_dependencies() {
		return array();
	}
}