module Synchronization
  def self.wait_until
    require "timeout"
    Timeout.timeout(Capybara.default_max_wait_time) do
      sleep(0.1) until value = yield
      value
    end
  end
end
