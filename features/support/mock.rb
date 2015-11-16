require 'json'
require 'cucumber'
require 'tempfile'
module Op5Cucumber::Mock

  class Mock
    def initialize()
      @file = Dir::Tmpname.make_tmpname('/tmp/mock', nil)
      puts "Initializing mock data in ... #{@file}"
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
      #ENV['OP5_MOCK']
      true
    end

    def driver_for_type(type)
      'ORMDriverLS default'
    end

    def save()
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

    def filename
      @file
    end
  end
end
