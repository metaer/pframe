<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Павел
 * Date: 21.09.13
 * Time: 16:54
 * To change this template use File | Settings | File Templates.
 */


/**
 * Подключает класс
 * @param string $class имя файла класса
 */
function load_classes($class){
    if (file_exists(CLASSES_PATH.$class.'.php'))
        require_once CLASSES_PATH.$class.'.php';
}

/**
 * как и print_r, только в тегом <pre> и die в конце;
 * @param mixed $expr выражение для print_r
 */
function print_p($expr){
    echo '<pre>';
    print_r($expr);
    die;
}

function show404(){
    header("HTTP/1.1 404 Not Found");
    die('404. Не найдено');
}

function send_email($message,$theme = null,$from = SITE_ADMIN,$to = WEBMASTER_EMAIL){

    if (!WORKING_VERSION && !SEND_MAIL_TEST_SERVER)
        return;

    if (is_null($theme))
        $theme = $message;

    mail($to,$theme,$message,"From: ".SITE_ADMIN);

}