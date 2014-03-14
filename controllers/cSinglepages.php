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

    public function single(){
        $template = &$this->template;

        $template->set('title','Пример');
        $template->display('single.phtml');
    }
}