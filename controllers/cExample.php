<?php

final class cExample extends cController
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

        $template->set('title','Движок pframe');

        $this->template->add_js_css('/js/main.js');

        $template->display(null,null,false);
    }

}