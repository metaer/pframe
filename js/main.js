

glob__hint_about_tab_showed = false;

$(function(){

	$('#prog_bar').css('visibility','hidden');

	revert_visibility_of_insert_button();

	clear_field();

	set_background_small_squares();

	cell(1).trigger('focus');

	$('.sudoku_field input').on('keydown',
		function(e){

			e = e || event;
			code = e.keyCode;
			index = e.target.tabIndex; //Текущий номер ячейки
			current_cell = cell(index); //Текущая ячейка

			if(code == 48 || code >= 65 && code <= 90 || code >= 106 && code <= 111 || code >= 186 && code <= 222){
				return false;
			}
			//Если цифра (кроме 0)
			else if (code >= 49 && code <= 57){

				//Покажем подсказку
				show_hint_about_tab();

				current_cell.val(code - 48);

				go_to_next_cell(index);

				return false;
			}
			//Если пробел, переходим на след. ячейку
			else if (code == 32){

				current_cell.val('');

				go_to_next_cell(index);

				return false;
			}
			//Если backspace
			else if (code == 8){
				//Если в ячейке есть цифра
				if (current_cell.val()){
					current_cell.val('');
					return false;
				}
				//Если ячейка пустая
				else{
					go_to_previous_cell(index);
					cell(index-1).val('');
				}
			}
		}
	);

	$('#clear_field_button').on('click',function(){clear_field();});
	$('#solve_task').on('click',function(){ switch_progress_animation('on'); solve_task(); });
	$('#insert_random_task').on('click',function(){insert_random_task()});
});

function set_background_small_squares(){
	var indexes = [1,2,3,10,11,12,19,20,21,7,8,9,16,17,18,25,26,27,31,32,33,40,41,42,49,50,51,55,56,57,64,65,66,73,74,75,61,62,63,70,71,72,79,80,81];
	var color = '#F7F3FA';
	for (index in indexes){
		cell(indexes[index]).css('background-color',color);
		cell(indexes[index]).parent().css('background-color',color);
	}
}

function cell(number){
	return $("[tabindex = "+number+"]");
}

function clear_field(){
	$('.sudoku_field input').val('');
	$('.sudoku_field input').css('color','');
}

function show_hint_about_tab(){
	if (!glob__hint_about_tab_showed){
		noty({text: "Подсказка: Используйте ПРОБЕЛ, чтобы пропустить ввод цифры; BACKSPACE, чтобы стереть цифру и перейти к предыдущему полю", layout:'topCenter', type: 'alert', timeout: 8850, dismissQueue: true});
		glob__hint_about_tab_showed = true;
		revert_visibility_of_insert_button();
	}
}

function solve_task(){

	noty({text: "Мы начинаем решать задачу. Максимальное время ожидания: 90 секунд", layout:'topCenter', type: 'information', timeout: 4000, dismissQueue: true});

	var str = read_field_to_string();

	$.ajax({
		type: "GET",
		url: "/ajax/get_sudoku_solution",
		dataType: "json",
		cache:false,
		timeout: 90000,
		async: true,
		data: {str: str},
		error: function(JqXhr, TextStatus, ErrorThrown)
		{
			switch_progress_animation('off');

			if (TextStatus == 'parsererror'){
				noty({text: 'Ошибка на сервере. Скорей всего лошара-программист что-то не предусмотрел. Ему уже отправлено уведомление о баге, и в течение ближайшего часа он возьмется за устранение ошибки.', layout:'topCenter', type: 'error', timeout: 14000, dismissQueue: true});
			}
			else if(TextStatus == 'timeout'){
				noty({text: 'Сервер не дал ответа в течение полутора минут. Либо задача настолько сложная, что этого времени не хватило на её решение, либо сервер временно прилег. Повторите попытку чуть позже. Программисту будет отправлено уведомление, и он обязательно разберется в чем дело.', layout:'topCenter', type: 'error', timeout: 16000, dismissQueue: true});
			}
		}

	}).done(function(result) {
		switch_progress_animation('off');
		if (result.solution){
			if (result.solution == 'nosolution'){
				noty({text: "Задача не имеет решения", layout:'topCenter', type: 'information', timeout: 2500, dismissQueue: true});
			}
			else{
				insert_string_to_field(result.solution);
				load_colors(result.array81);
				noty({text: "Задача успешно решена", layout:'topCenter', type: 'success', timeout: 2500, dismissQueue: true});
			}
		}
		else{
			noty({text: result.error, layout:'topCenter', type: 'error', timeout: 5000, dismissQueue: true});
			load_colors(result.array81);
		}
	});


}

function switch_progress_animation(param){
	switch (param){
		case 'on':
			$('#prog_bar').addClass('progress_anim');
			$('#prog_bar').css('visibility','visible');
			$('.btn').attr('disabled','disabled'); //Блочим кнопки на время решения.
			break;
		case 'off':
			$('#prog_bar').removeClass('progress_anim');
			$('#prog_bar').css('visibility','hidden');
			$('.btn').removeAttr('disabled');
	}
}

function colors(array81val){
	switch (array81val){
		case 'error':
			return 'red';
		case 'solution':
			return 'blue';
		case 'initial':
			return '';
	}
}

function load_colors(array81){
	for (var index in array81){
		var val = array81[index];
		cell(index).css('color',colors(val));
	}
}

function insert_random_task(){
	clear_field();
	str = example_tasks()[getRandomInt(0,example_tasks().length - 1)];
	insert_string_to_field(str);
}

function go_to_next_cell(index){
	cell(index + 1).trigger('focus');
}

function go_to_previous_cell(index){
	cell(index - 1).trigger('focus');
}

function example_tasks(){
	examples = [
		'.4...2.19...351.8631..947...94.....7.........2.....89...952..4142.169...16.8...7.',
		'9....24...5...1.8......3.16..2......1.7.6........17.63..5.78.......24..17.......9',
		'......8...67......4....2.317.3.965.4.............4...7..49...12..948.6...5...1...',
		'.1.6.7..4.42......87.3..6...8..7..2....893....3..6..1...8..6.45......17.4..9.8.6.',
		'.....5..................9..2..6....1....7..............8..3.................4....',
		'4..6..3.2.6.834...7...521..5...4.8...14...73...7.9...4..957...8...486.5.8.6..9..3',
		'.321.....4........5....2..3.67..3..9...8.4..6...9.5..1321..6..5.....7..4......83.'
	];
	return examples;
}

function getRandomInt(min, max)
{
	rand = Math.floor(Math.random() * (max - min + 1)) + min;
	if (rand > max)
		rand = max;
	return rand;
}

function insert_string_to_field(str){
	for (var key in str) {
		var val = str[key];
		index = key*1 + 1;
		cell(index).val(val != '.' ? val : '');
	}
}

function read_field_to_string(){
	str='';
	for (i = 1; i <= 81; i++)
		str += cell(i).val() == '' ? '.' : cell(i).val();

	return str;
}

function revert_visibility_of_insert_button(){
	var button = $('#insert_random_task');

	if (button.css('visibility') == 'hidden')
		button.css('visibility','visible');
	else
		button.css('visibility','hidden');
}