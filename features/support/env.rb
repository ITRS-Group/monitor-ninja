require 'op5cucumber'

if ENV['SERVER']
  SERVER=ENV['SERVER']
else
  SERVER='localhost'
end

if ENV['CUKE_NINJA_URL_PATH']
  NINJA_URL_PATH = ENV['CUKE_NINJA_URL_PATH']
else
  NINJA_URL_PATH = 'monitor'
end

if ENV['CUKE_NINJA_ROOT']
  NINJA_ROOT = ENV['CUKE_NINJA_ROOT']
else
  NINJA_ROOT = '/opt/monitor/op5/ninja'
end

After do |scenario|
  case scenario
  when Cucumber::Ast::Scenario
    name = scenario.name
  when Cucumber::Ast::OutlineTable::ExampleRow
    name = scenario.scenario_outline.name
  end
  if scenario.failed?
      puts "Scenario '#{name}' failed"
  end

end
World Op5Cucumber
