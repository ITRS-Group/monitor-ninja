<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="nagvis-widget-content">
        <?php
        if (isset($arguments['map']) && $arguments['map']) {
                if ($external_widget) {
?>
        <iframe name="nagvis" class="nagvis" src="/nagvis/frontend/nagvis-js/index.php?mod=Map&act=view&show=<?php print $arguments['map'] ?>&request_context=external_widget&widget=<?php echo $name; ?>&header_menu=0" width="100%" height="<?php print $arguments['height'] ?>" frameborder="0">
        Error : Can not load NagVis.
        </iframe>
<?php
                } else {?>
        <iframe name="nagvis" class="nagvis" src="/nagvis/frontend/nagvis-js/index.php?mod=Map&act=view&show=<?php print $arguments['map'] ?>&header_menu=0" width="100%" height="<?php print $arguments['height'] ?>" frameborder="0">
        Error : Can not load NagVis.
        </iframe>
        <?php }} else { ?>
        <p>No maps configured</p>
        <?php } ?>
</div>
