<?php

class cAjax extends cController {
    public function __construct(){
        parent::__construct(__CLASS__);
        unset($this->template);
    }

    private function ajax_response($arr){
        echo json_encode($arr);
        die;
    }

}