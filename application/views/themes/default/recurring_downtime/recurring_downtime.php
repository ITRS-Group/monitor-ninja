<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div align=center class='optboxtitle'>Schedule Options</div>
<table cellspacing=0 cellpadding=0 border=1 class='optBox'>
	<tr>
		<td class='optBoxItem'>
			<table cellspacing=0 cellpadding=0 class='optBox'>
				<tr>
					<td class='optBoxItem'>
						<form method='post' action='https://192.168.1.169/monitor.old/cgi-bin/downtime_sched.cgi'>
					</td>
					<td>
						<input type='hidden' name='cmd' value='add2'><input type='hidden' name='type' value='host'>
					</td>
				</tr>
				<tr>
					<td class='optBoxRequiredItem'>Host Name:</td>
					<td><b><INPUT TYPE='TEXT' NAME='host' VALUE=''></b></td>
				</tr>
				<tr>
					<td class='optBoxRequiredItem'>Author (Your Name):</td>
					<td><b><input type='text' name='user' value='monitor'></b></td>
				</tr>
				<tr>
					<td class='optBoxRequiredItem'>Comment:</td>
					<td><b><input type='text' name='comment' value='Recurring schedule' size=40></b></td>
				</tr>
				<tr>
					<td class='optBoxRequiredItem'>Time:</td>
					<td><b><input type='text' name='time' value='01:00'></b> (hh:mm)</td>
				</tr>
				<tr>
					<td class='optBoxRequiredItem'>Duration:</td>
					<td><input type='text' name='duration' value='120' size=8 > Minutes </td>
				</tr>
				<tr>
					<td class='optBoxItem'>Days of week:</td>
					<td><b><input type='text' name='day' value=''></b> Mon, Tue, Wed, ...</td>
				</tr>
				<tr>
					<td class='optBoxItem'>Dates of month:</td>
					<td><b><input type='text' name='date' value=''></b> 1, 2, 3 ...</td>
				</tr>
				<tr>
					<td class='optBoxItem' colspan=2></td>
				</tr>
				<tr>
					<td class='optBoxItem'></td>
					<td class='optBoxItem'>
						<input type='submit' name='btnSubmit' value='Commit'>
						<INPUT TYPE='reset' VALUE='Reset'></FORM>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
