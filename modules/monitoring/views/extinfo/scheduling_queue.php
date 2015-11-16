<div>
<?php
	if ($is_remote_filtered) {
		echo '<p class="alert info">' . _('Hosts and services that are checked by peers and pollers have been removed from the queue as the master cannot determine when those checks may be executed.') . '</p>';
	}

	if(!$data) {
		echo '<p class="alert info">' . _('Nothing scheduled') . '</p>';
		return;
	}

?>
	<table id="hostcomments_table">
		<tr>
			<?php foreach($columns as $column => $title) { ?>
					<th>
						<?php echo $title; ?>
					</th>
			<?php } ?>
			<th><?php echo _('Type'); ?></th>
			<th><?php echo _('Active checks'); ?></th>
			<th><?php echo _('Actions'); ?></th>
		</tr>
		<?php
			$check_types = array(
				nagstat::CHECK_OPTION_NONE => _('Normal'),
				nagstat::CHECK_OPTION_FORCE_EXECUTION => _('Forced'),
				nagstat::CHECK_OPTION_FRESHNESS_CHECK => _('Freshness'),
				nagstat::CHECK_OPTION_ORPHAN_CHECK => _('Orphan')
			);

			/**
			 * @return object $row | false
			 */
			foreach($data as $i => $object) {
				$host = ($object instanceof Host_Model) ? $object->get_name() : $object->get_host()->get_name();
				$service = ($object instanceof Service_Model) ? $object->get_description() : false;
		?>
		<tr class="<?php echo $i % 2 == 0 ? 'odd' : 'even'; ?>">

			<td>
				<?php
					echo html::anchor(
						url::method("extinfo", "details", array("host" => $host)),
						$host
					);
				?>
			</td>
			<td>
				<?php
					if ($service) {
						echo html::anchor(
							url::method("extinfo", "details", array("host" => $host, "service" => $service)),
							$service
						);
					}
				?>
			</td>

			<td><?php echo $object->get_last_check() ? date(date::date_format(), $object->get_last_check()) : _('Never checked'); ?></td>
			<td><?php echo $object->get_next_check() ? date(date::date_format(), $object->get_next_check()) : _('No check scheduled'); ?></td>

			<td>
				<?php
					$types = array();
					foreach($check_types as $option => $text) {
						if(($object->get_check_type() == 0 && $option == 0) || $object->get_check_type() & $option) {
							$types[] = $text;
						}
					}
					echo implode(", ", $types);
				?>
			</td>
			<td>
				<?php
					if ($object->get_active_checks_enabled()) {
						echo html::icon('shield-up') . " Enabled";
					} else {
						echo html::icon('shield-down') . " Disabled";
					}
				?>
			</td>
			<td class="icon">
			<?php
					$table = $object->get_table();
					if ($object->get_active_checks_enabled())
						echo html::anchor(
							url::method("cmd", "index", array(
								"command" => "disable_check",
								"table" => $table,
								"object" => $object->get_key())
							), html::icon('disable-active-checks', array(
								'title' => "Disable active checks of this $table")
							)
						);
					else
						echo html::anchor(
							url::method("cmd", "index", array(
								"command" => "enable_check",
								"table" => $table,
								"object" => $object->get_key())
							), html::icon('disable-active-checks', array(
								'title' => "Enable active checks of this $table")
							)
						);

					echo html::anchor(
						url::method("cmd", "index", array(
							"command" => "schedule_check",
							"table" => $table,
							"object" => $object->get_key())
						), html::icon('re-schedule', array(
							'title' => "Re-schedule the check of this $table")
						)
					);
?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
