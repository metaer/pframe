<?php

final class cMain extends cController
{

	/**
	 * @var cMainModel;
	 */
	protected $model;

	public function __construct(){
		parent::__construct(__CLASS__);
		$this->add_bootstrap();
	}

	public function index(){
		$template = &$this->template;

		$template->set('title','Решатель судоку');
		$template->set('active','main');

		$this->template->add_js_css('/js/main.js');

		$this->add_noty();

		$template->display(null,null,false);
	}


}
