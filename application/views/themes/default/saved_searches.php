<?php defined('SYSPATH') OR die('No direct access allowed.');?>
	<table id="saved_searches_table" title="<?php echo $this->translate->_('Saved searches') ?>" style="display:none">
		<tr style="height:20px">
			<td><strong><?php echo $this->translate->_('Search string') ?></strong></td>
			<td><strong><?php echo $this->translate->_('Name') ?></strong></td>
			<td><strong><?php echo $this->translate->_('Description') ?></strong></td>
			<td colspan="3"></td>
		</tr>
	<?php
	if (isset($searches) && !empty($searches)) {
		foreach ($searches as $s) { ?>
			<tr id="saved_searchrow_<?php echo $s->id ?>">
				<td id="searchquery_<?php echo $s->id ?>"><?php echo html::anchor('search/lookup?query='.$s->search_query, $s->search_query, array('title' => $this->translate->_('Use this search'))) ?></td>
				<td id="searchname_<?php echo $s->id ?>"><?php echo $s->search_name ?></td>
				<td id="searchdescription_<?php echo $s->id ?>"><?php echo $s->search_description ?></td>
				<td id="searchqueryimg_<?php echo $s->id ?>"><?php echo html::anchor('search/lookup?query='.$s->search_query, html::image($this->add_path('icons/16x16/use_search.png'), array('title' => $this->translate->_('Use this search'))) ) ?></td>
				<td class="edit_search_item" id="editsearch_<?php echo $s->id ?>"><?php echo html::image($this->add_path('icons/16x16/edit.png'), array('title' => $this->translate->_('Edit this search'), 'id' => 'editsearchimg_'.$s->id)) ?></td>
				<td class="remove_search_item" id="removesearch_<?php echo $s->id ?>"><?php echo html::image($this->add_path('icons/16x16/remove.png'), array('title' => $this->translate->_('Remove this search'), 'id' => 'removesearchimg_'.$s->id)) ?></td>
			</tr><?php
		}
	}?>

	</table>
