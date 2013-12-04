<?php
/**
 * ����� ��� ������������� ������ ���������� ���������� ������� �� ���� � ����������, ���������, ������������.
 * � ����������. TODO ������� �� ��������� ������� (������� ���� ������)
 */

abstract class cTable{

	//���������� ����� ��������
	const DEFAULT_PAGE_NUMBER = 1;

	//������ �������������� ���� ������� ����������
	private $sort_order_names = array('back' => 'DESC','forward' => 'ASC');

	//���������� ���� ����������
	private $default_sort_field;

	//���������� ������� ����������
	private $default_sort_order;

	//�� ����� ����� ������ �������� ����������
	private $sorts;

	//���������� �������
	public $count_pages;

	//���������� ����� �� ��������
	protected $count_items_on_page;

	//����� ��������
	protected $page_number;

	//���� �������� �������
	public $rows;

	//����, �� �������� ���������
	protected $sort_field;

	//������� ����������
	protected $sort_order;

	//�������� � �����. ��� ��� ��������� ����, �.�. ���������� �������� ����� ���� true, � ������ ������� �� ������� � $_GET.
	protected $checkboxes;

	//������� �� ���������
	protected $default_filters;

	//������������ ������� �� $_GET
	public $real_filters;

	//������ ��� ������ � ��.
	protected $db;

	//����� � WHERE � sql �������
	protected $sql_filter;

	//� ������ ���� ������ ������� (� ����������� �� ������ �������� � ���-�� �� ���. �������� ���� 2�� ��� � �� 10 �� ���, �� � 11 ����)
	protected $row_from;

	//�� ����� ��� ������ �������
	protected $row_to;

	// ������������ ���������� ������� �� ������ ��������� (�� ������ "...")
	private $count_pages_on_navigation_panel;

	//������ ��� ��������� ��� �������� ������ ��������. �������� /delivery/visit_stat_v2/?page=
	public $navigation_href;

	/**
	 * @var array ��������� ���������. ������, ��������� �� ����. ���������:
	 * 		int navigation_from - ������� ����� ����� �������� �� ������ ���������
	 * 		int navigation_to	- ������� ������ ����� �������� �� ������ ���������
	 * 		bool left_suspension_points - ���������� ������������� ������ ���������� �� ������ ���������
	 * 		bool right_suspension_points - ���������� ������������� ������� ���������� �� ������ ���������
	 * 		int current_page - ������� ��������
	 */
	public $navigation_params;


	/**
	 * ���������� ����� where ��� ������� � sql ������
	 * @return mixed
	 */
	abstract protected function generate_sql_filter();

	/**
	 * ���������� ������ �������
	 * @return mixed
	 */
	abstract protected function generate_query_string_with_pagination();

	/**
	 * ���������� ������� ����� �����
	 *
	 * @return mixed
	 */
	abstract protected function get_count_all_items();

	/**
	 * ������������� �������� ������. ���� � ���������� ������� �������� �����-�� ���������.
	 * @param $name ��� �������
	 * @param $value �������� ������� � $_GET
	 * @return mixed ������������ �������� �������
	 */
	abstract protected function get_real_filter($name,$value);

	public function __construct(){
		$this->db = cDataBase::getInstance();
	}

	/**
	 * ����� ����� ��� ���� ����������� �������. ���������� � ������������ ����������� �������.
	 */
	protected function general($params){

		foreach ($params as $key => $value){
			$this->$key = $value;
		}

		$this->sort_order =	 	$this->get_real_sort_order();
		$this->sort_field = 	$this->get_real_sort_field();
		$this->real_filters = 	$this->get_real_filters();

		$this->sql_filter =		$this->generate_sql_filter();

		$this->count_pages = 	$this->get_count_pages();

		$this->page_number =	$this->get_real_page();

		if (!$this->page_number)
			show404();

		$this->row_from =		$this->count_items_on_page * ($this->page_number - 1) + 1;
		$this->row_to =			$this->count_items_on_page * $this->page_number;

		$this->rows = 			$this->get_rows($this->generate_query_string_with_pagination());

		$this->navigation_params = $this->get_navigation_params();

		$this->navigation_href = $this->get_navigation_href();

	}



