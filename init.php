<?php

set_time_limit(900);

require_once dirname(__FILE__) . '/classes/cErrorHandler.php';

header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache");
header("Pragma: no-cache");

//Тестовая или рабочая версия сайта
define('WORKING_VERSION',$_SERVER['SERVER_NAME'] != SERVER_NAME_TEST_VER);

define('WORKING_SERVER',WORKING_VERSION ? INTERNAL_IP_WORKING_SERVER : EXTERNAL_IP_WORKING_SERVER);

//Конец строки
const EL = PHP_EOL;

//Добавление unix метки в качестве параметра к js/css
define('ADD_TIME_TO_JS_CSS',WORKING_VERSION);

if (WORKING_VERSION && TECHNICAL_WORK){
	header("HTTP/1.1 503 Service Unavailable",null,503);
	die('Технические работы');
}

//Разделитель директорий
defined('DS') or define('DS',DIRECTORY_SEPARATOR);

//Корневая директория со слешем на конце
defined('BASE_DIR_S') or define('BASE_DIR_S',dirname(__FILE__) . DS);

//Корневая директория (без слеша на конце)
define('BASE_DIR',dirname(__FILE__));

//Путь к классам
defined('CLASSES_PATH') or define('CLASSES_PATH',BASE_DIR_S.'classes'.DS);

//Путь к моделям
defined('MODELS_PATH') or define('MODELS_PATH',BASE_DIR_S.'models'.DS);

//Путь к контроллерам
defined('CONTROLLERS_PATH') or define('CONTROLLERS_PATH',BASE_DIR_S.'controllers'.DS);

//Путь к шаблонам
defined('TEMPLATES_PATH') or define('TEMPLATES_PATH',BASE_DIR_S.'templates'.DS);

//Подключаем файлы с функциями
require_once BASE_DIR_S . 'functions/gen_functions.php';
require_once BASE_DIR_S . 'functions/shutdown_func.php';

//Автоподключение классов
spl_autoload_register('load_classes');

set_error_handler('cErrorHandler::handle');
register_shutdown_function('shutdown');

//Подключаем всё необходимое для MVC
require_once CONTROLLERS_PATH.'cController.php';
require_once MODELS_PATH.'cModel.php';
require_once BASE_DIR_S . 'cTemplate.php';