module Op5Cucumber::NavigationHelpers
  ##
	# Convenience method of translating 'login page' to its expanded URL
	#
	# @param [String] page_name
	# @return [String] URL
	# @raise [RuntimeError] Requested the URL of a page name which is not yet defined
	def self.path_to(page_name)
		pages = {
			'main page' => '/index.php/tac/index',
			'login page' => '/index.php/default/show_login',
			'nagvis page' => '/index.php/nagvis/index',
			'nacoma page' => '/op5/nacoma/',
			'Host details page' => '/index.php/status/host/all',
			'Hostgroup details page' => '/index.php/listview?q=%5Bhostgroups%5D%20all',
			'Service details page' => '/index.php/status/service/all',
			'Configure page' => '//index.php/configuration/configure', # FIXME: This is bad; this is nacoma
			'list view' => '/index.php/listview',
			'main trap page' => '/index.php/listview?q=[traps] all'
		}

		if not pages.member? page_name then
			raise "Can't find mapping from \"#{page_name}\" to a path.\n" +
				"Now, go and add a mapping in #{__FILE__}"
		else
			"https://#{SERVER}/#{NINJA_URL_PATH}" + pages[page_name]
		end
	end

	##
	# @param path [String] URL in the form of /monitor/index.php/kdjb
	# @return [String] Prepended with domain
	def self.url_for(path)
		"https://#{SERVER}/#{NINJA_URL_PATH}" + path
	end
end
