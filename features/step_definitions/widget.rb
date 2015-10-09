widget_conf = "/opt/monitor/op5/ninja/application/config/custom/external_widget.php"

# KISS, expand with settings-structure if needed
When /^I expose the widget "([^"]+)"/ do |widget|
  if not File.exist?(widget_conf)
    config = <<-CONFIG
<?php
$config['widget_name'] = '#{widget}';
$config['username'] = "monitor";
$config['groups'] = array('admins', 'guests');

$config['widgets'] = array();
CONFIG
    File.open(widget_conf, "w"){|f| f.write(config.strip)}
  end
  new_conf = <<-WIDGET
$config['widgets']['#{widget}'] = array(
  'name' => '#{widget}',
  'friendly_name' => '#{widget}',
  'setting' => array(
    'height' => 600
  )
);
  WIDGET
  File.open(widget_conf, "a"){|f| f.write("\n" + new_conf)}
end

After('@external_widget') do
  File.delete(widget_conf)
end
