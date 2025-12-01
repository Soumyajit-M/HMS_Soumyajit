<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE (username = :username OR email = :username) AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $this->setSession($user);
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function register($data) {
        try {
            // Check if user already exists
            if ($data['role'] === 'doctor') {
                // For doctors, only check username uniqueness
                $query = "SELECT id FROM users WHERE username = :username";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':username', $data['username']);
            } else {
                // For other roles, check both username and email
                $query = "SELECT id FROM users WHERE username = :username OR email = :email";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':username', $data['username']);
                $stmt->bindParam(':email', $data['email']);
            }
            $stmt->execute();

            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user
            $query = "INSERT INTO users (username, email, password, role, first_name, last_name, phone, address) 
                     VALUES (:username, :email, :password, :role, :first_name, :last_name, :phone, :address)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            
            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();
                return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $userId];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    public function isLoggedIn() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $query = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }

    public function hasAnyRole($roles) {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], $roles);
    }

    private function setSession($user) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $query = "SELECT password FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();

            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':user_id', $userId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name,
                     phone = :phone, address = :address WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
