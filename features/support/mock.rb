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
      'ORMDriverLS default'
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
      hashes.each {|hash|
        hash.map {|field, value|
          if field.end_with? 's'
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
