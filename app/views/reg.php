<?php
    $error = '';
    $success = '';
    if (isset($_POST['send_reg'])) {
        $login = trim($_POST['username']);
        if ($_POST['password'] != $_POST['password_repeat']) {
            $error = 'Пароли не совпадают';
        }
        $password = $_POST['password'];
        $user = new User($db->connect);
        if (!$error) {
            if ($user->addUser($login, $password)) {
                $success = 'Вы успешно зарегистрированы';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
</head>
<body>
    <?php
        if ($error) {
            echo $error;
        }
        if($success) {
            echo $success;
        } else {
            ?>
            <form action="/?type=reg" method="post">
                <input type="text" name="username" placeholder="Введите логин">
                <input type="password" name="password" placeholder="Введите пароль">
                <input type="password" name="password_repeat" placeholder="Повторите пароль">
                <input type="submit" name="send_reg" value="Зарегистрироваться">
            </form>
            <p>
                Либо <a href="/">Авторизуйтесь</a>
            </p>
            <?php
        }
    ?>
    
</body>
</html>
