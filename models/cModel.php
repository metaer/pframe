<?php

class cModel{

	protected $db;

	public function __construct(){
		$this->db = cDataBase::getInstance();
	}
}
