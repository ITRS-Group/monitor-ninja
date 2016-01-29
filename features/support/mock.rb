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
               }
      }
    end

    def active?()
      !@file.nil?
    end

    def data(type)
      @data[driver_for_type(type)][type]
    end

    def driver_for_type(type)
      case type
      when /^host.*s$/
        'ORMDriverLS default'
      when /^service.*s$/
        'ORMDriverLS default'
      when /^ninja_widgets$/
        'ORMDriverMySQL default'
      else
        raise "Unknown type #{type}"
      end
    end

    def save()
      if not active?
        @file = Dir::Tmpname.make_tmpname('/tmp/mock', nil)
      end
      File.open(@file, 'w') { |f|
        f.write(@data.to_json)
      }
    end

    def mock(type, hashes={})
      if type == "MockedClasses"
        @data[type] = hashes
      else
        hashes.each {|hash|
          hash.map {|field, value|
            if field.end_with? 's'
              hash[field] = value.split ','
            end
          }
        }
        @data[driver_for_type(type)][type] += hashes
      end
      save
    end

    def delete!()
      File.delete(@file)
    end
  end
end
