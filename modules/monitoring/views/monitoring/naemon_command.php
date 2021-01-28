<script>
$(function(){
	var counter = 0; // Declare counter. This variable count the total number of alerts.
  	$(".alert").each(function(){
		counter++;
		// If counter is equal one, the alert will be displayed. 
		if(counter === 1) {
			$(this).css("display", "block");
		} 
	});
});
</script>

<?php
if ($result['status']) {
	echo '<div class="alert notice" style="display: none;">'.html::specialchars($result['output']);
	echo "</div>\n";
	$this->footer = '<input style="margin-left: 12px" type="button" value="Done" onclick="history.go(-2)" />'."\n";
} else {
	echo '<div class="alert error" style="display: none;">'.sprintf(_('There was an error submitting your commands to %s.'), Kohana::config('config.product_name'));
	if (!empty($result['output'])) {
		echo '<br /><br />'._('ERROR').': '.html::specialchars($result['output']);
	}
	echo "</div>\n";
	$this->footer = '<input style="margin-left: 12px" type="button" value="Back" onclick="history.go(-1)" />'."\n";
}
