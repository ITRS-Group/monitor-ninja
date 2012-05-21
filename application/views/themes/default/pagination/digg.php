<?php defined('SYSPATH') OR die('No direct access allowed.');
	if (!isset($_SESSION['_pagination_id_'])) {
		$_SESSION['_pagination_id_'] = 1;
	} else {
		switch ($_SESSION['_pagination_id_']) {
			case 1:
				$_SESSION['_pagination_id_'] = 2;
				break;
			case 2:
				$_SESSION['_pagination_id_'] = 1;
				break;
			default:
				$_SESSION['_pagination_id_'] = 1;
		}
	}


/**
 * Digg pagination style
 *
 * @preview  « Previous  1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26  Next »
 */
if (!empty($total_items)) {
$label_previous = html::image(
	$this->add_path('icons/16x16/arrow-left.png'),
	array('alt' => _('previous'),'title' => _('previous'), 'style' => 'margin-bottom: -4px')
);//_('previous');
$label_next = html::image(
	$this->add_path('icons/16x16/arrow-right.png'),
	array('alt' => _('next'),'title' => _('next'))
);//_('next');
?>

<?php
	$paging_step = config::get('pagination.paging_step', '*'); # step used below to print nr of items per page
	$max_items_per_page = config::get('pagination.max_items_per_page', '*'); # maximum items per page to show
	$entries = _('entries');
	if (!isset($items_per_page)) {
		$items_per_page = config::get('pagination.items_per_page', '*');
	}
	?>
	<span class="pagination_entries_str" style="display:none"><?php echo $entries ?></span>

<p class="pagination">
	<?php $url = str_replace('&','&amp;',$url);	?>
	<?php if ($previous_page): ?>
		<a href="<?php echo str_replace('{page}', $previous_page, $url) ?>" class="img prevpage"><?php echo $label_previous ?></a>
	<?php else: ?>
		<?php echo $label_previous ?>
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
		<a href="<?php echo str_replace('{page}', $next_page, $url) ?>" class="img nextpage"><?php echo $label_next ?></a>
	<?php else: ?>
		<?php echo $label_next ?>
	<?php endif ?>
	<?php //echo '&nbsp; (' . _('total') . ': ' . $total_items . ' ' . _('entries') . ')' ?>

</p>


<form class="pagination_form" action="<?php echo basename($_SERVER['PHP_SELF']) ?>" method="get">
		<fieldset>
		<?php //echo _('Show') ?>
		<select id="sel_items_<?php echo $_SESSION['_pagination_id_'] ?>" class="items_per_page" name="items_per_page" onchange="preserve_get_params('sel', $(this).attr('id'));this.form.submit()">
	<?php
		if ($total_items < $paging_step) {
			?>
			<option value="<?php echo $total_items ?>" selected="selected"><?php echo $total_items ?> <?php echo $entries ?></option>
			<?php
		} else {
			?>
			<option value="<?php echo $total_items ?>"<?php if ($items_per_page == $total_items) { ?> selected='selected'<?php } ?>><?php echo _('All').' '.$entries ?></option>
			<?php
		}
		for ($i=$paging_step ; $i<$total_items && $i<=$max_items_per_page; $i+=$paging_step ) {
			?><option value="<?php echo $i ?>"<?php if ($items_per_page == $i) { ?> selected='selected'<?php } ?>><?php echo $i ?> <?php echo $entries ?></option><?php
		}
	?>
		</select>

			<input
				type="text" size="4" name="custom_pagination_field" id="pagination_id_<?php echo $_SESSION['_pagination_id_'] ?>" class="custom_pagination_field"
				title="<?php echo _('Enter number of items to show on each page or select from the drop-down on the left') ?>"
				value="<?php echo $total_items < $items_per_page ? $total_items : $items_per_page ?>" />
			<input type="button" name="show_pagination" class="show_pagination" value="<?php echo _('Go') ?>" />
			</fieldset>
	</form>
<?php } ?>
