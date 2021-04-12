require 'tmpdir'
require 'socket'
require 'fileutils'

module Configuration
  class ConfigObject
    @@defaults = {
      'host' => {
        'use' => 'default-host-template',
        'check_command' => 'check-host-alive'
      },
      'hostgroup' => {
      },
      'servicegroup' => {
      },
      'service' => {
        'use' => 'default-service'
      },
      'contact' => {
      }
    }
    ##
    # @param [String] type A Nagios object type, 'host' for example
    # @param [Hash] props
    # @example Overwrite the default configs with a Hash
    #   ConfigObject.new('host', {'host_name' => 'sven', 'address': 'localhost'})
    def initialize(type, props)
      @type = type
      @props = @@defaults[type].merge(props)
    end

    ##
    # @return [String]
    def name
      if @type == 'service' then
        return @props['service_description']
      else
        return @props["#{@type}_name"]
      end
    end

    ##
    # @return [String] Representation of current object in Nagios config format
    def to_s
      res = "define #{@type} {\n"
      @props.each do |key, value|
        if not value.empty? then
          res += "\t#{key}\t#{value}\n"
        end
      end
      return res + "}\n";
    end
  end

  class NagiosConfiguration
    ##
    # @param template_dir [String]
    def initialize(template_dir="test/configs/templates/etc")
      @source_path = template_dir
      @host_check_results = []
      @to_unlink = []
      @to_restore = []
      @nagios_bin = "/opt/monitor/bin/monitor"
      tmpname = Dir::Tmpname.create(['-', '.']) {}
      @root_path = File.join(Dir.pwd, 'ci_tmp/config_'+tmpname)
      FileUtils::mkdir_p @root_path
      @objects = {}
    end

    ##
    # @param result [String]
    def add_host_check_result(result)
      @host_check_results.push "PROCESS_HOST_CHECK_RESULT;%s" % result
    end

    ##
    # @param result [String]
    def add_service_check_result(result)
      @host_check_results.push "PROCESS_SERVICE_CHECK_RESULT;%s" % result
    end

    ##
    # @param host [String]
    def set_host_in_downtime(host)
      # 10 hours from now should be enough
      issue_command "SCHEDULE_HOST_DOWNTIME;%s;%d;%d;1;0;0;someone;In downtime for testing" % [host, Time.now.to_i, Time.now.to_i+36000]
    end

    ##
    # @param service [String]
    def set_service_in_downtime(service)
      # 10 hours from now should be enough
      issue_command "SCHEDULE_SVC_DOWNTIME;%s;%d;%d;1;0;0;someone;In downtime for testing" % [service, Time.now.to_i, Time.now.to_i+36000]
    end


    def submit_passive_check_results
      @host_check_results.each {|result|
        issue_command result
      }
      @host_check_results = []
    end

    ##
    # @return [Array]
    def objects
      return @objects
    end

    ##
    # We create a fixture, consisting of a fake installation directory in tmp
    # which contains whatever configuration files, var data, and sockets we need.
    def activate
      @is_active = true
      @var_path = (File.exists?("/mnt/logs/") ? "/mnt/logs/" : @root_path) + "/var/"
      @rw_path = File.join(@root_path, "var", "rw") #<- this is where we store sockets and stuff
      @etc_path = File.join(@root_path, "etc") #<- and, of course, configuration
      if (!Dir.exists?(@var_path))
        Dir.mkdir(@var_path, 0777)
      end
      if (!Dir.exists?(File.join(@root_path, "var")))
        Dir.mkdir(File.join(@root_path, "var"), 0777)
      end
      if (!Dir.exists?(@rw_path))
        Dir.mkdir(@rw_path, 0777)
      end
      FileUtils.chmod 0777, @root_path
      FileUtils.chmod 0777, @var_path
      FileUtils.chmod 0777, @rw_path
      FileUtils.cp_r(@source_path, @etc_path, :remove_destination => true)
      FileUtils.chmod_R 0777, File.join(@etc_path, "op5lib")

      @OP5LIBCFG = File.join(@etc_path, "op5lib")
      #nagios setup
      nagios_cfg = File.join @etc_path, "nagios.cfg"

      @cmd_file = File.join(@rw_path, "nagios.cmd")
      if not File.exists? nagios_cfg
        raise "No nagios.cfg found at %s, template incomplete. Bailing out!" % nagios_cfg
      end
      update_paths(nagios_cfg)
      write_objects_cfg
      set_nagios_pipe(@cmd_file)
      nagios_log_filename = parse_config(nagios_cfg)["log_file"]

      patch_nacoma_config
      patch_op5lib_config
      spawn_child("su monitor -c '#{@nagios_bin} -d #{nagios_cfg}'")
      begin
        file_wait(@cmd_file, :state => :exists)
      rescue Exception => e
        puts "==== NAGIOS LOG ===="
        puts `tail #{nagios_log_filename}`
        puts "==== NAGIOS LOG END ===="
        raise e
      end

      `mysql -uroot nacoma -e 'TRUNCATE action_log'`# update timestamp so nacoma imports

      sock_path = File.join(@rw_path, 'live')
      check_livestatus sock_path

      #finally, selectively update ownership and mode to imitate what a real
      #installation would have in order to enable Nacoma to save configuration
      FileUtils.chown_R 'monitor', `id -gn monitor`.strip, @root_path
      FileUtils.chmod 0664, Dir.glob(File.join(@etc_path, "*"))
      FileUtils.chmod 0777, @etc_path
      FileUtils.chmod 0777, @OP5LIBCFG
    end

    ##
    # @param socket_path [String]
    # @raise [RuntimeError] Livestatus was not enabled
    def check_livestatus(socket_path)
      file_wait(socket_path)

      UNIXSocket.open(socket_path) do |socket|
        socket.write("GET status\nResponseHeader: fixed16\n\n");
        response = socket.gets
        if response.length == 0 || response[0..2] != "200"
          raise "No response from LiveStatus socket."
        end
      end
    end

    ##
    # @param cfg [String] Path of nagios.cfg to use
    # @raise Faulty configuration
    def verify_configuration(cfg)
      IO.popen("#{@nagios_bin} --allow-root -v #{cfg}") {|p|
        config_check_output = p.read
      }
      if ($?.exitstatus != 0)
        puts config_check_output
        raise "Faulty configuration :("
      end
    end

    ##
    # @param cmdstr [String]
    def spawn_child(cmdstr)
      pid = fork
      pid.nil? ? exec(cmdstr) : Process.detach(pid)
    end

    ##
    # @param cfg_path [String]
    def update_paths(cfg_path)
      if File.exist? cfg_path
        contents = File.read(cfg_path)
        File.open(cfg_path, 'w') do |cfg_file|
          cfg_file.write contents.gsub(
                           /@@TESTDIR@@/, @root_path
                         ).gsub(
                           /@@GROUP@@/, `id -gn monitor`.strip
                         ).gsub(
                           /@@USER@@/, `id -un monitor`
                         ).gsub(
                           /@@RWDIR@@/, @rw_path
                         ).gsub(
                           /@@VARDIR@@/, @var_path
                         ).gsub(
                           /@@VERBOSE@@/, ENV['VERBOSE'] ? "1" : "0"
                         ).gsub(/@@LOGDIR@@/, @var_path)
        end
      end
    end

    ##
    # @param pipe_path [String]
    def set_nagios_pipe(pipe_path)
      config_php = "/opt/monitor/op5/ninja/application/config/custom/config.php"
      File.open(config_php, 'w') { |config_file|
        config_file.write "<?php $config['nagios_pipe'] = '%s'; putenv('OP5LIBCFG=#{@OP5LIBCFG}');" % [pipe_path];
      }
      @to_unlink.push(config_php)
    end

    def write_objects_cfg
      config_file = File.join @etc_path, "objects.cfg"
      File.open(config_file, 'a') do |cfg_file|
        cfg_file.write self.to_s
      end
    end

    ##
    # @param key [String]
    # @param value [Array]
    # @return [Array]
    def []=(key, value)
      objs = []
      value.each { |item|
        objs.push(ConfigObject.new(key, item))
      };
      @objects[key] = objs
      objs
    end

    ##
    # @return [String] Nagios config composed of each object included
    def to_s
      ret = ""
      @objects.each_value { |objs| objs.each { |obj| ret += obj.to_s } }
      return ret
    end

    def patch_nacoma_config
      config = "<?php putenv('OP5LIBCFG=#{@OP5LIBCFG}'); $DEBUG = TRUE; $HOME = '#{@root_path}/'; $Config['main'] = $HOME . 'etc/nagios.cfg'; $Config['cgi'] = $HOME . 'etc/cgi.cfg';"
      config_file = '/opt/monitor/op5/nacoma/custom_config.php';
      if not File.exists? config_file then
        File.open(config_file, 'w') { |cf|
          cf.write(config)
        }
        @to_unlink.push(config_file)
      else
        puts "Cowardly refusing to overwrite existing custom config at %s, might not be a problem unless these are Nacoma tests" % config_file
      end
    end

    def patch_op5lib_config
      File.open(File.join(@OP5LIBCFG, "livestatus.yml"), 'w') { |lsyml|
        lsyml.write "path: #{@rw_path}/live"
      }
      File.open(File.join(@OP5LIBCFG, "queryhandler.yml"), 'w')  { |qhyml|
        qhyml.write "socket_path: #{@rw_path}/nagios.qh"
      }
    end

    ##
    # @param command [String]
    # @raise [RuntimError] No command file found
    def issue_command(command)
      if File.exists? @cmd_file
        File.open(@cmd_file, 'w+') do |cmd_f|
          cmd_f.puts("[%d] %s" % [Time.now.to_i, command])
          cmd_f.flush
        end
      else
        raise "Command file not available, did Nagios start properly?"
      end
    end

    ##
    # @param cleanup_dirs [Boolean] (true)
    def reset(cleanup_dirs=true)
      if not @is_active
        return
      end
      @to_unlink.each{ |f| File.unlink f }
      @to_restore.each{ |f| FileUtils.mv f + ".orig", f }
      nagios_lock = File.join @var_path, "nagios.lock"
      begin
        nagios_pid = File.read(nagios_lock).to_i
        Process.kill("TERM", nagios_pid)
      rescue Errno::ESRCH
        puts "Can not kill Nagios, already dead"
      end
      file_wait(nagios_lock, :state=>:removed)
      #We need to wait for the livestatus socket to disappear
      #since Nagios has not necessarily finished unloading all
      #brokers by the time it removes the pid file (above)
      file_wait(File.join(@rw_path, "live_tmp"), :state=>:removed)
      `mysql -uroot nacoma -e 'TRUNCATE action_log'`# update timestamp so nacoma imports
      if cleanup_dirs
        FileUtils.rm_rf @root_path if File.exists? @root_path
      else
        puts "Not removing directory %s" % @root_path
      end
    end

    ##
    # @param file [String]
    # @param args [Hash] ({:state => :exists})
    # @raise [RuntimeError] Timeout or bad status from file handling business
    def file_wait(file, args = {:state => :exists})
      if args[:state].equal? :exists
        cond = lambda {|file| File.exists?(file)}
      elsif args[:state].equal? :removed
        cond = lambda {|file| not File.exists?(file)}
      else
        raise "Unknown desired state '%s' for file %s" % [args[:state].to_s, file.to_s]
      end
      max_sleep = 40
      sleep_time = 0.1
      slept = 0
      until cond.call(file) do
        if slept < max_sleep
          sleep sleep_time
          slept += sleep_time
        else
          raise "Timed out waiting for file %s" % [file.to_s]
        end
      end
    end

    ##
    # @param config_file_path [String]
    # @return config [Hash] ({option [String] => value [String]})
    def parse_config(config_file_path)
      config = Hash.new
      File.open(config_file_path, 'r') do |file|
        file.each_line do |line|
          matches = line.match(/^\s*(\w+)\s*=\s*"?([^"]+)"?\s*$/)
          if matches
            option, value = matches.captures
            config[option] = value
          end
        end
      end
      return config
    end
  end

  class TrapperConfiguration
    def initialize()
      @collector_pid = "/tmp/collector.run"
      @collector_conf = "/opt/trapper/etc/collector.conf"
      @processor_pid = "/tmp/processor.run"
      @processor_conf = "/tmp/processor.conf"
      @to_unlink = []
    end
    def set_trapper_db(database, user, pass)
      # first for ninja:
      config_php = "/opt/monitor/op5/ninja/modules/trapper/config/custom/database.php"
      if not File.exist? config_php
        File.open(config_php, 'w') { |config_file|
          config_file.write "<?php $config['trapper']['connection'] = array_merge($config['trapper']['connection'], array('user' => '%s', 'pass' => '%s', 'database' => '%s'));" % [user, pass, database];
        }
        @to_unlink.push(config_php)
      else
        puts "Cowardly refusing to overwrite existing custom config at %s" % config_php
      end

      # and then for collector:
      my_config = File.expand_path('~/.my.cnf')
      if not File.exist? my_config
        File.open(my_config, 'w') { |file|
          # an extra foo=bar, because the last statement is ignored by my mysql
          file.write "[snmptrapd]\nhostname=localhost\ndatabase=%s\nuser=%s\npassword=%s\nfoo=bar\n" % [database, user, pass]
          @to_unlink.push(my_config)
        }
      else
        puts "Cowardly refusing to overwrite existing custom config at %s" % my_config
      end

      # and finally for processor:
      if not File.exist? @processor_conf
        File.open(@processor_conf, 'w') { |file|
          file.write "db.name = \"#{database}\"\ndb.user = \"#{user}\"\ndb.password = \"#{pass}\"\npidfile = \"#{@processor_pid}\""
          @to_unlink.push(@processor_conf)
        }
      else
        puts "Cowardly refusing to overwrite existing custom config at %s" % @processor_conf
      end
    end

    def start_trapper_daemons
      `service collector stop`
      `/opt/trapper/bin/collector -c #{@collector_conf} -p #{@collector_pid}`
      `service processor stop`
      `/usr/bin/processor -c #{@processor_conf}`
    end

    def initialize_test_db
      `mysql -uroot -e "drop user 'trapper_test'@'localhost'"`
      `mysql -uroot -e 'drop database trapper_test'`
      `mysql -uroot -e 'create database trapper_test'`
      $?.success? || abort("Couldn't create test database");
      `mysql -uroot -e "create user 'trapper_test'@'localhost' identified by 'trapper_test'"`
      $?.success? || abort("Couldn't create test user");
      `mysql -uroot -e "grant all privileges on trapper_test.* to 'trapper_test'@'localhost'"`
      $?.success? || abort("Couldn't grant privilegies on test user");
      `mysql -uroot trapper_test < /opt/trapper/share/db/collector.sql`
      $?.success? || abort("Failed to install collector tables - is op5-trapper-collector installed?");
      `mysql -uroot trapper_test < /usr/share/processor/processor.sql`
      $?.success? || abort("Failed to install processor tables - is op5-trapper-processor installed?");
    end

    def activate
      initialize_test_db
      set_trapper_db('trapper_test', 'trapper_test', 'trapper_test')
      start_trapper_daemons()
    end

    def reset
      begin
        collector_pid = File.read(@collector_pid).to_i
        Process.kill("TERM", collector_pid)
      rescue Errno::ESRCH
        puts "Can not kill collector, already dead"
      end
      begin
        processor_pid = File.read(@processor_pid).to_i
        Process.kill("TERM", processor_pid)
			rescue Errno::ESRCH
				puts "Can not kill processor, already dead"
			end
			@to_unlink.each{ |f| File.unlink f }
		end
	end
end
