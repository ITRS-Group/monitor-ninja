<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div style="padding:30px">
	<p>
		<?php echo _('You may upload a new widget as long as you meet the requirements below.') ?><br /><br />

		<ul class="upload_list">
			<li><?php echo _('The widget must be a zip file') ?></li>
			<li><?php echo _('It must contain all the required files') ?></li>
			<li title="<?php echo _('Click to show info') ?>"><a href="" id="dummy_href"><?php echo _('It must contain a manifest file (xml)') ?></a></li>
		</ul>
		<div id="xml_info" style="display:none;padding-top:10px">
			<?php echo _('The xml file should contain the following info:'); ?>
<pre>

&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!-- Manifest file for widget to be used in Ninja4Nagios  --&gt;
&lt;widget_content&gt;
	&lt;author&gt;Your name&lt;/author&gt;
	&lt;version&gt;1.0&lt;/version&gt;
	&lt;Friendly_name&gt;Display name of the widget&lt;/Friendly_name&gt;
	&lt;description&gt;Some info about your widget&lt;/description&gt; &lt;!-- (This info is not shown anywhere today) --&gt;
	&lt;page&gt;tac/index&lt;/page&gt; &lt;!-- (only tac/index supported as of now) --&gt;
&lt;/widget_content&gt;

</pre>
		<?php echo _('The xml file should be placed in the root of the widget folder') ?>
		</div>
	</p><br />

	<?php echo _('Use the form below to upload and install a new widget:') ?><br /><br />
<?php
	echo form::open_multipart('upload/handle_upload', array('id' => 'upload_form'));
	echo form::upload(array('id' => 'upload_file', 'name' => 'upload_file', 'type' => 'file'))."<br /><br />";
	echo form::submit('uploadbtn', 'Upload file');
	echo form::close();
?>
</div>
