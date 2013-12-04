<?php
//TODO Переделать всю эту галиматью в ООП стиль. Поменять названия переменных на нормальные.

/**
 * Возвращает true, если такой цифры нет в строке.
 * @param $k - цифра, которую проверяем на наличие
 * @param $j - строка поля
 * @param $pole поле
 * @return bool
 */
function not_figure_in_string($k,$j,$pole) {
	$temp_string = "";
	for ($z = 1; $z <=9; $z++) {
		$temp_string = $temp_string.$pole[$z][$j];
	}
	if (substr_count($temp_string, $k)>0){
		return false;
	}
	else{
		return true;
	}
}

function not_figure_in_column($k,$i,$pole) {
	$temp_string = "";
	for ($z = 1; $z <=9; $z++) {
		$temp_string = $temp_string.$pole[$i][$z];
	}
	if (substr_count($temp_string, $k)>0){
		return false;
	}
	else{
		return true;
	}
}

function not_figure_in_square($k,$i,$j,$pole) {
	$temp_string = "";
	for ($u = 3*div(($i-1),3)+1; $u <= 3*div(($i-1),3)+3; $u++) {
		for ($v = 3*div(($j-1),3)+1; $v <= 3*div(($j-1),3)+3; $v++){
			$temp_string = $temp_string.$pole[$u][$v];
		}
	}
	if (substr_count($temp_string, $k)>0){
		return false;
	}
	else{
		return true;
	}
}

function selection_figures(&$pole,$i,$j,&$variant){
	for ($k = 1; $k <=9; $k++) { // k - перебор цифр от 1 до 9 на предмет подходят ли в ячейку
		if (not_figure_in_string($k,$j,$pole) and not_figure_in_column($k,$i,$pole) and not_figure_in_square($k,$i,$j,$pole)){
			$variant[$i][$j]=$variant[$i][$j].$k;
		}
	}
}

function rez_obhoda(&$pole,&$nosolution) {
	$rez = false;
	$nosolution = false;

	for ($j = 1; $j <=9; $j++) {
		for ($i = 1; $i <=9; $i++){

			$variant[$i][$j]="";
			$curr_cell = &$pole[$i][$j];//текущая ячейка
			if (strlen($curr_cell)==0){
				selection_figures(&$pole,$i,$j,&$variant);//заполняем массив-строку вариантов
				if (strlen($variant[$i][$j]) == 1){
					$curr_cell=$variant[$i][$j];
					$rez=true;
				}
				elseif (strlen($variant[$i][$j]) == 0){
					$nosolution = true;
				}
				else{
				}
			}
		}
	}

	return $rez;
}

function task_solved($pole){
	foreach ($pole as $col) {
		foreach ($col as $cell) {
			if ($cell=="") return false;
		}
	}
	return true;
}

function GetStringCandidate($pole){
	for ($j = 1; $j <=9; $j++) {
		for ($i = 1; $i <=9; $i++){
			$variant[$i][$j]="";
			$curr_cell = $pole[$i][$j];//текущая ячейка
			if (strlen($curr_cell)==0){
				selection_figures(&$pole,$i,$j,&$variant);//заполняем массив-строку вариантов
			}
		}
	}

	for($min_candidate=2;$min_candidate<10;$min_candidate++){

		for ($j = 1; $j <= 9; $j++) {
			for ($i = 1; $i <= 9; $i++){
				if (strlen($variant[$i][$j])==$min_candidate){  //min_candidate - минимальное количество кандидатов
					return $variant[$i][$j].$i.$j;
				}
			}
		}

	}

	return "";
}

function put_figure($pole,$figure,$i,$j){
	$pole[$i][$j]=$figure;
	return $pole;
}

function change_id_leveldown($id,$number){
	return $id.$number;
}

function change_id_levelup($id){
	return substr($id,0,-1);
}

