<?php

class cController
{

//TODO Написать комменты в файлах

    protected $model;

    protected $template;

    public function __construct($controller){

        $this->create_model($controller);

		if ($controller != 'cAjax')
        	$this->template = new cTemplate($controller);
    }

    private function create_model($controller){
        $model = $controller.'Model';
        require_once MODELS_PATH.$model.'.php';
        $this->model = new $model;
    }

	protected function add_bootstrap(){
		$this->template->add_js_css(array(
			'/public_libs/bootstrap/css/bootstrap.min.css',
			'/public_libs/jQuery/jquery-1.10.2.min.js',
			'/public_libs/bootstrap/js/bootstrap.min.js',
		));
	}

	protected function add_noty(){
		$this->template->add_js_css(array(
			'/public_libs/noty/jquery.noty.js',
			'/public_libs/noty/layouts/topCenter.js',
			'/public_libs/noty/themes/default.js'
		));
	}

}