<?php
    if (isset($_POST['log_out'])) {
        $user->logout();
        header('Location: /');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
</head>
<body>
    <h1>Личный кабинет</h1>
    <form action="/" method="post">
        <button type="submit" name="log_out">Выйти</button>
    </form>
</body>
</html>