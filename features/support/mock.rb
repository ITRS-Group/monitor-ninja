require 'json'
require 'cucumber'
require 'tempfile'
module Op5Cucumber::Mock

  class Mock
    attr_reader :file

    def initialize()
      @file = nil
      @data = {'ORMDriverLS default' =>
               {
                 'hostgroups' => [],
                 'hosts' => [],
                 'servicegroups' => [],
                 'services' => []
               },
               'ORMDriverMySQL default' =>
               {
                 'ninja_widgets' => []
               },
               'ORMDriverYAML default' =>
               {
                 'users' => [],
                 'usergroups' => [],
                 'authmodules' => []
               },
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
      case type
      when /^hosts$/
        if not @data["ORMDriverLS default"].key?(:hosts)
          @data["ORMDriverLS default"] << {'hosts' => []}
        end
	'ORMDriverLS default'
      when /^hostgroups$/
        if not @data["ORMDriverLS default"].key?(:hostgroups)
          @data["ORMDriverLS default"] << {'hostgroups' => []}
        end
        'ORMDriverLS default'
      when /^services$/
        if not @data["ORMDriverLS default"].key?(:services)
          @data["ORMDriverLS default"] << {'services' => []}
        end
        'ORMDriverLS default'
      when /^servicegroups$/
        if not @data["ORMDriverLS default"].key?(:servicegroups)
          @data["ORMDriverLS default"] << {'servicegroups' => []}
        end
        'ORMDriverLS default'
      when /^ninja_widgets$/
        if not @data["ORMDriverMySQL default"].key?(:ninja_widgets)
          @data["ORMDriverMySQL default"] << {'ninja_widgets' => []}
        end
        'ORMDriverMySQL default'
      when /^users$/
        'ORMDriverYAML default'
      when /^authmodules$/
        'ORMDriverYAML default'
      when /^usergroups$/
        'ORMDriverYAML default'
      else
        raise "Unknown type #{type}"
      end
    end

    def save()
      if not active?
        @file = Dir::Tmpname.make_tmpname('/tmp/mock', nil)
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
          if field.end_with? 's' and value.is_a? String
            hash[field] = value.split ','
          end
        }
      }
      @data[driver_for_type(type)][type] += hashes
      save
    end

    def delete!()
      File.delete(@file)
    end
  end
end
