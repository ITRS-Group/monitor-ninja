<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<style>
	.big-number {
		display: block;
		width: 100%;
		text-align: center;
		color: #fff;
	}

	.big-number h1 {
		margin: 0;
		padding: 24px 0 0 0;
		font-size: 22pt;
	}

	.big-number p {
		margin: 0;
		padding: 0 0 24px 0;
		width: 100%;
		font-size: 12pt;
	}

	.big-number p span {
		font-size: 10pt;
	}

	.big-number.ok {background: #8c0;}
	.big-number.warning {background: #fd0; color: #751;}
	.big-number.critical {background: #c00;}
	.big-number.pending {background: #d0d0d0;}
</style>
<a target="_blank" href="<?php echo listview::querylink($query); ?>">
	<div class="big-number <?php echo $state; ?>">
		<h1 class="big-number-number"><?php echo $number; ?><?php echo $uom; ?></h1>
		<p>
			<?php
			echo $type;
			if ($description) {
				echo "<br><span>$description</span>";
			}
			?>
		</p>
	</div>
</a>
