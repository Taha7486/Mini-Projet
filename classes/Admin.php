<?php
require_once 'Account.php';

class Admin extends Account {
    private $table = "admins";

    public $admin_id;

    // Get admin profile
    public function getProfile() {
        $query = "SELECT ad.*, a.nom, a.email 
                  FROM " . $this->table . " ad
                  INNER JOIN accounts a ON ad.account_id = a.id
                  WHERE ad.account_id = :account_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":account_id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->admin_id = $row['admin_id'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            return true;
        }

        return false;
    }

    // Create club
    public function createClub($nom, $description = '') {
        $query = "INSERT INTO clubs 
                  SET nom=:nom, description=:description, created_by=:created_by";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":created_by", $this->admin_id);

        return $stmt->execute();
    }

    // Delete club
    public function deleteClub($club_id) {
        $query = "DELETE FROM clubs WHERE club_id = :club_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":club_id", $club_id);
        return $stmt->execute();
    }

    // Get all clubs
    public function getClubs() {
        $query = "SELECT c.*, a.nom as created_by_name,
                  (SELECT COUNT(*) FROM organisateurs WHERE club_id = c.club_id) as organizer_count,
                  (SELECT COUNT(*) FROM evenements WHERE club_id = c.club_id) as event_count
                  FROM clubs c
                  INNER JOIN admins ad ON c.created_by = ad.admin_id
                  INNER JOIN accounts a ON ad.account_id = a.id
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get club organizers
    public function getClubOrganizers($club_id) {
        $query = "SELECT p.participant_id, a.nom, a.email, p.student_id, p.department
                  FROM organisateurs o
                  INNER JOIN participants p ON o.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  WHERE o.club_id = :club_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":club_id", $club_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get organizer requests
    public function getOrganizerRequests($status = 'pending') {
        $query = "SELECT r.*, p.student_id, p.department, p.year,
                  a.nom as participant_name, a.email as participant_email,
                  c.nom as club_name
                  FROM organizer_requests r
                  INNER JOIN participants p ON r.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  INNER JOIN clubs c ON r.club_id = c.club_id
                  WHERE r.status = :status
                  ORDER BY r.requested_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Approve organizer request
    public function approveOrganizerRequest($request_id) {
        // Get request details
        $getQuery = "SELECT participant_id, club_id FROM organizer_requests 
                     WHERE request_id = :request_id";
        $getStmt = $this->conn->prepare($getQuery);
        $getStmt->bindParam(":request_id", $request_id);
        $getStmt->execute();
        $request = $getStmt->fetch(PDO::FETCH_ASSOC);

        if(!$request) {
            return false;
        }

        // Update request status
        $updateQuery = "UPDATE organizer_requests 
                        SET status='approved', decided_at=NOW(), decided_by=:decided_by 
                        WHERE request_id=:request_id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(":request_id", $request_id);
        $updateStmt->bindParam(":decided_by", $this->admin_id);
        $updateStmt->execute();

        // Add to organisateurs table
        $addQuery = "INSERT IGNORE INTO organisateurs 
                     SET participant_id=:participant_id, club_id=:club_id";
        $addStmt = $this->conn->prepare($addQuery);
        $addStmt->bindParam(":participant_id", $request['participant_id']);
        $addStmt->bindParam(":club_id", $request['club_id']);
        $addStmt->execute();

        // Update participant role
        $roleQuery = "UPDATE participants SET role='organizer' 
                      WHERE participant_id=:participant_id";
        $roleStmt = $this->conn->prepare($roleQuery);
        $roleStmt->bindParam(":participant_id", $request['participant_id']);
        $roleStmt->execute();

        return true;
    }

    // Reject organizer request
    public function rejectOrganizerRequest($request_id) {
        $query = "UPDATE organizer_requests 
                  SET status='rejected', decided_at=NOW(), decided_by=:decided_by 
                  WHERE request_id=:request_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":request_id", $request_id);
        $stmt->bindParam(":decided_by", $this->admin_id);

        return $stmt->execute();
    }

    // Create admin account
    public function createAdmin($nom, $email, $password, $student_id, $year, $department, $phone_number) {
        // Create account
        $account = new Account($this->conn);
        $account->nom = $nom;
        $account->email = $email;
        $account->password = $password;
        
        if(!$account->register()) {
            return false;
        }

        // Create admin record
        $query = "INSERT INTO admins SET account_id=:account_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":account_id", $account->id);

        return $stmt->execute();
    }

    // Manage users (get all users)
    public function manageUsers() {
        $query = "SELECT p.participant_id, p.student_id, p.year, p.department, p.phone_number, p.role,
                  a.nom, a.email,
                  (SELECT COUNT(*) FROM inscrit WHERE participant_id = p.participant_id) as events_registered,
                  (SELECT COUNT(*) FROM organisateurs WHERE participant_id = p.participant_id) as clubs_count
                  FROM participants p
                  INNER JOIN accounts a ON p.account_id = a.id
                  ORDER BY a.nom ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete user
    public function deleteUser($participant_id) {
        // Get account_id
        $query = "SELECT account_id FROM participants WHERE participant_id = :participant_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $participant_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Delete account (will cascade delete participant)
            $deleteQuery = "DELETE FROM accounts WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(":id", $row['account_id']);
            return $deleteStmt->execute();
        }

        return false;
    }

    // Get pending organizer requests
    public function getPendingRequests() {
        $query = "SELECT r.*, p.student_id, p.department, p.year, p.phone_number,
                  a.nom as user_name, a.email,
                  c.nom as club_name
                  FROM organizer_requests r
                  INNER JOIN participants p ON r.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  INNER JOIN clubs c ON r.club_id = c.club_id
                  WHERE r.status = 'pending'
                  ORDER BY r.requested_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all users
    public function getAllUsers() {
        $query = "SELECT p.participant_id, p.student_id, p.year, p.department, p.phone_number, p.role, p.created_at,
                  a.id as account_id, a.nom, a.email
                  FROM participants p
                  INNER JOIN accounts a ON p.account_id = a.id
                  ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all clubs
    public function getAllClubs() {
        $query = "SELECT * FROM clubs ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Change user role
    public function changeUserRole($accountId, $newRole) {
        // Get participant_id
        $query = "SELECT participant_id FROM participants WHERE account_id = :account_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":account_id", $accountId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        // Update role
        $updateQuery = "UPDATE participants SET role = :role WHERE participant_id = :participant_id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(":role", $newRole);
        $updateStmt->bindParam(":participant_id", $row['participant_id']);

        return $updateStmt->execute();
    }

    // Create organizer request
    public function createOrganizerRequest($participantId, $clubId) {
        // Check if request already exists for this participant and club
        $checkQuery = "SELECT request_id FROM organizer_requests 
                       WHERE participant_id = :participant_id 
                       AND club_id = :club_id 
                       AND status = 'pending'";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":participant_id", $participantId);
        $checkStmt->bindParam(":club_id", $clubId);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return true; // Request already exists
        }

        // Create new request
        $query = "INSERT INTO organizer_requests 
                  SET participant_id = :participant_id, club_id = :club_id, status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $participantId);
        $stmt->bindParam(":club_id", $clubId);

        return $stmt->execute();
    }
}
?>
