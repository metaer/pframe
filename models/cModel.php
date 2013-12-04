<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Павел
 * Date: 21.09.13
 * Time: 18:05
 * To change this template use File | Settings | File Templates.
 */
class cModel{

    protected $db;

    public function __construct(){
        $this->db = cDataBase::getInstance();
    }
}
