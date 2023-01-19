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
        $sql = 
        'INSERT INTO users 
        (username, password, date_created, is_confirmed, is_deleted) 
        VALUES 
        (:username, :password, :date_created, :is_confirmed, :is_deleted)';
        $userData = [
            'username' => $username,
            'password' => $password,
            'date_created' => date('Y-m-d H:i:s'),
            'is_confirmed' => 0,
            'is_deleted' => 0
        ];

        $statement = $this->db->prepare($sql);
        $statement->execute($userData);
        $publisher_id = $this->db->lastInsertId();
        
        if($publisher_id > 0) {
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
        $sql = 'UPDATE users SET token = :token, date_updated = :date_updated WHERE id = :id';
        $userData = [
            'token' => $this->token,
            'date_updated' => date('Y-m-d H:i:s'),
            'id' => $user_id
        ];
        $stmt= $this->db->prepare($sql);
        $stmt->execute($userData);
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
        $sql = 'SELECT token FROM users WHERE username = :username';
        $userData = [
            'username' => $username
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['token'] == $token) {
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
            $sql = 
            'INSERT INTO user_info (id_user, name, surname, birthday, sex, city, date_created) VALUES (:id_user, :name, :surname, :birthday, :sex, :city, :date_created)';

            $userData = [
                'id_user' => $user_id,
                'date_created' => date('Y-m-d H:i:s')
            ];
        } else {
            $sql = 'UPDATE user_info SET name = :name, surname = :surname, birthday = :birthday, sex = :sex, city = :city, date_updated = :date_updated WHERE id_user = :id_user';

            $userData = [
                'id_user' => $user_id,
                'date_updated' => date('Y-m-d H:i:s')
            ];
        }

        $userData = array_merge($data, $userData);
        $statement = $this->db->prepare($sql);
        if ($statement->execute($userData)){
            return true;
        }
        
        return false;
    }

    /**
     * Получение личной информации о пользователе
     */
    public function getUserInfo(int $user_id): ?array
    {
        $sql = 'SELECT id, id_user, name, surname, birthday, sex, city, photo FROM user_info WHERE id_user = :id_user';
        $userData = [
            'id_user' => $user_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userInfo) {
            return NULL;
        }
        
        return $userInfo;
    }

    /**
     * Удаление и восстановление аккаунта
     */
    public function changeAccessProfile(string $username, int $status): bool
    {
        $sql = 'UPDATE users SET is_deleted = :is_deleted, date_updated = :date_updated WHERE username = :username';
        $userData = [
            'username' => $username,
            'is_deleted' => $status,
            'date_updated' => date('Y-m-d H:i:s')
        ];
        $statement = $this->db->prepare($sql);
        if ($statement->execute($userData)){
            return true;
        }
        return false;
    }

    /**
     * Создание ссылки для потверждения аккаунта при регистрации
     */
    private function createMailTokenLink(string $username, string $message): ?string
    {
        $token = Helper::tokenGenerate(78);
        $path = $_SERVER['HTTP_HOST'];
        $link = "<a href='http://$path/confirm.php?token=$token&email=$username'>$message</a>";

        $sql = 'UPDATE users SET token_confirm = :token_confirm, date_updated = :date_updated, date_message = :date_message WHERE username = :username';
        $date = date('Y-m-d H:i:s');
        $userData = [
            'username' => $username,
            'token_confirm' => $token,
            'date_updated' => $date,
            'date_message' => $date
        ];
        $statement = $this->db->prepare($sql);
        if ($statement->execute($userData)){
            return $link;
        }
        return null;
    }

    /**
     * Подтверждение профиля/обновление бд после перехода по ссылке
     */
    public function confirmProfile(string $username, string $token): bool
    {
        $sql = 'SELECT token_confirm FROM users WHERE username = :username';
        $userData = [
            'username' => $username
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $userToken = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($token == $userToken['token_confirm']) {
            $sql = 'UPDATE users SET token_confirm = :token_confirm, date_updated = :date_updated, is_confirmed = :is_confirmed WHERE username = :username';
            $userData = [
                'username' => $username,
                'token_confirm' => '',
                'date_updated' => date('Y-m-d H:i:s'),
                'is_confirmed' => 1
            ];
            $statement = $this->db->prepare($sql);
            if ($statement->execute($userData)){
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка времени, прошедшего после отправки последнего сообщения для подтверждения аккаунта
     */
    public function checkConfirmMessageDate(string $username): bool
    {
        $sql = 'SELECT date_message FROM users WHERE username = :username';
        $userData = [
            'username' => $username
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $userDateUpdate = $stmt->fetch(PDO::FETCH_ASSOC);

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
        $sql = 'SELECT photo FROM user_info WHERE id_user = :id_user';
        $userData = [
            'id_user' => $user_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $userPhoto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userPhoto) {
            $sql = 
            'INSERT INTO user_info (id_user, photo, date_created) VALUES (:id_user, :photo, :date_created)';

            $userData = [
                'id_user' => $user_id,
                'photo' => $path,
                'date_created' => date('Y-m-d H:i:s')
            ];
            $statement = $this->db->prepare($sql);
            if ($statement->execute($userData)){
                return true;
            }
        } else {
            $this->deleteUserPhoto($userPhoto['photo']);
            $sql = 'UPDATE user_info SET photo = :photo, date_updated = :date_updated WHERE id_user = :id_user';
            $userData = [
                'id_user' => $user_id,
                'date_updated' => date('Y-m-d H:i:s'),
                'photo' => $path
            ];
            $statement = $this->db->prepare($sql);
            if ($statement->execute($userData)){
                return true;
            }
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