<?php defined('SYSPATH') OR die('No direct access allowed.');

	echo '<div id="rotation-frame"></div>';

	?>

<div id="page-rotation-title">
	NiRV experimental
</div>

<div id="page-rotation-initial">
	<h1>Welcome to the Rotational View</h1>
	<p>This view enters a "fullscreen" mode once you press play, it will hide the entire monitor GUI, 
		to show it again, just move your mouse and press the pause button.</p>

	<ul>
		<li><strong>Add new views</strong> to the rotation by entering their full URL in the bottom right box and press the add (+) button.</li>
		<li><strong>Start</strong> rotating through the views you've added by pressing the play button in the middle box.</li>
		<li><strong>Save</strong> your rotation pool by pressing the save button in the bottom right box. </li>
		<li><strong>Remove views</strong> by unchecking them in bottom right box and pressing the save button.</li>
	</ul>
</div>

<div id="page-rotation-views">
	
	These are the views you will rotate through. Add new views by entering their URI in this box and press the add (+) button.<br /><br />

	<form id="page-rotation-fields">
		
		<input type="text" id="page-rotation-new" /><input type="button" value="+" id="page-rotation-add" />

		<ul id="page-rotation-fields-list">
		</ul>

		<input type="button" value="Save" id="page-rotation-save" />

	</form>

</div>

<div id="page-rotation-note">
	
</div>

<div id="page-rotation-opts">
	<span id="page-rotation-prev">&laquo; Previous</span>
	<span id="page-rotation-pause">PAUSE</span>
	<span id="page-rotation-play">PLAY</span>
	<span id="page-rotation-next">Next &raquo;</span>

	<span id="page-rotation-faster">-</span>
	<input type="text" id="page-rotation-speed" disabled="true" size="3" value="10" />
	<span id="page-rotation-slower">+</span><br />

	<div id="page-rotation-goto">Some actions cannot be taken from rotational view, click here (or press space) to go to the real view.</div>
</div>
