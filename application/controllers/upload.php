<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller to handle widget upload
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Upload_Controller extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$auth = Nagios_auth_Model::instance();
		if (!$auth->view_hosts_root) {
			# redirect to default start page if not
			# properly authorized
			url::redirect(Kohana::config('routes.logged_in_default'));
		}
	}

	/**
	*	Index method
	*/
	public function index()
	{
		$this->template->content = $this->add_view('upload/index');
		$content = $this->template->content;
		$this->template->title = $this->translate->_('Widget Upload');
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_js[] = $this->add_path('upload/js/upload.js');
		$this->xtra_css[] = $this->add_path('upload/css/upload.css');
		$this->template->css_header->css = $this->xtra_css;
		$this->template->js_header->js = $this->xtra_js;
	}

	/**
	*	Take careof the uploaded widget file
	*/
	public function handle_upload()
	{
		$this->template->content = $this->add_view('upload/uploaded');
		$ct = $this->template->content;
		$this->template->title = $this->translate->_('Widget Upload');
		$this->template->disable_refresh = true;

		if (!isset($_FILES['upload_file'])) {
			url::redirect(Router::$controller.'/file_upload');
		}

		# assumes upload directory exists with read/write permissions
		$savepath = Kohana::config('upload.directory');

		if (!upload::valid($_FILES['upload_file']) || !upload::type($_FILES['upload_file'], array('zip'))) {
			$ct->err_msg = $this->translate->_("Uploaded file doesn't seem to be valid - aborting.");
			return;
		}

		$file = $_FILES['upload_file'];

		upload::save($file, $file['name'], $savepath);

		$widget_name = false;
		$folders = array();
		$files = array();
		$manifest = false;
		$friendly_name = false;

		$zip = zip::instance($savepath.$file['name']);
		if (($list = $zip->listContent()) == 0) {
			$ct->err_msg = sprintf($this->translate->_("Error: %s"), $zip->errorInfo(true));
			unlink($savepath.$file['name']);
			return;
		}

		foreach ($list as $index => $content) {
			if (strstr($content['filename'], '__')) {
				continue;
			}
			if ($content['index'] == 0 && $content['folder'] == 1) {
				# stash name
				$widget_name = str_replace('/', '', $content['filename']);
			}
			if ($content['folder'] == 1) {
				$folders[] = $content['filename'];
			} else {
				$files[] = $content['filename'];
			}
		}

		$level = 0;
		$errors = false;
		$erray = false;
		$classfile = false;
		if (!empty($folders)) {
			# if zipfile is v 1
			foreach ($folders as $f) {
				$level++;
				foreach ($files as $c) {
					$c_name = str_replace($f, '', $c);
					if (strstr($c_name, '__')) {
						continue;
					}

					$fileinfo = pathinfo($c_name);

					if (!strstr($c_name, '/')) {
						# found a file
						if ($level == 1 && $fileinfo['extension'] == 'php') {
							# Check for the class file
							if ($c_name == $widget_name.'.'.$fileinfo['extension']) {
								$classfile = $c_name;
							}
						} elseif ($level == 1 && $fileinfo['extension'] == 'xml') {
							# should be xml manifest file
							$manifest = $c_name;
						}
					}
				}
			}
		} else {
			# if zipfile is v 2
            $widget_name = strtolower($file['name']);
            $widget_name = str_replace('.zip', '', $widget_name);
            foreach($files as $c) {
                if ($c == $widget_name.'/'.$widget_name.'.php') {
                    $classfile = $widget_name.'.php';
                }
                if($c == $widget_name.'/manifest.xml') {
                    $manifest = 'manifest.xml';
                }
            }
        }

		if (empty($manifest)) {
			$errors++;
			$erray[] = $this->translate->_('Found no manifest file');
		}
		if (empty($classfile)) {
			$errors++;
			$erray[] = $this->translate->_('Found no class file');
		}

		$msg = '';
		$ct->widget_name = $this->translate->_('Widget name').': '.$widget_name."<br />";
		if (empty($errors) && !empty($classfile)) {
			#$msg .= sprintf($this->translate->_("Initial checks turned out ok - Unpacking...%s"), '<br />');
		} else {
			$ct->err_msg = sprintf($this->translate->_("Found %s errors:"), $errors);
			$ct->erray = $erray;
			unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		}

		if (!$list = $zip->extract(PCLZIP_OPT_PATH, $savepath)) {
			$ct->err_msg = sprintf($this->translate->_("Error: %s"), $zip->errorInfo(true));
			unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		}

		# check widget class file
		$classfile = file($savepath.$widget_name.'/'.$widget_name.'.php');

		$is_correct_classname = false;
		foreach ($classfile as $line) {
			if (strstr($line, 'class ') && strstr($line, ' extends widget_Core')) {
				$line = str_replace('class', '', $line);
				$line = str_replace('{', '', $line);
				$line = str_replace(' extends widget_Core', '', $line);
				$line = trim($line);
				if ($line !== ucfirst($widget_name).'_Widget') {
					$errors++;
					$erray[] = $this->translate->_('Widget classname does not meet requirements');
				}
			}
		}

		if (!empty($erray)) {
			$ct->err_msg = sprintf($this->translate->_("Found %s errors:"), $errors);
			$ct->erray = $erray;
			unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		} else {
			#$msg .= sprintf($this->translate->_("Everything seems ok. Let's install this widget. %s"), '<br />');
		}

		# load manifest
		$xml = simplexml_load_file($savepath.$widget_name.'/'.$manifest);
		if ($xml === false) {
			$ct->err_msg = $this->translate->_('Unable to load manifest file');
			unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		}

		$friendly_name = (string)$xml->Friendly_name;
		$description = (string)$xml->description;
		$version = (string)$xml->version;
		$pagename = (string)$xml->page; # should be tac/index but we don't care for now

		$data = Ninja_widget_Model::get($pagename, $widget_name);

		$custom_dir = APPPATH.Kohana::config('widget.custom_dirname');

		$widget_ok = false;
		$is_upgrade = false;
		if ($data !== false) {
			# widget already exists - compare versions
			$manifestpath = $custom_dir.$widget_name.'/'.$manifest;
			if (!file_exists($manifestpath)) {
				# actually, it appears to be gone/broken, so let's upgrade
				$widget_ok = true;
			}
			else {
				$check_xml = @simplexml_load_file($manifestpath);
				if ($check_xml !== false) {
					$old_version = $check_xml->version;
					if ($version > $old_version) {
						$widget_ok = true;
					}
				}
			}
			$is_upgrade = $widget_ok;
		} else {
			$widget_ok = true;
		}

		if (!$widget_ok) {
			$ct->err_msg = $this->translate->_('Error: A widget by this name already exists');
			unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		}


		if (!is_writable($custom_dir)) {
			sprintf($this->translate->_('Widget custom dir (%s) is not writable - please modify and try again'), $custom_dir);
		}

		exec('cp -av '.$savepath.$widget_name.'/ '.$custom_dir, $output, $retval);

		if ($retval != 0) {
			$ct->err_msg = $this->translate->_('Error: Unable to copy widget');
			unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		}

		if (file_exists($savepath.$file['name']));
			unlink($savepath.$file['name']);
		self::_rrmdir($savepath.$widget_name);

		$save = Ninja_widget_Model::install($pagename, $widget_name, $friendly_name);
		if ($save || $is_upgrade) {
			$msg .= sprintf($this->translate->_("OK, saved widget to db%s"), '<br />');
		} else {
			$ct->err_msg = $this->translate->_("Unable to save widget - maybe it's already installed?");
			if (file_exists($savepath.$file['name']))
				unlink($savepath.$file['name']);
			self::_rrmdir($savepath.$widget_name);
			return;
		}

		$ct->final_msg = sprintf($this->translate->_('This widget should now be properly installed.%s
			Please reload Tactical overview and enable the widget in the widget menu.'), '<br />');
		$ct->msg = $msg;
	}


	/**
	* Simple method to recursively remove a
	* directory and all containing files
	*/
	public function _rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
				foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") {
						self::_rrmdir($dir."/".$object);
					} else {
						unlink($dir."/".$object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
}
