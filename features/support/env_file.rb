
class NinjaEnvFile
  def initialize(path)
    if File.exist? path
      raise "Cowardly refusing to overwrite existing env file #{path}"
    end

    @path = path
  end

  def add(var, val)
    File.open(@path, 'a') { |f|
      f.write("#{var}=#{val}\n")
    }
  end

  def delete()
    File.unlink @path
  end
end
