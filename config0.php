<?

const SERVER_NAME_TEST_VER =         '***'; //SERVER_NAME тестовой версии. Не должно совпадать с SERVER_NAME рабочей версии!
const WEBMASTER_EMAIL =              '***'; //e-mail, куда будет сливаться инфа об ошибках и прочая лабуда с сайта
const THEME_ERROR_SITE =             'Перехваченная ошибка'; //Тема письма-уведомления о перехваченных ошибках.
const SITE_ADMIN =                   '***'; //Отправитель писем
const SEND_MAIL_TEST_SERVER =        false; //Константа показывает, нужно ли отправлять письма с тестового сервера
const WORKING_DB_SERVER =            true; //Показывает какую базу данных следует использовать: тестовую или рабочую.
const TEST_SERVER =                  '127.0.0.1'; //Тестовый сервер с СУБД
const TEST_LOGIN =                   'example';
const TEST_PASS =                    '';

const WORKING_LOGIN =                '***';
const WORKING_PASS =                 '***';
const MAIN_DB_NAME =                 '***'; //База данных по дефолту.

const DEFAULT_CONTROLLER =           'cExample';
const DEFAULT_ACTION =               'index';

const EXTERNAL_IP_WORKING_SERVER =         '***';
const INTERNAL_IP_WORKING_SERVER =         '***';

const TECHNICAL_WORK = false; //Повесить на рабочую версию сообщение "Технические работы"

//Массив с файлами, которые браузер может брать из своего кеша.
$__glob_array_allow_js_css_cache = array(
    '/public_libs/jQuery/jquery-1.10.2.min.js',
    '/public_libs/noty/jquery.noty.js',
    '/public_libs/noty/themes/default.js'
);