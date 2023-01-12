<?php
    $user = new User($db->connect);
    $userData = $user->getUserByUsername($_COOKIE['username']);

    if (isset($_POST['delete_profile'])) {
        if ($user->changeAccessProfile($_COOKIE['username'], 1)) {
            header('Location: /');
        }
    }
    if (isset($_POST['restore_profile'])) {
        if ($user->changeAccessProfile($_COOKIE['username'], 0)) {
            header('Location: /');
        }
    }

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

    if (isset($_POST['send_again'])) {
        $user->sendConfirmToken($_COOKIE['username']);
        header('Location: /');
    }

    $userInfo = $user->getUserInfo($userData['id']);

    $date = new DateTime(date('Y-m-d H:i'), new DateTimeZone(date_default_timezone_get()));
    $date->setTimezone(new DateTimeZone('Europe/London'));
?>

<?php if ($userData['is_deleted'] == 1) :?>
    <div class="row mb-5">
        <div class="col-12 ">
            <div class="card p-3">
                <h1>Личный кабинет</h1>
                <p>Приветствуем вас, <?= $userInfo['name'] ? $userInfo['name'] : $_COOKIE['username']; ?></p>
                <div class="alert alert-warning" role="alert">
                    Ваш аккаунт был удален <?= $userData['du']; ?>
                </div>
                <form action="/" method="post" class="d-flex">
                    <button type="submit" name="restore_profile" class="btn btn-success">Восстановить аккаунт</button>
                </form>
            </div>
        </div>
    </div>
<?php elseif($userData['is_confirmed'] == 0): ?>
    <div class="row mb-5">
        <div class="col-12 ">
            <div class="card p-3">
                <h1>Личный кабинет</h1>
                <p>Приветствуем вас, <?= isset($userInfo['name']) ? $userInfo['name'] : $_COOKIE['username']; ?></p>
                <div class="alert alert-warning" role="alert">
                    Подтвердите Ваш аккаунт. Мы отправили вам письмо на <u><?= $_COOKIE['username']; ?></u> 
                </div>
                <?php if(!$user->checkConfirmMessageDate($_COOKIE['username'])): ?>
                    <p>Повторно отправить сообщение можно будет через час.</p>
                <?php else: ?>
                    <form action="/" method="post" class="mt-3 d-flex justify-content-end">
                        <button type="submit" name="send_again" class="btn btn-info">Отправить ссылку еще раз</button>
                    </form>
                <?php endif; ?>
                <form action="/" method="post" class="mt-3 d-flex justify-content-end">
                    <button type="submit" name="log_out" class="btn btn-danger">Выйти</button>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
<div class="row mb-5">
    <div class="col-12 ">
        <div class="card p-3">
            <h1>Личный кабинет</h1>
            <p>Приветствуем вас, <?= isset($userInfo['name']) ? $userInfo['name'] : $_COOKIE['username']; ?></p>
        </div>
    </div>
</div>
<div class="row mb-5">
    <div class="col-lg-4">
        <div class="card p-3">
            <div class="content">
                <p>Дата регистрации: <?= $userData['dc']; ?></p>
                <p>Проведено на сайте: <?= Helper::convertTime(time() - strtotime($userData['dc'])); ?></p>
                <p>Текущее время: <?= date('H:i'); ?></p>
                <p>Время в Лондоне: <?= $date->format('H:i'); ?> </p>
            </div>
            <form action="/" method="post" class="mt-3 d-flex justify-content-end">
                <button type="submit" name="log_out" class="btn btn-danger">Выйти</button>
            </form>
        </div>
        <div class="card mt-3 mb-3 p-2">
            <form action="/" method="post" class="d-flex">
                <button type="submit" name="delete_profile" class="btn btn-secondary ms-auto">Удалить аккаунт</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <form action="/" method="post">
                <input type="text" name="name" placeholder="Имя" class="form-control mb-3 mt-3" value="<?= $userInfo['name'] ?? ''; ?>">
                <input type="text" name="surname" placeholder="Фамилия" class="form-control mb-3" value="<?= $userInfo['surname'] ?? ''; ?>">
                <input type="date" name="birthday" placeholder="Дата рождения" class="form-control mb-3" value="<?= $userInfo['birthday'] ?? ''; ?>">
                <select name="sex" class="form-select mb-3" >
                    <?php $sex = isset($userInfo['sex']) ? $userInfo['sex'] : ''; ?>
                    <option value="male" <?= $sex == 'male' ? 'selected' : ''; ?>>Мужчина</option>
                    <option value="female" <?= $sex == 'female' ? 'selected' : ''; ?>>Женщина</option>
                </select>
                <input type="text" name="city" placeholder="Город" class="form-control mb-3" value="<?= $userInfo['city'] ?? ''; ?>">
                <button type="submit" name="save" class="btn btn-info ms-auto d-block">Сохранить</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
