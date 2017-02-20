module ParsingHelpers
	def ext_info_table_lookup(key)
		base_xp = "//table[@class='ext']/tbody/tr[td[text() = \"#{key}\"]]/td[position()=last()]"
		begin
			find(:xpath, base_xp).text
		rescue Capybara::ElementNotFound
			#no? maybe it's within a span?
			find(:xpath, base_xp + '/span')
		end
	end
end
