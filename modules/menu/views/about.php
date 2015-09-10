<div class="popup-about">
  <img class="popup-about-img" src="/ninja/modules/menu/views/about-logo.png" width="256">
  <table class="popup-about-table">
    <?php
      foreach ($about->get_all() as $label => $version) {
        ?>
          <tr class="popup-about-row">
            <td colspan="2" class="popup-about-cell"><?php echo $label; ?></td>
            <td colspan="2" class="popup-about-cell"><?php echo $version; ?></td>
          </tr>
        <?php
      }
    ?>
    <tr class="popup-about-row popup-about-row-links">
      <td>
        <a href="https://kb.op5.com/x/UYEK">Knowledge Base</a>
      </td>
      <td>
        <a href="http://www.op5.com">www.op5.com</a>
      </td>
      <td>
        <a href="http://www.op5.com/support">Support</a>
      </td>
    </tr>
  </table>
</div>