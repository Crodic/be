<?php
header("Content-type: application/json");
class DBContext
{
    private $servername;
    private $username;
    private $password;
    private $DBname;

    /** @var PDO */
    private $conn;

    // Khởi Tạo Database
    function __construct()
    {
        $this->servername = servername;
        $this->username = username;
        $this->password = password;
        $this->DBname = dbname;
        if ($this->conn == NULL) {
            $this->Connection();
        } else {
            return $this->conn;
        }
    }

    // Huỷ Database
    function __destruct()
    {
        $this->servername = "";
        $this->username = "";
        $this->password = "";
        $this->DBname = "";
    }

    // Kết Nối Database
    public function Connection()
    {
        try {
            $this->conn = new PDO("mysql:host={$this->servername};dbname={$this->DBname};charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo json_encode(["status" => false, "statusCode" => 500, "msg" => "Connection Fail"]);
            exit();
        }
    }

    // Đóng PDO
    public function close()
    {
        $this->conn = NULL;
    }
}
