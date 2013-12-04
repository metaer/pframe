<?php
/**
 * Created by PhpStorm.
 * User: Павел
 * Date: 26.11.13
 * Time: 10:07
 */

class cDataBase {

    private $db_server;
    private $user;
    private $pass;
	private $db_name;

    private $pdo;

	private $connected;

	private $last_query;

    protected static $instance;

	private function __clone(){}  // Защищаем от создания через клонирование

	private function __wakeup(){}  // Защищаем от создания через unserialize

    private function __construct(){
		$this->db_server = WORKING_DB_SERVER ? WORKING_SERVER : TEST_SERVER;
		$this->user		 = WORKING_DB_SERVER ? WORKING_LOGIN : TEST_LOGIN;
		$this->pass		 = WORKING_DB_SERVER ? WORKING_PASS : TEST_PASS;
		$this->db_name   = MAIN_DB_NAME;

		try{
			$this->pdo = new PDO("mysql:host=$this->db_server;dbname=$this->db_name", $this->user, $this->pass);
		}
		catch(PDOException $e){
			trigger_error('Ошибка коннекта к базе: '.$e->getMessage(),E_USER_WARNING);
		}

		$this->connected = (bool)$this->pdo;

    }

	public static function getInstance() {
		if ( !isset(self::$instance) )
			self::$instance = new cDataBase();
		return self::$instance;
	}

	public function get_last_query(){
		return $this->last_query;
	}

	#-----------------------------------

	/**
	 * Выполняет запрос и возвращает количество затронутых рядов (update,insert,...) Рекомендуется использовать для не-select запросов
	 * @param $query
	 * @param null $file
	 * @param null $line
	 */
	public function non_select_query($query,$file=null,$line=null){

		if (!$this->connected)
			return false;

		$this->set_backtrace_data($file,$line);

		$pdo_st = $this->query($query,$file,$line);

		$this->check_pdo_st($pdo_st,$file,$line);

		return $pdo_st ? $pdo_st->rowCount() : false;

	}

	/**
	 * Выполняет sql_запрос и возвращает записи из результата запроса в виде ассоциативного массива. Использовать для select запросов
	 * @param $query
	 * @param null $file
	 * @param null $line
	 * @return bool|null
	 */
	public function select_query($query,$file = null,$line = null){

		if (!$this->connected)
			return false;

		$this->set_backtrace_data($file,$line);

		$pdo_st = $this->query($query,$file,$line);

		return $this->fetch($pdo_st, null, $file,$line);

	}

	/**
	 * Возвращает один ряд с данными
	 *
	 * @param $query
	 * @param null $file
	 * @param null $line
	 * @return null Один ряд с данными
	 */
	public function select_first_row_query($query,$file=null,$line=null){

		if (!$this->connected)
			return false;

		$this->set_backtrace_data($file,$line);

		$pdo_st = $this->query($query,$file,$line);
		return $this->fetch($pdo_st, 1, $file,$line);
	}


	/**
	 * Возвращает заголовки таблицы по имени таблицы (либо представления по его имени)
	 * @param $table
	 * @param null $file
	 * @param null $line
	 */
	public function get_headers($table,$file=null,$line=null){

		if (!$this->connected)
			return false;

		$this->set_backtrace_data($file,$line);

		$row = $this->select_first_row_query("SELECT * FROM $table",$file,$line);
		return array_keys($row);
	}

	#-----------------------------------

	/**
	 * Возвращает либо один ряд, либо массив с рядами, в зависимости от параметра $only_first_row, или null если нифига нет.
	 * @param $pdo_st
	 * @param null $only_first_row
	 * @param null $file
	 * @param null $line
	 * @return
	 */
	private function fetch($pdo_st, $only_first_row = null, $file = null,$line = null){

		$this->check_pdo_st($pdo_st,$file,$line);

		$fetch_method = 'fetch' . ($only_first_row ? '' : 'All');

		$fetched = $pdo_st->{$fetch_method}(PDO::FETCH_ASSOC);
		return $fetched ? : null;
	}

	/**
	 * Отлавливает ошибки в sql запросе
	 * @param $pdo_st
	 * @param null $file
	 * @param null $line
	 * @return bool
	 */
	private function check_pdo_st($pdo_st,$file = null, $line = null){
		if (!$pdo_st){
			$error_message_arr = $this->pdo->errorInfo();
			cErrorHandler::sql_query_error($error_message_arr[2],$file,$line);
			return false;
		}
	}

	//Выполняет запрос и возвращает pdo statement
	private function query($query,$file = null,$line = null){
		if (!$this->connected)
			return false;

		$pdo_st = $this->pdo->query($query);
		$this->last_query = $query;

		return $pdo_st;
	}

	private function set_backtrace_data(&$file,&$line){
		if(!$file || !$line){
			$backtrace = debug_backtrace();
			$file      = $backtrace[1]['file'];
			$line      = $backtrace[1]['line'];
		}
	}

}