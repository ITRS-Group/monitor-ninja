<?php
require_once(__DIR__."/../generator_lib.php");
require_once(__DIR__."/LalrGrammarParser.php");
require_once(__DIR__."/LalrGenerator.php");

class parsegen_Builder implements builder_interface {
	public function generate($moduledir, $confdir) {
		print "Generating parser from $confdir to $moduledir\n";
		foreach(scandir($confdir) as $conffile) {
			if($conffile[0] == '.')
				continue;
			printf("Generting grammar %s\n", $conffile);
			$grammar_file = file_get_contents("$confdir/$conffile");
			$grammar_parser = new LalrGrammarParser();
			$grammar = $grammar_parser->parse($grammar_file);
			printf("Into class %s\n", $grammar['class']);

			$generator = new LalrGenerator($grammar);
			$generator->generate($moduledir);
		}
	}

	public function get_dependencies() {
		return array();
	}
}