When /^I exec "(.*)"$/ do |command|
	  @output = `#{command} 2>&1`
	    @exitcode = $?.exitstatus
end

Then /^I should get exitcode "(.*)"$/ do |int|
	  raise('exitcode=' + String(@exitcode)) unless Integer(int) == Integer(@exitcode)
end

Then /^I shouldn't get exitcode "(.*)"$/ do |int|
	  raise('exitcode=' + String(@exitcode)) unless Integer(int) != Integer(@exitcode)
end

Then /^I should see output "(.*)"$/ do |outtext|
	  raise('output=' + String(@output.chomp)) unless String(@output.chomp) == String(outtext)
end

Then /^I shouldn't see output "(.*)"$/ do |outtext|
	  raise('output=' + String(@output.chomp)) unless String(@output.chomp) != String(outtext)
end
