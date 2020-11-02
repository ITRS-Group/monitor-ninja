<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div>
<table>
	<colgroup>
		<col />
		<col style="min-width: 128px" />
	</colgroup>
	<?php
		foreach ( $info as $row ) {

			echo "<tr>";
				echo "<td class=\"dark\">" . $row["title"] . "</td>";

				if ( isset( $row[ "command" ] ) ) {

					if ( isset( $row[ "value" ] ) ) {
						echo "<td style=\"padding: 8px\">" . $row["value"] . "</td>";
						echo "<td>";
					} else {
						echo "<td colspan=\"2\">";
					}

					if ( is_array( $row[ "command" ] ) ) {
						echo implode( " ", $row[ "command" ] );
					} else {
						echo $row[ "command" ];
					}

				} else {
					echo "<td colspan=\"2\" style=\"padding: 8px\">" . $row["value"];
				}

				echo "</td>";
			echo "</tr>";
		}
	?>
</table>
</div>

