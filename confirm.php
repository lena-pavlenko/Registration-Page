<?php
require 'config/config.php';
require 'app/classes/Helper.php';
require 'app/classes/Db.php';
require 'app/classes/User.php';
require 'vendor/autoload.php';
require 'app/classes/MailHelper.php';

$db = new Db();

if (!$db->connect) {
    die('Нужно подключиться к базе данных');
}
if (isset($_GET['email']) && isset($_GET['token'])) {
    $user = new User($db, $db->connect);
    if ($user->confirmProfile($_GET['email'], $_GET['token'])) {
        MailHelper::mailData($_GET['email'], 'Поздравляем!', 'Вы успешно подтвердили свой аккаунт!');
        echo 'Аккаунт подтвержден. <a href="/">Личный кабинет</a>';
    }
}
