<?php
class User
{
    private $db;
    private $token;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUserByUsername(string $username): ?array
    {
        $sql = 'SELECT id, password FROM users WHERE username = :username';
        $userData = [
            'username' => $username
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $user = $stmt->fetch();

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
            return true;
        }

        return false;
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
        $user = $stmt->fetch();

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

    public function getUserInfo(int $user_id): ?array
    {
        $sql = 'SELECT id, id_user, name, surname, birthday, sex, city FROM user_info WHERE id_user = :id_user';
        $userData = [
            'id_user' => $user_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $userInfo = $stmt->fetch();

        if (!$userInfo) {
            return NULL;
        }
        
        return $userInfo;
    }
}