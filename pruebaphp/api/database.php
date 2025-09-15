<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'sistema_registro_productos';
    private $port = '5432';
    private $username = 'postgres';
    private $password = '1234';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error de conexión: '.$e->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
?>