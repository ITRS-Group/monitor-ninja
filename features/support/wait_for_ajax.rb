module WaitForAjax
  extend self

  #Helper Functions
  def wait_for_ajax
    attempts = 10
    while attempts >= 0 and not finished_all_ajax_requests? do
      attempts -= 1
      sleep(1)
    end
  end

  def finished_all_ajax_requests?
    Capybara.page.evaluate_script('document.readyState === "complete" && typeof(jQuery) === "function" && jQuery.active === 0')
  end

end
