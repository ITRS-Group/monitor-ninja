require 'time'

module Logserver
	class SimpleSyslog
		def initialize
			@socket = UDPSocket.new
		end

		def log(entries)
			@socket.connect "127.0.0.1",514
			entries.each{|entry|
				sent = @socket.send entry.to_s,0

			}
		end

		def activate!
		end
	end

	class LogEntry
		def initialize(fields)
			@fields = fields
		end

		def to_s
			to_bsd_syslog_s
		end

		def to_bsd_syslog_s
			#Work in progress, all fields declared always, for now
			lfields =
				{
				:priority => 13,
				:version => 1,
				:sent_at => Time.now.strftime("%b %e %H:%M:%S"),
				:program => "LSTest",
				:procid => "123",
				:message => "dummy test message"
			}.merge(@fields)
			"<%{priority}>%{sent_at} %{program}[%{procid}]: %{message}" % lfields
		end

		def to_rfc5424_syslog_s
			lfields =
				{
				:priority => 13,
				:version => 1,
				:sent_at => Time.now.iso8601,
				:host => 'localhost',
				:program => 'LSTest',
				:procid => '123',
				:sdata => '-',
				:msgid => '-',
				:message => "dummy test message"

			}.merge(@fields)
			"<%{priority}>%{version} %{sent_at} %{host} %{program} %{procid} %{msgid} %{sdata} %{message}" % lfields
		end
	end
end
