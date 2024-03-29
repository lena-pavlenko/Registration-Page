<?php
class Db
{
    public $connect;
    
    public function __construct()
    {
        try{
            $this->connect = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        } catch (Exception $e) {
            $this->connect = null;
        }
    }

    public function select(string $table, array $fields, array $params): ?array
    {
        if(!$this->connect) {
            return null;
        }
        
        $sql = 'SELECT ';

        foreach ($fields as $key => $value) {

            if (!is_numeric($key)) {
                $sql .= $key . ' as `' . $value . '`,';
                continue;
            }
            $sql .= $value . ',';
        }

        $sql = substr($sql, 0, -1);
        $sql .= ' FROM ' . $table . ' WHERE ';
        $and = ' AND ';
        $count = 0;

        foreach ($params as $key => $value) {
            $count++;
            if ($count > 1 && $count <= count($params)) {
                $sql .= $and;
            }
            $sql .= $key . ' = :' . $key;
        }

        $stmt = $this->connect->prepare($sql);
        $stmt->execute($params); 
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
       
        return $data === false ? null : $data;
    }

    public function update(string $table, array $fields, array $params): ?bool
    {
        if(!$this->connect) {
            return null;
        }
        
        $sql = 'UPDATE ' . $table . ' SET ';

        foreach ($fields as $key => $value) {
            $sql .= $key . ' = :' . $key . ',';
        }

        $sql = substr($sql, 0, -1);
        $sql .= ' WHERE ';
        $and = ' AND ';
        $count = 0;

        foreach ($params as $key => $value) {
            $count++;
            if ($count > 1 && $count <= count($params)) {
                $sql .= $and;
            }
            $sql .= $key . ' = :' . $key;
        }
       
        $stmt = $this->connect->prepare($sql);
        $data = array_merge($fields, $params);

        return $stmt->execute($data); 
    }

    public function insert($table, $fields): bool
    {
        if(!$this->connect) {
            return null;
        }
        
        $sql = 'INSERT INTO ' . $table . ' (';

        foreach ($fields as $key => $value) {
            $sql .= $key . ',';
        }
        $sql = substr($sql, 0, -1);

        $sql .= ') VALUES (';

        foreach ($fields as $key => $value) {
            $sql .= ':' . $key . ',';
        }
        $sql = substr($sql, 0, -1);

        $sql .= ')';

        $statement = $this->connect->prepare($sql);
        return $statement->execute($fields);
    }

    public function find(int $id, string $table): ?array
    {
        if(!$this->connect) {
            return null;
        }
        
        $sql = 'SELECT * FROM ' . $table . ' WHERE id = :id';
        
        $dataArray = [
            'id' => $id
        ];

        $stmt = $this->connect->prepare($sql);
        $stmt->execute($dataArray); 
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data;
    }
}