<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
                    // Use a persistent disk path if on Render, otherwise use local path
        if (getenv('RENDER')) {
            $this->db_path = '/var/data/hms/hms.sqlite';
        } else {
            $this->db_path = __DIR__ . '/../database/hms.sqlite';
        }

        // Ensure the directory exists
        if (!file_exists(dirname($this->db_path))) {
            mkdir(dirname($this->db_path), 0755, true);
        }
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            throw new Exception("Connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
