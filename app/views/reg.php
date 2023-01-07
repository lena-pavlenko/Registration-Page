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
            } else {
                $error = 'Ошибка регистрации';
            }
        }
    }
?>

<?php
    if ($error) {
        echo $error;
    }
    if($success) {
        echo $success;
    } else {
        ?>
        <form action="/?type=reg" method="post">
            <div class="mb-3">
                <input type="text" name="username" placeholder="Введите логин" class="form-control mb-3">
            </div>
            <div class="mb-3">
                <input type="password" name="password" placeholder="Введите пароль" class="form-control mb-3">
            </div>
            <div class="mb-3">
                <input type="password" name="password_repeat" placeholder="Повторите пароль" class="form-control mb-3">
            </div>
            <input type="submit" name="send_reg" value="Зарегистрироваться" class="btn btn-primary mb-3">
        </form>
        <p>
            Либо <a href="/">Авторизуйтесь</a>
        </p>
        <?php
    }
?>