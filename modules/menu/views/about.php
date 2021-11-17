<div class="popup-about">
		<img class="popup-about-img" src="/ninja/modules/menu/views/itrs_op5_expanded.png">
  <table class="popup-about-table">
    <tr class="popup-about-row popup-about-row-links">
      <td>
        <a href="https://docs.itrsgroup.com/docs/op5-monitor/">Knowledge Base</a>
      </td>
      <td>
        <a href="https://www.itrsgroup.com/products/network-monitoring-op5-monitor">ITRS OP5 Monitor</a>
      </td>
      <td>
        <a href="https://support.itrsgroup.com/">Support</a>
      </td>
    </tr>
  </table>
<?php
$data = $about->get_all();
echo html::get_definition_list($data);
?>
</div>
