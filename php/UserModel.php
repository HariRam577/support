<?php
class UserModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserById($id) {
        $sql = "SELECT * FROM approved_users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAllUsers() {
        $sql = "SELECT * FROM approved_users";
        $result = $this->conn->query($sql);
        return $result;
    }
}
?>
