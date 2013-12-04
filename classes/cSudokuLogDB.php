<?php
/**
 * Created by PhpStorm.
 * User: Павел
 * Date: 29.11.13
 * Time: 16:10
 */

class cSudokuLogDB {

	//Временная unix-метка начала
	private $time_start;

	//Временная юникс-метка конца
	private $time_end;

	private $memory_start;

	private $max_memory;

	//Целое число от 1 до 4. Подробности см. в базе
	private $result_status = "NULL";

	private $input_str;

	private $result;

	private $nesting_level = "NULL";

	private $db;

	//Порядковый номер задачи
	private $id_task;

	public function __construct(){
		$this->db = cDataBase::getInstance();
	}

	private function set_nesting_level(){
		global $nesting_level;
		if ($nesting_level)
			$this->nesting_level = $nesting_level;
	}

	public function set_result_status($val){
		$this->result_status = in_array($val,array(1,2,3,4)) ? $val : "NULL";
	}

	public function start_log($input_str){
		$this->input_str = $input_str;
		$this->id_task = $this->write_start_record();
		$this->time_start = microtime(1);
		$this->memory_start = memory_get_usage(true);
	}
	
	public function end_log($result, $result_status = null){
		$this->set_nesting_level();

		$this->time_end = microtime(1);

		if (!is_null($result_status))
			$this->result_status = $result_status;

		$this->max_memory = memory_get_peak_usage(true);
		$this->result = $result;
		$this->write_end_record();
	}

	private function write_start_record(){
		$this->db->non_select_query("
			INSERT
				INTO tasks
					(
						date_add,
						input_str
					)
				VALUES
					(
						now(),
						'".addslashes($this->input_str)."')
		");

		$row = $this->db->select_first_row_query("
			SELECT
				id_task
			FROM
				tasks
			ORDER BY
				date_add DESC
			LIMIT 0,1
		");

		return $row['id_task'];
	}

	private function write_end_record(){
		$this->db->non_select_query("
			INSERT
				INTO tasks_results
					(
						id_task,
						result,
						time,
						memory,
						nesting_level,
						result_type
					)
				VALUES
					(
						$this->id_task,
						'$this->result',
						".round(($this->time_end - $this->time_start)*1000).",
						".($this->max_memory - $this->memory_start).",
						$this->nesting_level,
						$this->result_status
					)

		");
	}
}

//TODO Сделать разделы "О программе", "MVC-каркас",
//TODO Выложить на github