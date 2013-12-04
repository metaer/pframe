<?php

class cTemplate
{
	//Массив с файлами, которые браузер может брать из своего кеша.
	private $array_allow_js_css_cache;

	private $default_wrapper_template;

	private $default_internal_template;

	private $wrapper_template;

	private $internal_template;

	private $additional_js = array();

	private $additional_css = array();

	private $data = array();

	private $short_controller_name;

	//Выделенная кнопка в меню (текущий раздел)
	private $active;

	public function __construct($controller){
		$this->short_controller_name = strtolower(substr($controller,1));
		$this->default_wrapper_template = 'general/default_wrapper.phtml';
		$this->default_internal_template = $this->short_controller_name.'.phtml';

		global $__glob_array_allow_js_css_cache;
		$this->array_allow_js_css_cache = $__glob_array_allow_js_css_cache;
	}

	private function add_js_css_item($jc){
		$parts = explode('.',$jc);
		$this->{'additional_'.$parts[sizeof($parts)-1]}[] = $jc;
	}

	public function add_js_css($jc){
		if (!is_array($jc))
			$this->add_js_css_item($jc);
		else{
			foreach ($jc as $jc_item)
				$this->add_js_css_item($jc_item);
		}
	}

	public function set($name, $val){
		$this->data[$name] = $val;
	}

	public function multi_set($arr_names,$arr_values){
		if (count($arr_names) != count($arr_values))
			trigger_error('Не совпадает количество в массивах (параметры multi_set)',E_USER_WARNING);


		foreach($arr_names as $k => $name)
			$this->set($name,$arr_values[$k]);
	}

	/**
	 * @param null|string $internal_template файл шаблона (внутренняя часть)
	 * @param null|string $wrapper_template файл шаблона (обертка)
	 * @param bool $include_base_js включать или нет js совпадающий с именем контроллера
	 * @param bool $include_base_css включать или нет css совпадающий с именем контроллера
	 */
	public function display($internal_template = null, $wrapper_template = null, $include_base_js = true, $include_base_css = true){

		if (!is_null($internal_template) && file_exists(TEMPLATES_PATH.$internal_template))
			$this->internal_template = $internal_template;
		else
			$this->internal_template = $this->default_internal_template;

		if (!is_null($wrapper_template) && file_exists(TEMPLATES_PATH.$wrapper_template))
			$this->wrapper_template = $wrapper_template;
		else
			$this->wrapper_template = $this->default_wrapper_template;

		$this->internal_template = TEMPLATES_PATH.$this->internal_template;
		$this->wrapper_template = TEMPLATES_PATH.$this->wrapper_template;

		extract($this->data);

		$aajc = &$this->array_allow_js_css_cache;

		require $this->wrapper_template;
	}

}
