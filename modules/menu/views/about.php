<div class="popup-about">
		<img class="popup-about-img" src="/ninja/modules/menu/views/itrs_op5_expanded.png">
  <div class="popup-about-table">
    <div class="popup-about-row popup-about-row-links">
      <span class="popup-link">
        <a href="<?php echo Kohana::config('product.docs_url'); ?>">Knowledge Base</a>
      </span>
      <span class="popup-link">
        <a href="https://www.itrsgroup.com/products/network-monitoring-op5-monitor">ITRS OP5 Monitor</a>
      </span>
      <span class="popup-link">
        <a onclick="openLicenseInfo()">License Information</a>
      </span>
      <span class="popup-link">
        <a href="<?php echo Kohana::config('product.support_url'); ?>">Support</a>
      </span>
  </div>
  </div>
<?php
$data = $about->get_all();
echo html::get_definition_list($data);
?>

</div>
