<?php

$number_of_views = count($views);
for($i = 0; $i < $number_of_views; $i++) {
	$views[$i]->render(true);
	if($i != $number_of_views) {
		echo "<hr>\n";
	}
}
