<?php
require 'config/config.php';
require 'app/classes/Db.php';

$db = new Db();

if (!$db->connect) {
    die('Нужно подключиться к базе данных');
}

if (isset($_COOKIE['token'])) {
    echo 'Проверяем авторизацию';
} else {
    echo 'Кидаем на авторизацию';
}