# KISS, expand with settings-structure if needed
When /^I expose the widget "([^"]+)"$/ do |widget|
  @widget = Op5Cucumber::Widget::ExternalWidget.new(widget)
end

When /^I expose the widget "([^"]+)" with settings/ do |widget, settings|
  @widget = Op5Cucumber::Widget::ExternalWidget.new(widget, settings.hashes[0])
end

When /^I have a broken widget with error message "([^"]+)"$/ do |msg|
  @widget = Op5Cucumber::Widget::BrokenWidget.new(msg)
end

When /^I have a widget that fails to render with error message "([^"]+)"$/ do |msg|
  @widget = Op5Cucumber::Widget::UnrenderableWidget.new(msg)
end

After do
  if not @widget.nil?
    @widget.delete!
  end
end
