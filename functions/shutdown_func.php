<?php

function shutdown(){
	//Если программа завершилась из-за фатальной ошибки - перехватываем.
	cErrorHandler::fatal();
}