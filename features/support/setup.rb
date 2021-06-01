require 'rspec'
require 'capybara'
require 'capybara/rspec'
require 'capybara/cucumber'
require 'selenium-webdriver'
require 'webdrivers'
require 'billy/capybara/cucumber'
require 'syslog'
require 'fileutils'

if ENV['TEST_ENV_NUMBER']
  class Capybara::Server
    def find_available_port
      @port = 9887 + ENV['TEST_ENV_NUMBER'].to_i
      @port += 1 while is_port_open?(@port) and not is_running_on_port?(@port)
    end
  end
end

Billy.configure do |c|
  c.cache = false
  c.logger.level = "error"
  c.before_handle_request = proc { |method, url, headers, body|
    if ENV["ci_tmp_mock_file"]
      headers['X-op5-mock'] = ENV["ci_tmp_mock_file"]
    end
    [method, url, headers, body]
  }
end

Capybara.register_driver :selenium_chrome_headless_billy do |app|
  options = Selenium::WebDriver::Chrome::Options.new
  options.add_argument('--headless')
  options.add_argument('--no-sandbox')
  options.add_argument('--ignore-certificate-errors')
  options.add_argument('--blink-settings=imagesEnabled=true')
  options.add_argument('--window-size=1920,1080')
  options.add_argument('--enable-features=NetworkService,NetworkServiceInProcess')
  options.add_argument("--proxy-server=http://#{Billy.proxy.host}:#{Billy.proxy.port}")
  options.add_argument("--proxy-bypass-list=<-loopback>")

  capabilities = Selenium::WebDriver::Remote::Capabilities.chrome
  capabilities['acceptInsecureCerts'] = true
  capabilities['goog:loggingPrefs'] = { browser: 'ALL' }

  Capybara::Selenium::Driver.new(
    app,
    browser: :chrome,
    desired_capabilities: capabilities,
    options: options,
    clear_local_storage: true,
    clear_session_storage: true
  )
end

Capybara.default_driver = :selenium_chrome_headless_billy
Capybara.javascript_driver = :selenium_chrome_headless_billy
Capybara.run_server = false
Capybara.default_max_wait_time = 30
Capybara.match = :prefer_exact

Syslog.open("cucumber", 0, Syslog::LOG_DAEMON)

# Screenshot any failed scenario
After do |scenario|
  if scenario.failed?
    if ENV['CUKE_SCREEN_DIR']
      screen_dir = ENV['CUKE_SCREEN_DIR']
    else
      screen_dir = './screenshots'
    end
    Dir::mkdir(screen_dir) if not File.directory?(screen_dir)
    screenshot = File.join(screen_dir, "FAILED_#{scenario.name.gsub(' ','_').gsub(/[^0-9A-Za-z_]/, '')}.png")
    screenshot_embed_filename = "./screenshots/FAILED_#{scenario.name.gsub(' ','_').gsub(/[^0-9A-Za-z_]/, '')}.png"
    page.save_screenshot(screenshot, full: true)
    embed screenshot_embed_filename, 'image/png'
    puts 'Browser console output:'
    puts '---'
    for logitem in page.driver.browser.manage.logs.get(:browser)
      puts logitem
    end
    puts '---'
  end
  Capybara.reset_sessions!
end

Before do |scenario|
  @params = {}
  case scenario.source.last
  when Cucumber::Core::Ast::ScenarioOutline
    @scenario_name = scenario.scenario_outline.name
  when Cucumber::Core::Ast::Scenario
    @scenario_name = scenario.name
  when Cucumber::Core::Ast::ExamplesTable::Row
    @scenario_name = scenario.name
  when Cucumber::Core::Test::Case
    @scenario_name = scenario.name
  else
    raise("Unhandled scenario class: #{scenario.source.last}")
  end
end

Before ('@configuration') do
  @configuration = Configuration::NagiosConfiguration.new
end

Before ('@asmonitor') do
  step %Q|I am logged in as "monitor" with password "monitor"|
end

# Head straight to nacoma - doing so might involve visiting configure
Before ('@nacoma') do
  step %Q|I enter nacoma|
end

After ('@configuration') do |scenario|
  #don't remove config dir if the test failed, useful for debugging
  scenario.failed? ? @configuration.reset(false) : @configuration.reset
end

Before ('@ninja_api_auth_enabled') do |scenario|
  $old_http_api_conf = File.read('/etc/op5/http_api.yml')
  File.open('/etc/op5/http_api.yml', 'w') { |file| file.write($old_http_api_conf.gsub('ninja: false', 'ninja: true')) }
end

After ('@ninja_api_auth_enabled') do |scenario|
  File.open('/etc/op5/http_api.yml', 'w') { |file| file.write($old_http_api_conf) }
end

Before ('@enable_get_login') do |scenario|
  File.open('/opt/monitor/op5/ninja/application/config/custom/auth.php', 'w') { |file| file.write("<?php $config['use_get_auth'] = true;") }
end

After ('@enable_get_login') do |scenario|
  File.delete('/opt/monitor/op5/ninja/application/config/custom/auth.php')
end

Before ('@set_saved_reports') do |scenario|
  `mysql -uroot merlin -e "INSERT INTO saved_reports (id, type, report_name, created_by, created_at, updated_by, updated_at) VALUES (100, 'avail', 'test report1', 'administrator', 1537853553, 'administrator', 1548165004)"`
  `mysql -uroot merlin -e "INSERT INTO saved_reports_objects (report_id, object_name) VALUES (100, 'LinuxServers')"`
  `mysql -uroot merlin -e "INSERT INTO saved_reports_options (report_id, name, value) VALUES (100, 'use_alias', 1)"`
end

After ('@set_saved_reports') do |scenario|
  `mysql -uroot merlin -e "DELETE FROM saved_reports_objects WHERE report_id = 100"`
  `mysql -uroot merlin -e "DELETE FROM saved_reports_options WHERE report_id = 100"`
  `mysql -uroot merlin -e "DELETE FROM saved_reports WHERE id = 100"`
end
