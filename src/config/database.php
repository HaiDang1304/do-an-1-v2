<?php
class Database
{
  private $host = "localhost";
  private $db_name = "do-an-1-v2";
  private $username = "root";
  private $password = "";
  public $conn;

  public function getConnection()
  {
    $this->conn = null;
    try {
      $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
      if ($this->conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $this->conn->connect_error);
      }
    } catch (Exception $e) {
      echo "Lỗi: " . $e->getMessage();
    }

    return $this->conn;
  }
}
?>