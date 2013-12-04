<?php

class cAjax extends cController {
	public function __construct(){
		parent::__construct(__CLASS__);
		unset($this->template);
	}

	public function get_sudoku_solution(){

		$input_sudoku_string = isset($_REQUEST['str']) ? $_REQUEST['str'] : '';

		$dblog = new cSudokuLogDB();
		$dblog->start_log($input_sudoku_string);

		$sudoku_task = new cSudokutask($input_sudoku_string);

		try{
			$solut_result = $sudoku_task->get_solution(); // Возвращает либо массив с полем, либо строку 'nosolution';
			$response_array = array('solution' => $solut_result);
			if ($solut_result != 'nosolution'){
				$response_array['array81'] = $sudoku_task->get_array81();
				$dblog->set_result_status(1);
			}
			else
				$dblog->set_result_status(2);


		}catch (Exception $e){
			ob_get_clean();
			$response_array = array('error' => $sudoku_task->get_last_error(), 'array81' => $sudoku_task->get_array81());
			$solut_result = 'incorrect'; //Типо неверные начальные условия
		}

		//Добавим в базу запись о результате.
		$dblog->end_log($solut_result,$sudoku_task->get_error_status());

		//В тестовой версии отдадим браузеру текст буфера.
		if (!WORKING_VERSION)
			$response_array['buffer'] = $sudoku_task->get_buffer();

		$this->ajax_response($response_array);

	}

	private function ajax_response($arr){
		echo json_encode($arr);
		die;
	}

}