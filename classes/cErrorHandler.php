<?php

class cErrorHandler {

	static $error_types = array(
		E_ERROR => 'E_ERROR',
		E_WARNING => 'E_WARNING' ,
		E_PARSE => 'E_PARSE',
		E_NOTICE => 'E_NOTICE',
		E_CORE_ERROR => 'E_CORE_ERROR',
		E_CORE_WARNING => 'E_CORE_WARNING',
		E_COMPILE_ERROR => 'E_COMPILE_ERROR',
		E_COMPILE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE',
		E_STRICT => 'E_STRICT',
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
		E_DEPRECATED => 'E_DEPRECATED',
		E_USER_DEPRECATED => 'E_USER_DEPRECATED',
		E_ALL => 'E_ALL'
	);

	static function handle($errno, $errstr, $file, $line){

		$error_type = self::$error_types[$errno];

		self::error_report($errstr,$file,$line,$error_type);

	}

	private static function error_report($text,$file,$line,$error_type = null){
		$date = date("Y.m.d H:i:s");

		$message = "$date: ". (!is_null($error_type) ? $error_type : 'SQL_ERROR')." $text. file: $file, line: $line <br><br>".EL.EL;

		$message .=  WORKING_VERSION ? '' : '<br><pre>' . print_r(debug_backtrace(),1);
		$message .= print_r($_SERVER,1);

		send_email($message,THEME_ERROR_SITE);

		if (defined('WORKING_VERSION') && !WORKING_VERSION)
			echo "<b>$message</b>";
	}

	/**
	 * Перехват фатальных ошибок
	 */
	static function fatal(){
		$err = error_get_last();

		$err_type = &$err['type'];

		$fatal_array = array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);

		if ($err && in_array($err_type,$fatal_array))
			self::handle($err_type,$err['message'],$err['file'],$err['line']);

	}

	public static function sql_query_error($message,$file,$line){
		$message.="<br><pre>".cDataBase::getInstance()->get_last_query()."</pre><br>";
		self::error_report($message,$file,$line);
	}
} 