<?php

//TODO переделать роутинг в стиле ООП на класс cRouter

//Отсечем get параметры
$parts = explode('?',$_SERVER['REQUEST_URI']);
$request_uri_without_params = $parts[0];

//Получим имена контроллера и действия
$parts = explode('/',strtolower($request_uri_without_params));
$parts[2] = isset($parts[2]) && $parts[2] ? $parts[2] : null;
list(,$controller,$action) = $parts;

require_once BASE_DIR_S.'controllers/cSinglepages.php';

//В случае небольших простеньких страничек используем один контроллер на них всех (по методу на каждую)
if (in_array($controller, get_class_methods('cSinglepages')) && is_null($action)){
	$action = $controller;
	$controller = 'Singlepages';
}

//Если в стартовой строке запроса не указаны контроллер и действие, выставим дефолтовые значения
$controller = $controller ? 'c'.ucfirst(strtolower($controller)) : DEFAULT_CONTROLLER;

//Тоже самое с действием
$action = $action ? strtolower($action) : DEFAULT_ACTION;

//Переменная показывает, существует ли указанная "страница"
$page_exists = false;

//Вызываем метод контроллера
if (file_exists(CONTROLLERS_PATH.$controller.'.php')){
    require_once CONTROLLERS_PATH.$controller.'.php';
    if (class_exists($controller)){
        $class_info = new ReflectionClass($controller);
        if (!$class_info->isAbstract() && $class_info->hasMethod($action)){
            $method = $class_info->getMethod($action);
            if ($method->isPublic() && !$method->isStatic() && !$method->isConstructor() && !$method->isDestructor()){
                $method->invoke(new $controller);
                $page_exists = true;
            }
        }
    }
}

//Если такого контроллера/метода нет - выдаем 404
if (!$page_exists)
    show404();