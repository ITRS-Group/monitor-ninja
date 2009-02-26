<?php defined('SYSPATH') or die('No direct script access.');
require_once('tbs_class_php5.php');

class Tbs_Core
{
	private static $TBS = null;

	public function __construct()
	{
		if(self::$TBS == null) $this->TBS = new clsTinyButStrong();
	}

	public function LoadTemplate($File, $HtmlCharSet='UTF-8')
	{
		return $this->TBS->LoadTemplate($File, $HtmlCharSet);
	}

	public function MergeBlock($BlockName, $Source)
	{
		return $this->TBS->MergeBlock($BlockName, $Source);
	}

	public function MergeField($BaseName, $X)
	{
		return $this->TBS->MergeField($BaseName, $X);
	}

	public function Show()
	{
		$this->TBS->Show();
		return $this->TBS->Source;
	}
}
