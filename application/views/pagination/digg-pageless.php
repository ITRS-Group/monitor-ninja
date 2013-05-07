<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Like the Digg style, but without page numbers, because they're slow to calculate
 *
 * @preview  « Previous  Next »
 */
$label_previous = '<span class="icon-16 x16-arrow-left" title="'._('Previous').'"></span>';
$label_next = '<span class="icon-16 x16-arrow-right" title="'._('Next').'"></span>';
?>
<div class="pagination_container">

<?php
	$paging_step = config::get('pagination.paging_step', '*'); # step used below to print nr of items per page
	$max_items_per_page = config::get('pagination.max_items_per_page', '*'); # maximum items per page to show
	$entries = _('entries');
	if (!isset($items_per_page)) {
		$items_per_page = config::get('pagination.items_per_page', '*');
	}
	?>
	<span class="pagination_entries_str" style="display:none"><?php echo $entries ?></span>

<div class="pagination">
	<?php $url = str_replace('&','&amp;',$url);	?>
	<?php if ($previous_page): ?>
		<a href="<?php echo str_replace('{page}', $previous_page, $url) ?>" class="prevpage"><?php echo $label_previous ?></a>
	<?php else: ?>
		<?php echo $label_previous ?>
	<?php endif ?>

	<?php if ($next_page && !$hide_next): ?>
		<a href="<?php echo str_replace('{page}', $next_page, $url) ?>" class="nextpage"><?php echo $label_next ?></a>
	<?php else: ?>
		<?php echo $label_next ?>
	<?php endif ?>
	<?php //echo '&nbsp; (' . _('total') . ': ' . $total_items . ' ' . _('entries') . ')' ?>

</div>
</div>
