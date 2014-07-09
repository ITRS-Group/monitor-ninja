<?php
require_once ('../buildlib.php');

/*
 * Generate parsers
 */

require_once (NINJA_SDK_PATH . '/parsegen/LalrGenerator.php');
require_once (NINJA_SDK_PATH . '/parsegen/LalrGrammarParser.php');
class LSFilter_generator extends generator_module {
	protected function do_run() {
		print "Generating: LSFilter\n";
		$grammar_file = file_get_contents($this->gen_dir . 'lsfilter.txt');
		$grammar_parser = new LalrGrammarParser();
		$grammar = $grammar_parser->parse($grammar_file);

		$generator = new LalrGenerator('LSFilter', $grammar);
		$generator->generate();

		print "Generating: LSColumns\n";
		$grammar_file = file_get_contents($this->gen_dir . 'lscolumns.txt');
		$grammar_parser = new LalrGrammarParser();
		$grammar = $grammar_parser->parse($grammar_file);

		$generator = new LalrGenerator('LSColumns', $grammar);
		$generator->generate();
	}
}

$generator = new LSFilter_generator('lsfilter');
$result = $generator->run();

if ($result != 0)
	exit($result);

/*
 * Generate base ORM classes
 */

require_once (NINJA_SDK_PATH . '/orm/ORMBuilder.php');
class orm_generator extends generator_module {
	protected function do_run() {
		require ('structure.php'); /* Sets $tables */

		$builder = new ORMBuilder();

		$builder->generate_base();

		foreach ($tables as $name => $structure) {
			$builder->generate_table($name, $tables);
		}

		$builder->generate_manifest($tables);
	}
}

$generator = new orm_generator('lsfilter');
$result = $generator->run();

if ($result != 0)
	exit($result);

exit(0);
