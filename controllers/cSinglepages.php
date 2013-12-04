<?php

class cSinglepages extends cController {

	/**
	 * @var cSinglepagesModel;
	 */
	protected $model;

	public function __construct(){
		parent::__construct(__CLASS__);
		$this->add_bootstrap();
	}

	public function about(){
		$this->template->set('title','О программе');
		$this->template->set('active','about');
		$this->template->display('about.phtml');
	}

	public function example(){
		$template = &$this->template;

		$template->set('title','Пример');
		$template->display('example.phtml');
	}
}