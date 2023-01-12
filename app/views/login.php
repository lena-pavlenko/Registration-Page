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

<?php
    if ($error) {
        echo $error;
    }
?>
<form action="/" method="post">
    <div class="mb-3">
        <input type="text" name="username" placeholder="Введите логин" class="form-control mb-3">
    </div>
    <div class="mb-3">
        <input type="password" name="password" placeholder="Введите пароль" class="form-control mb-3">
    </div>
    <input type="submit" name="send_auth" value="Авторизоваться" class="btn btn-primary">
</form>
<p>
    Либо <a href="/?type=reg">Зарегистрируйтесь</a>
</p>

