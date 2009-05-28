<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Digg pagination style
 *
 * @preview  « Previous  1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26  Next »
 */

$label_previous = $this->translate->_('previous');
$label_next = $this->translate->_('next');
?>

<p class="pagination">

	<?php if ($previous_page): ?>
		<a href="<?php echo str_replace('{page}', $previous_page, $url) ?>">&laquo;&nbsp;<?php echo $label_previous ?></a>
	<?php else: ?>
		&laquo;&nbsp;<?php echo $label_previous ?>
	<?php endif ?>


	<?php if ($total_pages < 13): /* « Previous  1 2 3 4 5 6 7 8 9 10 11 12  Next » */ ?>

		<?php for ($i = 1; $i <= $total_pages; $i++): ?>
			<?php if ($i == $current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

	<?php elseif ($current_page < 9): /* « Previous  1 2 3 4 5 6 7 8 9 10 … 25 26  Next » */ ?>

		<?php for ($i = 1; $i <= 10; $i++): ?>
			<?php if ($i == $current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

		&hellip;
		<a href="<?php echo str_replace('{page}', $total_pages - 1, $url) ?>"><?php echo $total_pages - 1 ?></a>
		<a href="<?php echo str_replace('{page}', $total_pages, $url) ?>"><?php echo $total_pages ?></a>

	<?php elseif ($current_page > $total_pages - 8): /* « Previous  1 2 … 17 18 19 20 21 22 23 24 25 26  Next » */ ?>

		<a href="<?php echo str_replace('{page}', 1, $url) ?>">1</a>
		<a href="<?php echo str_replace('{page}', 2, $url) ?>">2</a>
		&hellip;

		<?php for ($i = $total_pages - 9; $i <= $total_pages; $i++): ?>
			<?php if ($i == $current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

	<?php else: /* « Previous  1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26  Next » */ ?>

		<a href="<?php echo str_replace('{page}', 1, $url) ?>">1</a>
		<a href="<?php echo str_replace('{page}', 2, $url) ?>">2</a>
		&hellip;

		<?php for ($i = $current_page - 5; $i <= $current_page + 5; $i++): ?>
			<?php if ($i == $current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

		&hellip;
		<a href="<?php echo str_replace('{page}', $total_pages - 1, $url) ?>"><?php echo $total_pages - 1 ?></a>
		<a href="<?php echo str_replace('{page}', $total_pages, $url) ?>"><?php echo $total_pages ?></a>

	<?php endif ?>


	<?php if ($next_page): ?>
		<a href="<?php echo str_replace('{page}', $next_page, $url) ?>"><?php echo $label_next ?>&nbsp;&raquo;</a>
	<?php else: ?>
		<?php echo $label_next ?>&nbsp;&raquo;
	<?php endif ?>
	<?php echo '&nbsp; (' . $this->translate->_('total') . ': ' . $total_items . ' ' . $this->translate->_('entries') . ')' ?>

	<?php
	$paging_step = 100; # step used below to print nr of items per page
	$entries = $this->translate->_('entries');
	if (!isset($items_per_page)) {
		$items_per_page = Kohana::config('pagination.items_per_page');
	}
	?>
	<span class="pagination_entries_str" style="display:none"><?php echo $entries ?></span>
	<form class="pagination_form" style="float:left" action="<?php echo basename($_SERVER['PHP_SELF']) ?>" method="get"><?php echo $this->translate->_('Show') ?> :
		<select class="items_per_page" name="items_per_page" onchange="this.form.submit()">
	<?php
		if ($total_items < $paging_step) {
			?>
			<option value="<?php echo $total_items ?>" selected="selected"><?php echo $total_items ?> <?php echo $entries ?></option>
			<?php
		} else {
			?>
			<option value="<?php echo $total_items ?>"<?php if ($items_per_page == $total_items) { ?> selected='selected'<?php } ?>><?php echo $this->translate->_('All').' '.$entries ?>
			<?php
		}
		for ($i=$paging_step ; $i<$total_items; $i+=$paging_step ) {
			?><option value="<?php echo $i ?>"<?php if ($items_per_page == $i) { ?> selected='selected'<?php } ?>><?php echo $i ?> <?php echo $entries ?></option><?php
		}
	?>
		</select>
		&nbsp;
			<input
				type="text" size="4" name="custom_pagination_field" class="custom_pagination_field"
				title="<?php echo $this->translate->_('Enter number of items to show on each page or select from the drop-down on the left') ?>"
				value="<?php echo $total_items < $items_per_page ? $total_items : $items_per_page ?>" />
			<input type="button" name="show_pagination" class="show_pagination" value="<?php echo $this->translate->_('go') ?>" />
	</form>
</p>