require 'rspec'
require 'capybara'
require 'capybara/rspec'
require 'capybara/poltergeist'
require 'capybara/cucumber'
require 'syslog'
require 'fileutils'

module CucumberHelpers
  require './features/support/navigationhelpers.rb'
  include NavigationHelpers
  require './features/support/parsinghelpers.rb'
  include ParsingHelpers
  require './features/support/synchronization.rb'
  include Synchronization
  require './features/support/sqlhelpers.rb'
  include SQLHelpers
end

Capybara.register_driver :poltergeist do |app|
  Capybara::Poltergeist::Driver.new(app, :phantomjs_options => ['--ignore-ssl-errors=yes', '--ssl-protocol=any', '--load-images=no'])
end
Capybara.register_driver :poltergeist_debug do |app|
  Capybara::Poltergeist::Driver.new(app, :phantomjs_options => ['--ignore-ssl-errors=yes', '--ssl-protocol=any', '--load-images=yes'], :debug => true, :inspector => true)
end

Capybara.default_driver = :poltergeist
Capybara.javascript_driver = :poltergeist
Capybara.run_server = false
Capybara.default_wait_time = 6
Capybara.match = :prefer_exact

Syslog.open("cucumber", 0, Syslog::LOG_DAEMON)

if ENV['DEBUG'] or ENV['VERBOSE']
  Capybara.default_driver = :poltergeist_debug
  Capybara.javascript_driver = :poltergeist_debug
end

After do |scenario|
  if scenario.failed?
	if ENV['CUKE_SCREEN_DIR']
	  screen_dir = ENV['CUKE_SCREEN_DIR']
	else
	  screen_dir = './screenshots'
	end
	Dir::mkdir(screen_dir) if not File.directory?(screen_dir)
	screenshot = File.join(screen_dir, "FAILED_#{@scenario_name.gsub(' ','_').gsub(/[^0-9A-Za-z_]/, '')}.png")
	screenshot_embed_filename = "./screenshots/FAILED_#{@scenario_name.gsub(' ','_').gsub(/[^0-9A-Za-z_]/, '')}.png"
	page.driver.render(screenshot, :full => true)
	embed screenshot_embed_filename, 'image/png'
	#if ENV['DEBUG'] or ENV['VERBOSE']
	#	page.driver.network_traffic.each { | request |
	#	  puts "#{request.method} #{request.url} => \n" + request.response_parts.map { | response | "\t#{response.status} #{response.status_text}" }.join("\n")
	#	}
	#	puts page.html
	#end
  end
  Capybara.reset_sessions!
end

Before do |scenario|
  @params = {}
  case scenario
  when Cucumber::Ast::Scenario
    @scenario_name = scenario.name
  when Cucumber::Ast::OutlineTable::ExampleRow
    @scenario_name = scenario.scenario_outline.name
  end
  Syslog.log(Syslog::LOG_INFO, "Running '#{@scenario_name}'")
end

Before ('@configuration') do
  @configuration = Configuration::NagiosConfiguration.new
end

After ('@configuration') do |scenario|
  #don't remove config dir if the test failed, useful for debugging
  scenario.failed? ? @configuration.reset(false) : @configuration.reset
end


Before ('@asmonitor') do
  step %Q|I am logged in as "monitor" with password "monitor"|
end


Before ('@enable_get_login') do |scenario|
  File.open('/opt/monitor/op5/ninja/application/config/custom/auth.php', 'w') { |file| file.write("<?php $config['use_get_auth'] = true;") }
end

After ('@enable_get_login') do |scenario|
  File.delete('/opt/monitor/op5/ninja/application/config/custom/auth.php')
end

World CucumberHelpers
