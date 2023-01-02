<?php
class User
{
    private $db;
    private $token;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addUser(string $username, string $password) :bool
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
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

    private function setAuth($user_id) :void
    {
        setcookie("token", $this->token, time()+259200, "/", $_SERVER['HTTP_HOST']);
        $sql = 'UPDATE users SET token = :token, date_updated = :date_updated WHERE id = :id';
        $userData = [
            'token' => $this->token,
            'date_updated' => date('Y-m-d H:i:s'),
            'id' => $user_id
        ];
        $stmt= $this->db->prepare($sql);
        $stmt->execute($userData);
    }

    public function auth(string $username, string $password) :bool
    {
        $sql = 'SELECT id, password FROM users WHERE username = :username';
        $userData = [
            'username' => $username
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData); 
        $user = $stmt->fetch();
       
        if (password_verify($password, $user['password'])) {
            $this->token = Helper::tokenGenerate();
            $this->setAuth($user['id']);
            return true;
        } 

        return false;
    }
}