<?php
defined('SYSPATH') OR die('No direct access allowed.');
$lp = LinkProvider::factory();
$user = op5Auth::instance()->get_user();
?>
<div class="gettingstarted_container">
  <div class="gettingstarted_columncontainer">
    <div class="gettingstarted_block">
      <div class="gettingstarted_column">
        <div class="gettingstarted_box">
          <div class="gettingstarted_bullet">1</div>
          <div class="gettingstarted_content">
            <h1><span>Start monitoring</span></h1>
            <h2>a. Prepare</h2>
            <p>
            Some servers and network equipment may require an agent to monitor.
            </p>
            <ul class="linklist">
              <li><a href="https://kb.op5.com/x/KATx#InstallandGetStartedwithop5Monitor-Prepareserversornetworkequipmenttobemonitored">Prepare equipment</a></li>
            </ul>
            <h2>b. Add your first host</h2>
            <p>
            Add hosts easily with pre-configured management packs using the Host wizard, or add them manually.
            </p>
			<ul class="linklist">
				<?php if (class_exists('Wizard_Controller')) { ?>
			  <li><a href="<?php echo $lp->get_url('wizard'); ?>">Host wizard</a></li>
				<?php } ?>
				<?php if (class_exists('Configuration_Controller')) { ?>
              <li><a href="<?php echo $lp->get_url('configuration', 'configure', array('page' => 'host_wizard.php')); ?>">Add hosts manually</a></li>
				<?php } ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="gettingstarted_column">
        <div class="gettingstarted_box">
          <div class="gettingstarted_bullet">2</div>
          <div class="gettingstarted_content">
            <h1><span>Get notified</span></h1>
            <h2>a. Supply contact details</h2>
            <p>
            Make sure you are notified if your monitored hosts detect problems.
            </p>
            <ul class="linklist">
				<?php if (class_exists('Configuration_Controller')) { ?>
              <li>
              <a href="<?php echo $lp->get_url('configuration', 'configure', array(
                'page' => 'edit.php?' . http_build_query(array(
                'obj_type' => 'contact',
                'obj_name' => $user->get_username()
                ))
                )); ?>">Add your contact details
			  </a>
			  </li>
				<?php } ?>
            </ul>
            <h2>b. Set up host notifications</h2>
            <p>
				<?php if (class_exists('Configuration_Controller')) { ?>
            In the <a href="<?php echo $lp->get_url('configuration', 'configure'); ?>">configuration interface</a>, select a host to edit, choose the Advanced tab and add your contact.
				<?php } else { ?>
            In the configuration interface, select a host to edit, choose the Advanced tab and add your contact.
				<?php } ?>
            </p>
            <p>
            If you want to send emails using your own mail server see the How-to:
            </p>
            <ul class="linklist">
              <li><a href="https://kb.op5.com/x/awA6">Relaying emails</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="gettingstarted_block">
      <div class="gettingstarted_column">
        <div class="gettingstarted_box">
          <div class="gettingstarted_bullet">3</div>
          <div class="gettingstarted_content">
            <h1><span>Be in control</span></h1>
            <h2>Reports</h2>
            <p>op5 Monitor can supply you with multiple types of configurable reports, such as availability and SLA. See "Report" in the menu.
            </p>
            <h2>Business services</h2>
            <p>
            See the status of your business services and get notified when they experience problems.
            </p>
            <ul class="linklist">
				<?php if (class_exists('Synergy_Controller')) { ?>
              <li><a href="<?php echo $lp->get_url('synergy', 'view'); ?>">Go to Business Services</a></li>
				<?php } ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="gettingstarted_column">
        <div class="gettingstarted_box">
          <div class="gettingstarted_bullet">4</div>
          <div class="gettingstarted_content">
            <h1><span>Learn more</span></h1>
            <h2>We are here to help</h2>
            <p>
            If you need assistance or want a quote:
            </p>
            <ul class="linklist">
              <li><a href="https://www.op5.com/how-to-buy-op5/">How to buy</a></li>
              <li><a target="_blank" href="https://www.op5.com/services/support/">op5 Support</a></li>
            </ul>
            <h2>Resources</h2>
            <p>
            For more information we refer you to our documentation and How-to's.
            </p>
            <ul class="linklist">
              <li><a target="_blank" href="https://kb.op5.com/x/hYD7">op5 Monitor Documentation</a></li>
              <li><a target="_blank" href="https://kb.op5.com/x/UYEK">How-to</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
