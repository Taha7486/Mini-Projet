<?php
class Club {
    private $conn;
    private $table = "clubs";

    public $club_id;
    public $nom;
    public $description;
    public $created_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all clubs
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nom ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get club by ID
    public function getById($club_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE club_id = :club_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":club_id", $club_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add organizer to club
    public function addOrganizer($participant_id) {
        $query = "INSERT IGNORE INTO organizers 
                  SET participant_id=:participant_id, club_id=:club_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $participant_id);
        $stmt->bindParam(":club_id", $this->club_id);
        return $stmt->execute();
    }

    // List events for this club
    public function listEvents() {
        $query = "SELECT e.*, COUNT(i.registration_id) as registered_count
                  FROM events e
                  LEFT JOIN registered i ON e.event_id = i.event_id
                  WHERE e.club_id = :club_id
                  GROUP BY e.event_id
                  ORDER BY e.date_event ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":club_id", $this->club_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create club
    public function create($created_by = null) {
        $query = "INSERT INTO " . $this->table . " 
                  SET nom = :nom, description = :description, created_by = :created_by";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":created_by", $created_by);

        if ($stmt->execute()) {
            $this->club_id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Update club
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nom = :nom, description = :description
                  WHERE club_id = :club_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":club_id", $this->club_id);

        return $stmt->execute();
    }

    // Delete club
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE club_id = :club_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":club_id", $this->club_id);
        return $stmt->execute();
    }
}
?>
