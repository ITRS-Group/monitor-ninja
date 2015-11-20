require 'op5cucumber'

Before do |scenario|
  @mock = Op5Cucumber::Mock::Mock.new
end
World Op5Cucumber
