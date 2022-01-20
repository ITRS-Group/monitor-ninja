Then(/^I should have a tasty cookie$/) do
	# page.driver.cookies looks like this: {"PHPSESSID"=>#<Capybara::Poltergeist::Cookie:0x000000039668a8 @attributes={"domain"=>"localhost", "httponly"=>true, "name"=>"PHPSESSID", "path"=>"/", "secure"=>true, "value"=>"ke0haa7p9dc4vfaubd123tubg2"}>}
	page.driver.cookies.each {
		| (cookie_name, cookie) |
		cookie.secure?.should be == true
		cookie.httponly?.should be == true
	}
end
