require 'cucumber'

module NavigationHelpers
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

	##
	# Convenience method of translating 'login page' to its expanded URL
	#
	# @param [String] page_name
	# @return [String] URL
	# @raise [RuntimeError] Requested the URL of a page name which is not yet defined
	def path_to(page_name)
		pages = {
			'main page' => '/index.php/tac/index',
			'login page' => '/index.php/default/show_login',
			'nagvis page' => '/index.php/nagvis/index',
			'nacoma page' => '/op5/nacoma/',
			'Host details page' => '/index.php/status/host/all',
			'Hostgroup details page' => '/index.php/listview?q=%5Bhostgroups%5D%20all',
			'Service details page' => '/index.php/status/service/all',
			'Configure page' => '//index.php/configuration/configure', # FIXME: This is bad; this is nacoma
			'PNP page' => '/index.php/pnp/',
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
	def url_for(path)
		"https://#{SERVER}" + path
	end
end
