<?php
class Account {
    protected $conn;
    private $table = "accounts";

    public $id;
    public $nom;
    public $email;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create account
    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nom=:nom, email=:email, password=:password";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $hashedPassword);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Login
    public function login() {
        $query = "SELECT id, nom, email, password FROM " . $this->table . " 
                  WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->nom = $row['nom'];
                $this->email = $row['email'];
                return true;
            }
        }

        return false;
    }

    // Get account by ID
    public function getAccount() {
        $query = "SELECT id, nom, email FROM " . $this->table . " 
                  WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            return true;
        }

        return false;
    }

    // Get account by email
    public function getByEmail() {
        $query = "SELECT id, nom, email FROM " . $this->table . " 
                  WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            return true;
        }

        return false;
    }
}
?>
