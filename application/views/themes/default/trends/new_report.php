<?php

?>
<br />
<?php if (!isset($is_avail)) { ?>
<p><?php echo _('Reporting period') ?>: <?php echo $options['report_period'] ?></p>
<?php
}

if (isset($graph_image_source) && $graph_image_source) { ?>
	<img src="<?php echo url::site() ?>trends/<?php echo $graph_image_source ?>" alt="" />
<?php } ?>
<div style="clear:both"></div>
<?php if(isset($avail_template) && !empty($avail_template)) {
	echo $avail_template;
}