function solve_sudoku($id,$pole){

	do{
		$rez_obhoda=rez_obhoda(&$pole,&$nosolution);//истина - если было, что заполнить; nosolution - означает, что зашли в тупик
	}
	while ($rez_obhoda);

	if (task_solved($pole)){
		return $pole;
	}
	else{
		//echo "простым методом задача не решилась, переходим к части 2";

		global $tree, $candidate_ext, $amount_cand_pass, $nesting_level; // 1ая переменная - массив кандидатов на каждом уровне. Вторая - количество рассмотренных кандидатов на уровне

		if (!isset($amount_cand_pass[$id]))
			$amount_cand_pass[$id] = 0;

		if (!isset($tree[$id]))
			$tree[$id]=$pole;

		if (!isset($candidate_ext[$id])){
			//формируем строку кандидатов для текущего поля. Последние 2 цифры - номер ячейки (i;j)
			$candidate_ext[$id] = GetStringCandidate($tree[$id]);
		}

		if ((strlen($candidate_ext[$id])>2) and(!$nosolution)){//если кандидаты есть и мы не зашли в тупик

			if ($amount_cand_pass[$id]>0){//Если кандидаты на этом уровне уже рассматривались, то...
				if ($amount_cand_pass[$id]==strlen(($candidate_ext[$id]))-2){//Если рассматривались все кандидаты на текущем уровне
					if ($id=="1")//если текущий уровень = 1
						return 'nosolution';//Задача не имеет решения
					else{#если текущий уровень не равен 1
						$newid=change_id_levelup($id);
						$newpole = $pole;

						$j=substr($candidate_ext[$id],-1);
						$i=substr($candidate_ext[$id],-2,1);
						//$extinfo="На текущем уровне рассматривались уже все кандидаты.<br>Ячейка {$i};{$j}.<br> Список кандидатов: ".substr($candidate_ext[$id],0,-2)."<br>Было рассмотрено ".$amount_cand_pass[$id]." кандидатов. Поднимаемся на уровень выше";

						$nesting_level++;

						return solve_sudoku($newid,$tree[$newid]);
					}
				}
				else{//Если рассматривались ещё не все кандидаты на текущем уровне
					$amount_cand_pass[$id]++;//увеличиваем порядковый номер рассматриваемого кандидата
					$candidate = substr($candidate_ext[$id],0,-2);
					$figure = $candidate[$amount_cand_pass[$id]-1];
					$j=substr($candidate_ext[$id],-1);
					$i=substr($candidate_ext[$id],-2,1);
					$newpole=put_figure($tree[$id],$figure,$i,$j);//подставляем первого кандидата в ячейку i;j. В параметрах функции:поле,что подставлять,i,j
					$newid=change_id_leveldown($id,$amount_cand_pass[$id]);//параметры - id, порядковый номер кандидата

					$extinfo="Информация перед помещением кандидата в ячейку: На текущем уровне рассматривались ещё не все кандидаты<br>Ячейка {$i};{$j}.<br> Список кандидатов: ".substr($candidate_ext[$id],0,-2)."<br>Было рассмотрено ".$amount_cand_pass[$id]." кандидатов. Поместили в ячейку цифру {$figure}";

					$nesting_level++;

					return solve_sudoku($newid,$newpole);

				}
			}
			else{//Если кандидаты на этом уровне ещё не рассматривались
				$amount_cand_pass[$id]=1;
				$candidate = substr($candidate_ext[$id],0,-2);
				$figure = $candidate[0];
				$j=substr($candidate_ext[$id],-1);
				$i=substr($candidate_ext[$id],-2,1);
				$newpole=put_figure($tree[$id],$figure,$i,$j);//подставляем первого кандидата в ячейку i;j. В параметрах функции:поле,что подставлять,i,j
				$newid=change_id_leveldown($id,1);//параметры - id, порядковый номер кандидата

				$extinfo="Информация перед помещением кандидата в ячейку: На текущем уровне кандидаты не рассматривались<br>Ячейка {$i};{$j}.<br> Список кандидатов: ".substr($candidate_ext[$id],0,-2)."<br>Было рассмотрено ".$amount_cand_pass[$id]." кандидатов. Поместили в ячейку цифру {$figure}";

				$nesting_level++;

				return solve_sudoku($newid,$newpole);

			}

		}
		else{  #если кандидатов нет или мы зашли в тупик
			if ($id=="1"){//если текущий уровень = 1
				return 'nosolution';//Задача не имеет решения
			}
			else{#если текущий уровень не равен 1
				$newid=change_id_levelup($id);
				$newpole = $pole;

				//$extinfo="Кандидатов нет или мы зашли в тупик. Поднимаемся на уровень выше<br>Ячейка {$i};{$j}<br>";

				$nesting_level++;

				return solve_sudoku($newid,$tree[$newid]);
			}
		}
	}
}

function show_process($id,$pole,$extinfo){
	global $nesting_level;
	$nesting_level++;
	echo "<br><br><br>";
	vyvesty_pole($pole);
	echo "id поля выше: {$id}<br>";
	echo "уровень дерева поля выше: ".strlen($id);
	echo "<br>Функция solve_sudoku была выполнена {$nesting_level} раз. <br>";
	echo $extinfo;
	echo "<br><br><br>";
}

function pre_verify_row($pole) {

	for ($j=1;$j<=9;$j++){


		$string = "";
		for ($i=1;$i<=9;$i++){
			$a = ($pole[$i][$j]=="" ? " " : $pole[$i][$j]);
			if (substr_count($string,$a)>0){
				echo("Неверные начальные условия.<br>Ошибка в ячейке:<br>Стоблец № {$i}<br>
				Строка № {$j}<br>Цифра {$a} уже встречается в строке {$j}<br>");

				return false;
			}
			$string = $string.$pole[$i][$j];
		}
	}

	return true;
}

function pre_verify_col($pole) {

	for ($i=1;$i<=9;$i++){


		$string = "";
		for ($j=1;$j<=9;$j++){
			$a = ($pole[$i][$j]=="" ? " " : $pole[$i][$j]);
			if (substr_count($string,$a)>0){
				echo("Неверные начальные условия.<br>Ошибка в ячейке:<br>Стоблец № {$i}<br>
				Строка № {$j}<br>Цифра {$a} уже встречается в колонке {$i}<br>");

				return false;
			}
			$string = $string.$pole[$i][$j];
		}
	}

	return true;
}

function pre_verify_small_square($pole) {
	for ($z=1;$z<=9;$z++){


		$x=1+3*($z-1-((div($z-1,3))*3));
		$y=1+div($z-1,3)*3;
		$string = "";
		for ($i=$x;$i<=$x+2;$i++){
			for ($j=$y;$j<=$y+2;$j++){
				$a = ($pole[$i][$j]=="" ? " " : $pole[$i][$j]);
				if (substr_count($string,$a)>0){
					echo("Неверные начальные условия.<br> В малом квадрате № {$z} имеются одинаковые цифры<br>");

					return false;
				}
				$string = $string.$pole[$i][$j];

			}
		}


	}
	return true;
}

function div($a,$b){
	return floor($a/$b);
}

/**
 * @param $pole
 * @return bool
 */
function pre_verification_all_field($pole){
	$a=pre_verify_row($pole);
	$b=pre_verify_col($pole);
	$c=pre_verify_small_square($pole);
	return ($a and $b and $c);
}