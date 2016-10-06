
# This is a step that really shouldn't be in cucumber, hence the "hacky" way of
# changing cucumber behaviour during it.
#
# However, It's about rendering, and the real test should be "can I save
# when a hidden required field is not set".
#
# Buuut, How do you validate that the save has been performed when mocked
# configuration from cucumber is not persistent?
Then /^the hidden required field "([^\"]+)" should not be required$/ do |field_name|
  before = Capybara.ignore_hidden_elements;
  Capybara.ignore_hidden_elements = false;
  page.should have_selector(:css, "[name='#{field_name}'][data-hidden-required='required']")
  page.should have_no_selector(:css, "[name='#{field_name}'][required]")
  Capybara.ignore_hidden_elements = before;
end
