require 'json'
require 'cucumber'
require 'tempfile'
module Mock

  class Mock
    attr_reader :file

    def initialize()
      @file = nil
      @data = {
        'MockedClasses' => []
      }
      @mocked_classes = {}
    end

    def active?()
      !@file.nil?
    end

    def data(type)
      @data[driver_for_type(type)][type]
    end

    def driver_for_type(type)
      # Note: `type` must be the *front-end* name of the table, that is, the
      # "saved_filters" part of "[saved_filters] all" when you are trying to
      # query it. It should therefore *not* be "ninja_saved_filters" which is
      # the backend-name (what's really stored in the database).
      case type
      when /^host.*s$/
        'ORMDriverLS default'
      when /^service.*s$/
        'ORMDriverLS default'
      when /^status$/
        'ORMDriverLS default'
      when /^dashboard_widgets$/
        'ORMDriverMySQL default'
      when /^dashboards$/
        'ORMDriverMySQL default'
      when /^notification.*s$/
        'ORMDriverMySQL default'
      when /^users$/
        'ORMDriverYAML default'
      when /^authmodules$/
        'ORMDriverYAML default'
      when /^usergroups$/
        'ORMDriverYAML default'
      when /^settings$/
        'ORMDriverMySQL default'
      when /^contact.*s$/
        'ORMDriverLS default'
      when /^permission_quark.*s$/
        'ORMDriverMySQL default'
      else
        raise "Unknown type #{type}"
      end
    end

    def save()
      if not active?
        tmpname = Dir::Tmpname.create(['-', '.']) {}
        @file = File.join(Dir.pwd, "ci_tmp_mock" + tmpname)
        FileUtils::mkdir_p File.dirname(@file)
      end
      @mocked_classes.each {|real_class, blk|
        @data["MockedClasses"] << {"real_class" => real_class}.merge(blk)
      }
      @mocked_classes = {}

      File.open(@file, 'w') { |f|
        f.write(@data.to_json)
      }
      File.chmod(0777, @file)
    end

    def mock_class(real_class, class_block)
      @mocked_classes[real_class] = class_block
      save
    end

    def mock(type, hashes={})
      if type == "MockedClasses"
        raise "Can't mock classes through mock(), use mock_class() instead"
      end
      hashes.each {|hash|
        hash.map {|field, value|
          # Let's split all properties, guessed based on that the field end
          # with a "s". This is really silly assumption, so we need to
          # (at least) filter out the "alias" column. Otherwise, if we try to
          # mock the alias column in a cucumber step, we get an array as a
          # value, with a single string as a member.

          if not [
            'alias',
            'notes',
            'obsess',
            'accept_passive_checks',
            'enable_notifications',
            'enable_event_handlers',
            'execute_service_checks',
            'execute_host_checks',
            'accept_passive_service_checks',
            'accept_passive_host_checks',
            'obsess_over_hosts',
            'obsess_over_services'
          ].include? field and field.end_with? 's' and value.is_a? String
            values = value.split ','
            if values[0].is_a? String and values[0].include? "="
              hash[field] = {}
              values.each {|v|
                key, value = v.split '='
                hash[field][key] = value
              }
            else
              hash[field] = values
            end
          end
        }
        # Due to native driver intersects these fields are required
        # but we don't want to mock them explicitly in every scenario
        if type == 'hosts' or type == 'services'
          hash['state'] = 0 if not hash.key?('state')
          hash['has_been_checked'] = 1 if not hash.key?('has_been_checked')
        end
      }
      driver = driver_for_type(type)
      if not @data.key?(driver)
        @data[driver] = {}
      end
      if not @data[driver].key?(type)
        @data[driver][type] = []
      end

      @data[driver][type] += hashes
      save
    end

    def delete!()
      File.delete(@file)
    end
  end
end
