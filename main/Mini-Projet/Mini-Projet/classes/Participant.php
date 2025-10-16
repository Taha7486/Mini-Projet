<?php
require_once 'Account.php';

class Participant extends Account {
    private $table = "participants";

    public $participant_id;
    public $account_id;
    public $student_id;
    public $year;
    public $department;
    public $phone_number;
    public $role;

    // Register as participant
    public function registerParticipant($nom, $email, $password, $student_id, $year, $department, $phone_number) {
        // Set account properties
        $this->nom = $nom;
        $this->email = $email;
        $this->password = $password;
        
        // First create account
        if(!parent::register()) {
            return false;
        }

        // Then create participant record
        $query = "INSERT INTO " . $this->table . " 
                  SET account_id=:account_id, student_id=:student_id, 
                      year=:year, department=:department, phone_number=:phone_number";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":account_id", $this->id);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":year", $year);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":phone_number", $phone_number);

        if($stmt->execute()) {
            $this->participant_id = $this->conn->lastInsertId();
            $this->student_id = $student_id;
            $this->year = $year;
            $this->department = $department;
            $this->phone_number = $phone_number;
            return true;
        }

        return false;
    }

    // Get participant profile
    public function getProfile() {
        $query = "SELECT p.*, a.nom, a.email 
                  FROM " . $this->table . " p
                  INNER JOIN accounts a ON p.account_id = a.id
                  WHERE p.account_id = :account_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":account_id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->participant_id = $row['participant_id'];
            $this->account_id = $row['account_id'];
            $this->student_id = $row['student_id'];
            $this->year = $row['year'];
            $this->department = $row['department'];
            $this->phone_number = $row['phone_number'];
            $this->role = $row['role'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            return true;
        }

        return false;
    }

    // Request organizer role
    public function requestOrganizerRole($club_id) {
        $query = "INSERT INTO organizer_requests 
                  SET participant_id=:participant_id, club_id=:club_id, 
                      request_type='become_organizer'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->bindParam(":club_id", $club_id);

        return $stmt->execute();
    }

    // Register for event
    public function registerForEvent($event_id) {
        // Check if already registered
        $checkQuery = "SELECT registration_id FROM registered 
                       WHERE participant_id=:participant_id AND event_id=:event_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":participant_id", $this->participant_id);
        $checkStmt->bindParam(":event_id", $event_id);
        $checkStmt->execute();

        if($checkStmt->rowCount() > 0) {
            return false; // Already registered
        }

        // Register for event
        $query = "INSERT INTO registered 
                  SET participant_id=:participant_id, event_id=:event_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->bindParam(":event_id", $event_id);

        if($stmt->execute()) {
            // Update registered count
            $updateQuery = "UPDATE events 
                           SET registered_count = registered_count + 1 
                           WHERE event_id = :event_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":event_id", $event_id);
            $updateStmt->execute();

            return true;
        }

        return false;
    }

    // Get events (all public events)
    public function getEvents() {
        $query = "SELECT e.*, c.nom as club_name, a.nom as creator_name
                  FROM events e
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  INNER JOIN organizers o ON e.created_by = o.organizer_id
                  INNER JOIN participants p ON o.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  ORDER BY e.date_event ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get my registered events
    public function getMyEvents() {
        $query = "SELECT e.*, c.nom as club_name
                  FROM events e
                  INNER JOIN registered i ON e.event_id = i.event_id
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  WHERE i.participant_id = :participant_id
                  ORDER BY e.date_event ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get clubs assigned to this participant (if organizer)
    public function getMyClubs() {
        $query = "SELECT c.*, COUNT(e.event_id) as event_count
                  FROM organizers o
                  INNER JOIN clubs c ON o.club_id = c.club_id
                  LEFT JOIN events e ON c.club_id = e.club_id
                  WHERE o.participant_id = :participant_id
                  GROUP BY c.club_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alias for getMyClubs
    public function getClubs() {
        return $this->getMyClubs();
    }

    // Get organizer requests for this participant
    public function getOrganizerRequests() {
        $query = "SELECT r.*, c.nom as club_name
                  FROM organizer_requests r
                  INNER JOIN clubs c ON r.club_id = c.club_id
                  WHERE r.participant_id = :participant_id
                  ORDER BY r.requested_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
