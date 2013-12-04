<?php

error_reporting(0);

//Подключаем файл конфигурации
require_once 'config.php';

//Инициализируем приложение
require_once 'init.php';

//Выполняем маршрутизацию
require_once BASE_DIR_S . 'router.php';