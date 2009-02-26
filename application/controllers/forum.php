<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default Kohana controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Forum_Controller extends Controller {

	// Disable this controller when Kohana is set to production mode.
	// See http://docs.kohanaphp.com/installation/deployment for more details.
	const ALLOW_PRODUCTION = FALSE;

	// Set the name of the template to use
	#public $template = 'forum/test';

	public function index()
	{
		#$profiler = new Profiler;
		#$this->load->model('menu');
		#$menu = $this->menu->ListModules($dist_id, $arch_id);
		$table = array(
				array('host' => 'monitor', 'up' => '70', 'down' => '30'),
				array('host' => 'logserver', 'up' => '40', 'down' => '60'),
				array('host' => 'statistics', 'up' => '50', 'down' => '50'),
				array('host' => 'printer', 'up' => '0', 	'down' => '100'),
				array('host' => 'op5', 'up' => '100', 'down' => '0')
			);

		$data = array (
			'text' => 'hej hej',
			array(
					'name' => 'table',
					'data' => $table
			)
		);
		#$view = $this->_kohana_load_view('forum/test', $data);
		#$view = new View('forum/test');
		#$view->LoadTemplate('/var/www/kohana/application/views/forum/test.html.php');
		#$view->MergeBlock('table', $data);
		$view = $this->_kohana_load_view(DOCROOT . 'application/views/forum/test.html', $data);
		#$view = $this->load->view('forum/test', $data);
		$this->Show();

		// In Kohana, all views are loaded and treated as objects.
		#$this->template->content = new View('forum_content');

		// You can assign anything variable to a view by using standard OOP
		// methods. In my welcome view, the $title variable will be assigned
		// the value I give it here.
		#$this->template->title = 'Welcome to My o Per\'s Forum!';

		// An array of links to display. Assiging variables to views is completely
		// asyncronous. Variables can be set in any order, and can be any type
		// of data, including objects.
		#$this->template->content->links = array
		#(
		#	'Login'     => 'forum/login',
		#);
	}

	function login()
	{
		#$this->template->title = 'Welcome to My o Per\'s Forum!';
		// Anything submitted?
		if ($_POST)
		{
			// Merge the globals into our validation object.
			$post = Validation::factory($_POST);

			// Ensure upload helper is correctly configured, config/upload.php contains default entries.
			$post->add_rules('*', 'required');

			// Alternative syntax for multiple file upload validation rules
			//$post->add_rules('imageup.*', 'upload::required', 'upload::valid', 'upload::type[gif,jpg,png]', 'upload::size[1M]');

			if ($post->validate() )
			{
				#Auth_Core::login($_POST['user'], $_POST['passwd']);
				#Auth::instance()->login($_POST['user'], $_POST['passwd']);
				// It worked!
				#echo "Seems OK...";
				// Move (and rename) the files from php upload folder to configured application folder
				#upload::save('imageup1');
				#upload::save('imageup2');
				#echo 'Validation successfull, check your upload folder!';

				$user = ORM::factory('user');
					//form fields as $user attributes
				foreach ($_POST as $key => $val){
					if ($key == 'login') continue;
					$user->$key = $val;
				}
				if (Auth::instance()->login($_POST['username'], $_POST['password']))
				{

					//redirect to somewhere
					url::redirect('forum/start');
					#echo "Bump";
				}
			}
			else
			{
				// You got validation errors
				echo '<p>validation errors: '.var_export($post->errors(), TRUE).'</p>';
				#echo Kohana::debug($post);
			}
		}

		// Display the form
		echo form::open('forum/login');
		echo form::label('dologin', 'Login here').':<br/>';
		// Use discrete upload fields
		// Alternative syntax for multiple file uploads
		// echo form::upload('imageup[]').'<br/>';

		echo form::input('username').'<br/>';
		echo form::password('password').'<br/>';
		echo form::submit('login', 'Login!');
		echo form::close();

	}

	public function start($id=false)
	{
		#echo "tjobba";
		#print_r(Session::instance());
		#echo Auth::instance()->get_user();
		$db = new Database;
		$table = 'forum AS f, users AS u';
		echo '<h1>Testforum:</h1>';
		if ($db->table_exists('forum'))
		{
			$query = $db->select('f.*, u.username')->from($table)->where('f.user_id=u.id')->get();
			if (count($query)) {
				echo "<table class='box' cellpadding=1 cellspacing=1 border=0>
					<tr><th>ID</th><th>Date</th><th>Title</th><th>Message</th><th>User</th><th>&nbsp;</th></tr>";
				foreach ($query as $item)
				{
					echo "<tr>";
					echo "<td>".$item->id."</td>";
					echo "<td>".date('Y-m-d, H:i', $item->the_date)."</td>";
					echo "<td>".$item->title."</td>";
					echo "<td>".$item->msg."</td>";
					echo "<td>".$item->username."</td>";
					echo "<td>";
					echo form::open('forum/edit_post');
					echo form::submit('edit', 'edit');
					echo form::submit('delete', 'delete');
					echo form::hidden('id', $item->id);
					echo form::close();
					echo "</td>";
					echo "</tr>";
				}
				echo "</table>";
			}
			if ($id !== false) {
				$sql = 'SELECT f.*, u.username FROM '.$table.' WHERE f.id = '.$id;
				$row = $db->query($sql);
			}
			echo form::open('forum/add_post');
			echo form::label('dopost', 'Add message').':<br/>';
			echo "Title: ".form::input('title', isset($row) ? $row->current()->title : '').'<br/>';
			echo "Message: ".form::textarea('msg', isset($row) ? $row->current()->msg : '').'<br/>';
			echo form::submit('dopost', isset($row) ? 'Update' : 'Go!');
			if (isset($row)) {
				echo form::hidden('id', $id);
				echo form::submit('new', 'Cancel');
			}
			echo form::close();
		}
		else
		{
			echo 'NO! The '.$table.' table doesn\'t exist, so we can\'t continue =( ';
		}
		echo "<br/><br/>\n";
		echo 'done in {execution_time} seconds';

	}

	public function add_post()
	{
		if ($_POST)
		{
		// Merge the globals into our validation object.
			foreach ($_POST as $key => $val){
				if ($key == 'dopost') continue;
				$user->$key = $val;
			}
			$db = new Database;
			$table = 'forum';
			if (isset($user->id) & !isset($user->new)) {
				# update
				$update = $db->update('forum', array(
					'title' =>$user->title,
					'msg' => $user->msg,
					'user_id' =>Auth::instance()->get_user(),
					'the_date' => time()), array('id' => $user->id)
					);
			} elseif(isset($user->new)) {
				#echo "Show addbtn";
			} else {
				# insert
				$insert = $db->insert($table, array(
					'user_id' =>Auth::instance()->get_user(),
					'title' => $user->title,
					'msg' => $user->msg,
					'the_date' => time())
					);
					$rows = count($insert);
			}

			url::redirect('forum/start');
		}
	}

	public function edit_post()
	{
		if ($_POST) {
			foreach ($_POST as $key => $val){
				$user->$key = $val;
			}
			$db = new Database;
			$table = 'forum';
			if (isset($user->delete)) {
				# delete record
				$db->delete('forum', array('id' => $user->id));
				url::redirect('forum/start');
			} else {
				if (isset($user->edit)) {
					url::redirect('forum/start/'.$user->id);
				}
			}
		}
	}

	public function __call($method, $arguments)
	{
		// Disable auto-rendering
		$this->auto_render = FALSE;

		// By defining a __call method, all pages routed to this controller
		// that result in 404 errors will be handled by this method, instead of
		// being displayed as "Page Not Found" errors.
		echo 'This text is generated by __call. If you expected the index page, you need to use: welcome/index/'.substr(Router::$current_uri, 8);
	}

} // End Welcome Controller
