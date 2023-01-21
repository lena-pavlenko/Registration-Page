<?php
class User
{
    private object $db;
    private object $Db;
    private string $token;
    private array $uploadImageErrors = [];

    public function __construct($db, $connect)
    {
        $this->db = $connect;
        $this->Db = $db;
    }

    /**
     * Получение данных о пользователе по логину
     */
    public function getUserByUsername(string $username): ?array
    {
        $userData = [
            'username' => $username
        ];
        
        $user = $this->Db->select(
            'users',
            ['id', 'password', 'date_created' => 'dc', 'date_updated' => 'du', 'is_deleted', 'is_confirmed'],
            $userData
        );

        return $user;
    }

    /**
     * Добавление нового юзера
     */
    public function addUser(string $username, string $password): bool
    {
        $user = $this->getUserByUsername($username);
        if (!empty($user)) {
            return false;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Добавление данных в таблицу
        $userData = [
            'username' => $username,
            'password' => $password,
            'date_created' => date('Y-m-d H:i:s'),
            'is_confirmed' => 0,
            'is_deleted' => 0
        ];
        
        if($this->Db->insert('users', $userData)) {
            $this->sendConfirmToken($username);
            return true;
        }

        return false;
    }

    /**
     * Отправка ссылки с токеном для подтверждения аккаунта
     */
    public function sendConfirmToken(string $username): void
    {
        $link = $this->createMailTokenLink($username, 'Подтвердите аккаунт');
        MailHelper::mailData($username, 'Подтверждение аккаунта', 'Перейдите по ссылке для активации аккаунта: ' . $link);
        MailHelper::mail();
    }

    /**
     * Создание токена для авторизации
     */
    private function setAuth(int $user_id, string $username): void
    {
        setcookie("token", $this->token, time()+259200, "/", $_SERVER['HTTP_HOST']);
        setcookie("username", $username, time()+259200, "/", $_SERVER['HTTP_HOST']);

        $fields = [
            'token' => $this->token,
            'date_updated' => date('Y-m-d H:i:s'),
        ];
        $params = ['id' => $user_id];

        $this->Db->update('users', $fields, $params);
    }

    /**
     * Авторизация пользователя
     */
    public function auth(string $username, string $password): bool
    {   
        // Выбирает данные из таблицы
        $user = $this->getUserByUsername($username);

        if (empty($user)) {
            return false;
        }
        if (password_verify($password, $user['password'])) {
            $this->token = Helper::tokenGenerate();
            $this->setAuth($user['id'], $username);
            return true;
        } 

        return false;
    }

    /**
     * Сравнение значений куки со значениями в БД для проверки авторизации
     */
    public function checkAuth(string $username, string $token): bool
    {
        $userData = [
            'username' => $username
        ];
        
        $userToken = $this->Db->select('users', ['token'], $userData);

        if (isset($userToken['token']) && $userToken['token'] == $token) {
            return true;
        }

        return false;
    }

    /**
     * Удаление куки
     */
    public function logout(): void
    {
        setcookie("token", '', time()-259200, "/", $_SERVER['HTTP_HOST']);
        setcookie("username", '', time()-259200, "/", $_SERVER['HTTP_HOST']);
    }

    /**
     * Установка значений в поля в ЛК
     */
    public function setName(int $user_id, array $data): bool
    {
        if (empty($this->getUserInfo($user_id))) {

            $fields = [
                'id_user' => $user_id,
                'date_created' => date('Y-m-d H:i:s')
            ];
            $fields = array_merge($data, $fields);

            return $this->Db->insert('user_info', $fields);
        } else {
            $fields = [
                'date_updated' => date('Y-m-d H:i:s'),
            ];
            $fields = array_merge($data, $fields);
            $params = ['id_user' => $user_id];
    
            return $this->Db->update('user_info', $fields, $params);
        }
        
        return false;
    }

    /**
     * Получение личной информации о пользователе
     */
    public function getUserInfo(int $user_id): ?array
    {
        $userData = [
            'id_user' => $user_id
        ];
        
        $userInfo = $this->Db->select(
            'user_info', 
            ['id', 'id_user', 'name', 'surname', 'birthday', 'sex', 'city', 'photo'], 
            $userData
        );
        
        return $userInfo;
    }

    /**
     * Удаление и восстановление аккаунта
     */
    public function changeAccessProfile(string $username, int $status): bool
    {
        $fields = [
            'is_deleted' => $status,
            'date_updated' => date('Y-m-d H:i:s')
        ];
        $params = ['username' => $username];

        return $this->Db->update('users', $fields, $params);
    }

    /**
     * Создание ссылки для потверждения аккаунта при регистрации
     */
    private function createMailTokenLink(string $username, string $message): ?string
    {
        $token = Helper::tokenGenerate(78);
        $path = $_SERVER['HTTP_HOST'];
        $link = "<a href='http://$path/confirm.php?token=$token&email=$username'>$message</a>";

        $date = date('Y-m-d H:i:s');
        $fields = [
            'token_confirm' => $token,
            'date_updated' => $date,
            'date_message' => $date
        ];
        $params = ['username' => $username];

        return $this->Db->update('users', $fields, $params) ?  $link : null;
    }

    /**
     * Подтверждение профиля/обновление бд после перехода по ссылке
     */
    public function confirmProfile(string $username, string $token): bool
    {
        $userData = [
            'username' => $username
        ];
        
        $userToken = $this->Db->select('users', ['token_confirm'], $userData);

        if ($token == $userToken['token_confirm']) {

            $fields = [
                'token_confirm' => '',
                'date_updated' => date('Y-m-d H:i:s'),
                'is_confirmed' => 1
            ];
            $params = ['username' => $username];
    
           return $this->Db->update('users', $fields, $params);
        }

        return false;
    }

    /**
     * Проверка времени, прошедшего после отправки последнего сообщения для подтверждения аккаунта
     */
    public function checkConfirmMessageDate(string $username): bool
    {
        $userData = [
            'username' => $username
        ];

        $userDateUpdate = $this->Db->select('users', ['date_message'], $userData);

        $updateSec = strtotime($userDateUpdate['date_message']);
        
        return (time() - $updateSec) > 3600;
    }

    /**
     * Загрузка фото пользователя
     */
    public function uploadUserPhoto($user_id): bool
    {
        if (empty($_FILES["user_photo"]["tmp_name"])) {
            return false;
        }
        $target_dir = "uploads/";
        $target_file = $target_dir . time() . basename($_FILES["user_photo"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["user_photo"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $uploadOk = 0;
            $this->uploadImageErrors[] = 'Загрузите изображение!';
        }
        // Check if file already exists
        if (file_exists($target_file)) {
            $uploadOk = 0;
            $this->uploadImageErrors[] = 'Такой файл уже существует на сервере!';
        }
        // Check file size
        if ($_FILES["user_photo"]["size"] > 5000000) {
            $uploadOk = 0;
            $this->uploadImageErrors[] = 'Размер файла не должен превышать 5 Мб!';
        }
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $this->uploadImageErrors[] = 'Допустимые расширения: jpg, png, jpeg, gif!';
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $this->uploadImageErrors[] = 'Простите, Ваш файл не удалось загрузить!';
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["user_photo"]["tmp_name"], $target_file)) {
                $this->saveUserPhoto($user_id, $target_file);
                return true;
            } else {
                $this->uploadImageErrors[] = 'Простите, Ваш файл не удалось загрузить!';
            }
        }

        return false;
    }

    /**
     * Сохранение фото пользователя в бд
     */
    private function saveUserPhoto(string $user_id, string $path): bool
    {
        $userData = [
            'id_user' => $user_id
        ];

        $userPhoto = $this->Db->select('user_info', ['photo'], $userData);
        
        if (!$userPhoto) {
            $userData = [
                'id_user' => $user_id,
                'photo' => $path,
                'date_created' => date('Y-m-d H:i:s')
            ];

            return $this->Db->insert('user_info', $userData);
        } else {
            $photoPath = $userPhoto['photo'] ?? '';
            $this->deleteUserPhoto($photoPath);

            $fields = [
                'date_updated' => date('Y-m-d H:i:s'),
                'photo' => $path
            ];
            $params = ['id_user' => $user_id];
    
            return $this->Db->update('user_info', $fields, $params);
        }

        return false;
    }

    /**
     * Удаление файла
     */
    private function deleteUserPhoto(string $path): void
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .  $path;
        if (file_exists($path)) {
            unlink($path);
        }
        
    }
}