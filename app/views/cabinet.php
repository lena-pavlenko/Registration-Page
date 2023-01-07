<?php
    $user = new User($db->connect);
    $userData = $user->getUserByUsername($_COOKIE['username']);

    if (isset($_POST['log_out'])) {
        $user->logout();
        header('Location: /');
    }

    if (isset($_POST['save'])) {
        $user->setName($userData['id'], [
            'name' => trim($_POST['name']),
            'surname' => trim($_POST['surname']),
            'birthday' => $_POST['birthday'] ? $_POST['birthday'] : null,
            'sex' => $_POST['sex'],
            'city' => trim($_POST['city']),
        ]);
    }

    $userInfo = $user->getUserInfo($userData['id']);
?>


<div class="row mb-5">
    <div class="col-12 ">
        <div class="card p-3">
            <h1>Личный кабинет</h1>
            <p>Приветствуем вас, email</p>
        </div>
    </div>
</div>
<div class="row mb-5">
    <div class="col-4">
        <div class="card p-3">
            <div class="content">
                <p>Дата регистрации: </p>
                <p>Проведено на сайте: </p>
                <p>Текущее время: </p>
                <p>Время в Лондоне: </p>
            </div>
            <form action="/" method="post" class="mt-3 d-flex justify-content-end">
                <button type="submit" name="log_out" class="btn btn-danger">Выйти</button>
            </form>
        </div>
       
    </div>
    <div class="col-8">
        <div class="card p-3">
            <form action="/" method="post">
                <input type="text" name="name" placeholder="Имя" class="form-control mb-3 mt-3" value="<?= $userInfo['name'] ?? ''; ?>">
                <input type="text" name="surname" placeholder="Фамилия" class="form-control mb-3" value="<?= $userInfo['surname'] ?? ''; ?>">
                <input type="date" name="birthday" placeholder="Дата рождения" class="form-control mb-3" value="<?= $userInfo['birthday'] ?? ''; ?>">
                <select name="sex" class="form-select mb-3" >
                    <option value="male" <?= $userInfo['sex'] == 'male' ? 'selected' : ''; ?>>Мужчина</option>
                    <option value="female" <?= $userInfo['sex'] == 'female' ? 'selected' : ''; ?>>Женщина</option>
                </select>
                <input type="text" name="city" placeholder="Город" class="form-control mb-3" value="<?= $userInfo['city'] ?? ''; ?>">
                <button type="submit" name="save" class="btn btn-info">Сохранить</button>
            </form>
        </div>
    </div>
</div>