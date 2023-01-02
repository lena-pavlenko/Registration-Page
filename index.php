<?php
require 'config/config.php';
require 'app/classes/Helper.php';
require 'app/classes/Db.php';
require 'app/classes/User.php';

$db = new Db();

if (!$db->connect) {
    die('Нужно подключиться к базе данных');
}

if (isset($_COOKIE['token'])) {
    echo 'Вы авторизованы';
} else {
    if (isset($_GET['type']) && $_GET['type'] == 'reg') {
        include 'app/views/reg.php';
    } else {
        include 'app/views/login.php';
    }
}