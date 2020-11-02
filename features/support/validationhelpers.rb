require 'open3'
module ValidationHelpers
  def tidy(html)
    exceptions = []
    Open3.popen3("tidy -qi") {|stdin, stdout, stderr|
      stdin.write(html)
      stdin.close()
      stdout.close()
      exceptions = stderr.read.split("\n").select{|line|
        !line.match("Warning: trimming empty") &&
        !line.match("proprietary attribute ")
      }
      stderr.close()
    }
    return exceptions
  end
end
