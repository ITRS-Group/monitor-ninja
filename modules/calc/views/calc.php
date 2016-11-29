<?php defined('SYSPATH') OR die('No direct access allowed.');
$form->get_view()->render(true);
echo '<hr />';

echo 'Placeholder for result of: ' . $expr;

/*
$parser = new Calc(
	new CalcPreprocessor(),
	new CalcCalcVisitor()
	);

try {
	echo "<h1>" . $parser->parse($expr) . "</h1>";
} catch( CalculatorException $e ) {
	echo "<h1>" . $e->getMessage() . "</h1>";
	echo html::specialchars(substr($e->get_query(), 0, $e->get_position()))
		. " &lt;here&gt; "
		. html::specialchars(substr($e->get_query(), $e->get_position()));
}
*/
