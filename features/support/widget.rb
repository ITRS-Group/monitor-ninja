require 'fileutils'
module Op5Cucumber::Widget
  class Widget
  end

  class BrokenWidget < Widget
    attr_accessor :widget_code, :widget_dir, :widget_file

    def widget_code
      <<-WIDGET
<?php
class Broken_Widget extends widget_Base {
  public function __construct($model) {
    throw new Exception("%s");
  }
}
      WIDGET
    end

    def widget_dir
      "modules/monitoring/widgets/broken"
    end

    def widget_file
      "broken.php"
    end

    def initialize(message)
      Dir::mkdir(File.join(NINJA_ROOT, widget_dir()))
      File.open(File.join(NINJA_ROOT, widget_dir(), widget_file()), "w"){|f|
        f.write(widget_code % [message])
      }
    end

    def delete!
      FileUtils::rm_rf File.join(NINJA_ROOT, widget_dir)
    end
  end

  class UnrenderableWidget < BrokenWidget

    def widget_dir
      "modules/monitoring/widgets/unrenderable"
    end

    def widget_file
      "unrenderable.php"
    end

    def widget_code
      <<-WIDGET
<?php
class Unrenderable_Widget extends widget_Base {
  public function index() {
    throw new Exception("%s");
  }
}
      WIDGET
    end

  end

  class ExternalWidget < Widget
    def initialize(name, settings = {'height'=>"600"})
      @cfg_file = File.join NINJA_ROOT, "application/config/custom/external_widget.php"
      config = <<-CONFIG
<?php
$config['widget_name'] = '#{name}';
$config['username'] = "monitor";
$config['groups'] = array('admins', 'guests');
$config['widgets'] = array();
$config['widgets']['#{name}'] = array(
  'name' => '#{name}',
  'friendly_name' => '#{name}',
  'setting' => array(
CONFIG
      File.open(@cfg_file, "w"){|f|
        f.write(config.strip)
        settings.map {|setting, value|
          f.write("'#{setting}' => '#{value}',")
        }
        f.write("\n));")
      }
    end

    def delete!
      File.delete(@cfg_file)
    end

  end
end
