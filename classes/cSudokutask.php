<?php

class cSudokutask {

	private $buffer;

	private $input_str;

	private $last_error;

	//Код ошибки. 3 - неверные начальные условия судоку; 4 - неверная начальная строка.
	private $error_status;

	/**
	 * @var Массив, который содержит инфу о цветах с первой до 81й ячейки. initial - дефолтный цвет (в нашем случае черный), error - красный, solution - синий.
	 */
	private $array81;

	public function __construct($input_str){

		$this->input_str = $input_str;
	}

	public function get_solution(){

		$this->verify_input_string();

		require_once BASE_DIR.'/includes/sudoku_procedure_style.inc.php';

		global $nesting_level, $pole;

		$nesting_level = 0;
		$pole = $this->str_to_array($this->input_str);

		$this->verify_field($pole);

		//Перехватим выходной поток
		ob_start();

		$sud_result = solve_sudoku(1,$pole); //Если нет решения - возвращает строку 'nosolution', если есть - поле с массивом

		$this->buffer = ob_get_clean();

		if (is_array($sud_result))
			$sud_result = $this->array_to_str($sud_result);

		if ($sud_result != 'nosolution')
			$this->make_array81_success();

		return $sud_result;


	}

	//Сюда передается массив столбцов
	private function verify_field($pole){
		$this->verify_rows($pole);
		$this->verify_cols($pole);
		$this->verify_small_squares($pole);
	}

	private function verify_rows($pole){
		for ($j=1;$j<=9;$j++){
			$string = "";
			for ($i=1;$i<=9;$i++){
				$a = ($pole[$i][$j]=="" ? " " : $pole[$i][$j]);
				if (substr_count($string,$a)>0){

					$this->last_error = "Неверные начальные условия.<br>Ошибка в ячейке:<br>Стоблец № {$i}<br> Строка № {$j}<br>Цифра {$a} уже встречается в строке {$j}<br>";

					$this->make_array81_error('row',$j);

					$this->error_status = 3;
					throw new Exception();

				}
				$string = $string.$pole[$i][$j];
			}
		}

		return true;
	}

	private function verify_cols($pole){
		for ($i=1;$i<=9;$i++){

			$string = "";
			for ($j=1;$j<=9;$j++){
				$a = ($pole[$i][$j]=="" ? " " : $pole[$i][$j]);
				if (substr_count($string,$a)>0){

					$this->last_error = "Неверные начальные условия.<br>Ошибка в ячейке:<br>Стоблец № {$i}<br> Строка № {$j}<br>Цифра {$a} уже встречается в колонке {$i}<br>";

					$this->make_array81_error('col',$i);

					$this->error_status = 3;
					throw new Exception();
				}
				$string = $string.$pole[$i][$j];
			}
		}

		return true;
	}

	private function verify_small_squares($pole){

		for ($z=1;$z<=9;$z++){
			$x=1+3*($z-1-((div($z-1,3))*3));
			$y=1+div($z-1,3)*3;
			$string = "";
			for ($i=$x;$i<=$x+2;$i++){
				for ($j=$y;$j<=$y+2;$j++){
					$a = ($pole[$i][$j]=="" ? " " : $pole[$i][$j]);
					if (substr_count($string,$a)>0){

						$this->last_error = "Неверные начальные условия.<br> В малом квадрате № {$z} имеются одинаковые цифры<br>";

						$this->make_array81_error('square',$z);

						$this->error_status = 3;
						throw new Exception();

					}
					$string = $string.$pole[$i][$j];

				}
			}


		}
		return true;
	}

	/**
	 * Проверяет на предмет содержания только точек, цифр от 1 до 9 и длины 81 символ.
	 * Не проверяет правила задачи судоку
	 */
	private function verify_input_string(){
		$symbols = preg_replace('/[1-9.]/','',$this->input_str); //Заменяем все разрешенные символы пустой строкой. Если остались какие-то символы, значит нам передали хренотень
		$condition_ok = (strlen($this->input_str) == 81) && $symbols === '';

		if (!$condition_ok){
			$this->last_error = 'Неверная входная строка. Строка должна состоять из 81 символа и содержать только цифры от 1 до 9 и точки';
			$this->error_status = 4;
			throw new Exception();
		}

	}

	/**
	 * Переводит судоку-строку в суддоку-массив
	 */
	private static function str_to_array($str){

		$a = 0;
		$field = array();

		for ($y = 1; $y <= 9; $y++){
			for ($x = 1; $x <= 9; $x++){
				$field[$x][$y] = $str[$a] == '.' ? "" : $str[$a];
				$a++;
			}
		}

		return $field;
	}

	/**
	 * Переводит судоку-массив в судоку-строку. Массив судоку должен состоять из массивов столбцов. (тогда горизонтальная координата будет первой при обращении через $arr[$x][$y]
	 * Возвращаемая строка состоит из строки, которая является конкатенацией рядов судоку-поля (1й ряд, потом 2й ряд, ...)
	 */
	private static function array_to_str($arr){

		$str = '';

		for ($y = 1; $y <= 9; $y++){
			for ($x = 1; $x <= 9; $x++)
				$str.=$arr[$x][$y] ?: ".";
		}

		return $str;
	}

	public function get_input_str(){
		return $this->input_str;
	}

	public function get_buffer(){
		return $this->buffer;
	}

	private function make_array81_success(){
		for ($i = 1; $i <= 81; $i++){
			$v = $this->input_str[$i-1];
			$arr[$i] = is_numeric($v) ? 'initial' : 'solution';
		}
		$this->array81 = $arr;
	}

	/**
	 * Делает массив81 из верификации рядов, или колонок или малых квадратов (чтоб потом на сайте выделять красным ряд, или там квадрат)
	 * @param $type тип
	 * @param $num
	 */
	private function make_array81_error($type,$num){
		switch ($type){
			case 'square':
				$top_left_index = $this->get_top_left_index_by_small_square_number($num);
				for ($i = $top_left_index; $i<=$top_left_index + 2; $i++){
					for ($j = $i; $j != 27 + $i; $j+=9){
						$indexes[] = $j;
					}
				}

				foreach($indexes as $index){
					$arr[$index] = 'error';
				}

				break;
			case 'col':
				for ($i = $num; $i != 81+$num ; $i+=9)
					$arr[$i] = 'error';

				break;

			case 'row':
				for($i = $num * 9 - 8 ; $i <= $num*9; $i++)
					$arr[$i] = 'error';

		}

		//Теперь допишем начальные цифры
		for ($i = 1; $i <= 81; $i++){
			if (isset($arr[$i]))
				continue;
			if (is_numeric($this->input_str[$i-1])){
				$arr[$i] = 'initial';
			}

		}

		$this->array81 = $arr;

	}

	private function get_top_left_index_by_small_square_number($num){
		$conformity = array(
			1=>1,
			2=>4,
			3=>7,
			4=>28,
			5=>31,
			6=>34,
			7=>55,
			8=>58,
			9=>61
		);

		return $conformity[$num];
	}

	public function get_array81(){
		return $this->array81;
	}

	public function get_last_error(){
		return $this->last_error;
	}

	public function get_error_status(){
		return $this->error_status;
	}
}

