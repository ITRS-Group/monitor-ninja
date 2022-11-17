<div class="popup-about">
		<img class="popup-about-img" src="/ninja/modules/menu/views/itrs_op5_expanded.png">
  <table class="popup-about-table">
    <tr class="popup-about-row popup-about-row-links">
      <td>
        <a href="<?php echo Kohana::config('product.docs_url'); ?>">Knowledge Base</a>
      </td>
      <td>
        <a href="https://www.itrsgroup.com/products/network-monitoring-op5-monitor">ITRS OP5 Monitor</a>
      </td>
      <td>
        <a href="<?php echo Kohana::config('product.support_url'); ?>">Support</a>
      </td>
    </tr>
  </table>

<div id="aboutInfo" style="display:block;">
  <?php
    $data = $about->get_all();
    echo html::get_definition_list($data);
  ?>

  <a onclick="document.getElementById('aboutInfo').style.display='none'; document.getElementById('licenseInfo').style.display='block'">Show License Information &raquo;</a>
</div>

<div id="licenseInfo" style="display:none;all:inherit;">
  <a onclick="document.getElementById('aboutInfo').style.display='block'; document.getElementById('licenseInfo').style.display='none'">&laquo; Back</a>
  <?php require_once('license_info.php'); ?>
</div>

</div>
