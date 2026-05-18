<?php
// Database configuration
class Database {
    private $host = "sql302.infinityfree.com";
    private $db_name = "if0_41822736_inlandflow";
    private $username = "if0_41822736";
    private $password = "Joerenrey21";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Start session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function getUserData() {
    global $db;
    if(!isLoggedIn()) return null;
    
    $userId = $_SESSION['user_id'];
    $userType = $_SESSION['user_type'];
    
    if($userType == 'guest') {
        $stmt = $db->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif($userType == 'resort') {
        $stmt = $db->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif($userType == 'municipal') {
        $stmt = $db->prepare("SELECT * FROM tb_municipality WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif($userType == 'provincial') {
        $stmt = $db->prepare("SELECT * FROM tb_provincial WHERE provid = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}
?>