	/**
	 * ���������� ����� �������� ��� false, ���� ����� �������� ���
	 */
	protected function get_real_page(){
		if (!isset($_GET['page']))
			return self::DEFAULT_PAGE_NUMBER;
		elseif (!$this->count_pages or !is_numeric($_GET['page']) or $_GET['page'] > $this->count_pages)
			return false;
		else
			return $_GET['page'];
	}

	//������� ������ � ������ �� sql �������
	private function get_rows($query){
		$rows = $this->db->select_query($query,__FILE__,__LINE__);
		if ($rows)
			return $rows;
		return false;
	}

	/**
	 * @return int ���������� ���������� �������
	 */
	private function get_count_pages(){
		return ceil($this->get_count_all_items() / $this->count_items_on_page);
	}

	/**
	 * ������������� �������� ������� (���� � $_GET ������ ������ ������)
	 */
	protected function get_real_filters(){
		foreach ($this->default_filters as $name => $value){
			//�� ��������
			if (!in_array($name,$this->checkboxes)){
				if (isset($_GET[$name]))
					$real_filters[$name] = $this->get_real_filter($name,$_GET[$name]);
				else
					$real_filters[$name] = $this->default_filters[$name];
			}
			//��������� ��������� (���� ����� ���������� - ������� �� ������� ���������, �����: ���� ���������� ����� ������ ������� ��� ������� �� ������ � �������� ����, �� ����������� � ������ ��������, � ���� ��� - ����� ���������� ��������
			else{
				$real_filters[$name] = isset($_GET['form_sent']) ? isset($_GET[$name]) : ((isset($_GET[$name]) ? (bool)$_GET[$name] : (bool)$this->default_filters[$name] ));
			}
		}

		return isset($real_filters) ? $real_filters : null;
	}

	/**
	 * @return mixed ������������ ���� ����������
	 */
	private function get_real_sort_field(){
		if (isset($_GET['sort_field']) && in_array($_GET['sort_field'],$this->sorts))
			return $_GET['sort_field'];

		return $this->default_sort_field;
	}

	/**
	 * @return mixed ������������ ������� ����������
	 */
	private function get_real_sort_order(){
		if (isset($_GET['sort_order']) && array_key_exists($_GET['sort_order'],$this->sort_order_names))
			return $this->sort_order_names[$_GET['sort_field']];

		return $this->default_sort_order;
	}

	private function get_navigation_params(){
		if ($this->count_pages < 2 or $this->count_items_on_page < 1 or $this->count_pages_on_navigation_panel < 1)
			return false;

		$navigation_params['current_page'] = $this->page_number;

		//���������� ������� ����� �� ������� �������� �� ������ ���������:
		$count_left_pages = ceil(($this->count_pages_on_navigation_panel - 1) / 2);

		$left_number = $this->page_number - $count_left_pages;
		$left_number = $left_number > 0 ? $left_number : 1;

		$right_number = $left_number + $this->count_pages_on_navigation_panel - 1;

		if ($right_number > $this->count_pages){
			$right_number = $this->count_pages;
			$left_number = $right_number - $this->count_pages_on_navigation_panel + 1;
			$left_number = $left_number > 0 ? $left_number : 1;
		}

		$navigation_params['navigation_from'] = $left_number;
		$navigation_params['navigation_to'] = $right_number;
		$navigation_params['left_suspension_points'] = $navigation_params['navigation_from'] > 1;
		$navigation_params['right_suspension_points'] = $navigation_params['navigation_to'] < $this->count_pages;


		return $navigation_params;
	}

	private function get_navigation_href(){

		$uri = &$_SERVER['REQUEST_URI'];

		// ���� � ������ ������� ��� ����������
		if (!strpos($uri,'?'))
			return $uri.'?page=page_number_pattern';
		// ���� ���� ������ ���������, �� ��� page
		elseif (!empty($_GET) && !isset($_GET['page']))
			return $uri.'&page=page_number_pattern';
		else
			return preg_replace('/page=[0-9]+/','page=page_number_pattern',$uri);
	}

}