<?php
    $error = '';
    $success = '';
    if (isset($_POST['send_auth'])) {
        $login = trim($_POST['username']);
        $password = $_POST['password'];
        $user = new User($db->connect);
        if (!$user->auth($login, $password)) {
            $error = 'Данные не верны';
        } else {
            header('Location: /');
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
</head>
<body>
    <?php
        if ($error) {
            echo $error;
        }
    ?>
    <form action="/" method="post">
        <input type="text" name="username" placeholder="Введите логин">
        <input type="password" name="password" placeholder="Введите пароль">
        <input type="submit" name="send_auth" value="Авторизоваться">
    </form>
    <p>
        Либо <a href="/?type=reg">Зарегистрируйтесь</a>
    </p>
</body>
</html>
