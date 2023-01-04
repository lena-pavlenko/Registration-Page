<?php
require 'config/config.php';
require 'app/classes/Helper.php';
require 'app/classes/Db.php';
require 'app/classes/User.php';

// Создаем экземпляр класса PDO (PHP Data Objects) - для работы с базой данных
$db = new Db();

if (!$db->connect) {
    die('Нужно подключиться к базе данных');
}

// Подключение файлов для регистрации и авторизации
if (isset($_COOKIE['token']) && isset($_COOKIE['username'])) {
    $user = new User($db->connect);

    if ($user->checkAuth($_COOKIE['username'], $_COOKIE['token'])) {
        include 'app/views/cabinet.php';
    } else {
        include 'app/views/login.php';
    }
} else {
    if (isset($_GET['type']) && $_GET['type'] == 'reg') {
        include 'app/views/reg.php';
    } else {
        include 'app/views/login.php';
    }
}