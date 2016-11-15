<?php

/**
 * Manages the small quicklinks located in the top & middle of the main menu
 * bar.
 */
class Quicklink_Controller extends Authenticated_Controller {

	/**
	 * Renders a form for adding and removing quicklinks.
	 */
	public function index() {
		$all_quicklinks = array();
		$stored_quicklinks = Ninja_setting_Model::fetch_page_setting("dojo-quicklinks", "tac");
		if($stored_quicklinks) {
			$all_quicklinks = json_decode($stored_quicklinks->setting, true);
			if($all_quicklinks === null) {
				// let's not dig into the error, it will
				// still require intervention from someone
				// at OP5 to fix it anyways
				throw new Exception("Could not restore the ".
					"stored quicklinks for the user ".
					"'$username'. Please contact OP5 ".
					"Support, this may be a bug.");
			}
		}

		$index_url = LinkProvider::factory()->get_url("quicklink", "index");
		$form = new Form_Model($index_url);
		$form->add_field(new Form_Field_Text_Model("href", "URI"));
		$form->add_field(new Form_Field_Text_Model("title", "Title"));
		$form->add_field(new Form_Field_Option_Model("target", "Open in", array(
			"" => "This window",
			"_blank" => "New window",
		)));
		$form->add_field(new Form_Field_Icon_Model("icon", "Icon"));
		//$form->add_field(new Form_Field_List_Model("remove", "Remove selected quicklinks"));

		$form->add_button(new Form_Button_Confirm_Model("save", "Save"));
		$form->add_button(new Form_Button_Cancel_Model("close", "Close"));
		// $form->set_values("stored stuff"); // present this as a
		// list, just as we do with shared dashboards, that have one
		// "x" button each

		if($_POST) {
			try {
				$result = $form->process_data($_POST);
			} catch(FormException $e) {
				$this->template = json::fail_view($e->getMessage());
				return;
			}
			$all_quicklinks[] = $result;
			Ninja_setting_Model::save_page_setting(
				"dojo-quicklinks",
				"tac",
				json_encode($all_quicklinks)
			);
			$this->template = json::ok_view("Your new quicklink '".$result["title"]."' was saved");
			return;
		}

		$quicklink_view = new View("quicklink/list");
		$quicklink_view->quicklinks = $all_quicklinks;

		$this->template = new View('concat');
		$this->template->views = array(
			$form->get_view(),
			$quicklink_view,
		);
	}

	/**
	 * Remove a quicklink after verifying that it exists.
	 */
	public function delete_quicklink() {
		if(!$_POST) {
			$this->template = json::fail_view("You must POST in order to delete a quicklink");
			return;
		}
		$stored_quicklinks = Ninja_setting_Model::fetch_page_setting(
			"dojo-quicklinks", "tac");
		$all_quicklinks = json_decode($stored_quicklinks->setting, true);
		if(!$stored_quicklinks || !$all_quicklinks) {
			$this->template = json::fail_view("You do not have ".
				"any quicklinks, cannot delete anything");
			return;
		}
		if(!isset($_POST["title"], $_POST["href"])) {
			$this->template = json::fail_view("You need to ".
				"submit both a title and a href in order ".
				"to delete a quicklink");
			return;
		}
		// This operation could be idempotent, but let's just check if
		// something was affected or not, in order to provide sensible
		// output to the user.
		$old_count = count($all_quicklinks);

		// Since quicklinks do not use IDs, we remove every quicklink
		// associated with the same title and the same href.
		$all_quicklinks = array_filter($all_quicklinks, function($q) {
			return $q["title"] != $_POST["title"] ||
				$q["href"] != $_POST["href"];
		});
		if($old_count == count($all_quicklinks)) {
			$this->template = json::fail_view(sprintf("Could not find any quicklink with the title '%s' and the URI '%s'", $_POST["title"], $_POST["href"]));
			return;
		}
		Ninja_setting_Model::save_page_setting(
			"dojo-quicklinks",
			"tac",
			json_encode($all_quicklinks)
		);
		$this->template = json::ok_view(array("title" => $_POST["title"], "href" => $_POST["href"]));
	}
}
