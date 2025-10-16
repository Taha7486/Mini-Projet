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
                  (SELECT COUNT(*) FROM organizers WHERE club_id = c.club_id) as organizer_count,
                  (SELECT COUNT(*) FROM events WHERE club_id = c.club_id) as event_count
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
                  FROM organizers o
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

        // Add to organizers table
        $addQuery = "INSERT IGNORE INTO organizers 
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
    public function createAdmin($nom, $email, $password) {
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
                  (SELECT COUNT(*) FROM registered WHERE participant_id = p.participant_id) as events_registered,
                  (SELECT COUNT(*) FROM organizers WHERE participant_id = p.participant_id) as clubs_count
                  FROM participants p
                  INNER JOIN accounts a ON p.account_id = a.id
                  ORDER BY a.nom ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete user (by account_id)
    public function deleteUser($account_id) {
        // If the account belongs to a participant who is organizer, delete their events first
        // Find participant_id by account_id
        $participantStmt = $this->conn->prepare("SELECT participant_id FROM participants WHERE account_id = :account_id");
        $participantStmt->bindParam(":account_id", $account_id);
        $participantStmt->execute();
        $participant = $participantStmt->fetch(PDO::FETCH_ASSOC);

        if ($participant && !empty($participant['participant_id'])) {
            $participantId = (int)$participant['participant_id'];
            
            // First, decrement registered_count for events where this participant was registered
            $decrementStmt = $this->conn->prepare("
                UPDATE events 
                SET registered_count = registered_count - 1 
                WHERE event_id IN (
                    SELECT event_id FROM registered 
                    WHERE participant_id = :participant_id
                ) AND registered_count > 0
            ");
            $decrementStmt->bindParam(":participant_id", $participantId);
            $decrementStmt->execute();
            
            // Find organizer ids for this participant
            $orgStmt = $this->conn->prepare("SELECT organizer_id FROM organizers WHERE participant_id = :pid");
            $orgStmt->bindParam(":pid", $participantId);
            $orgStmt->execute();
            $organizerIds = $orgStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($organizerIds)) {
                // Delete events created by these organizers (will cascade registered, emails)
                $in = implode(',', array_fill(0, count($organizerIds), '?'));
                $delEvents = $this->conn->prepare("DELETE FROM events WHERE created_by IN ($in)");
                foreach ($organizerIds as $idx => $oid) {
                    $delEvents->bindValue($idx + 1, (int)$oid, PDO::PARAM_INT);
                }
                $delEvents->execute();
            }
        }

        // Check if it's an admin account and delete admin record first
        $adminQuery = "SELECT admin_id FROM admins WHERE account_id = :account_id";
        $adminStmt = $this->conn->prepare($adminQuery);
        $adminStmt->bindParam(":account_id", $account_id);
        $adminStmt->execute();
        if ($adminStmt->rowCount() > 0) {
            $deleteAdminQuery = "DELETE FROM admins WHERE account_id = :account_id";
            $deleteAdminStmt = $this->conn->prepare($deleteAdminQuery);
            $deleteAdminStmt->bindParam(":account_id", $account_id);
            $deleteAdminStmt->execute();
        }

        // Finally, delete account (cascades participants, organizers)
        $deleteQuery = "DELETE FROM accounts WHERE id = :id";
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->bindParam(":id", $account_id);
        return $deleteStmt->execute();
    }

    // Deactivate/Activate user account
    public function toggleUserStatus($account_id, $is_active) {
        // Add an 'active' column to accounts table if it doesn't exist
        // For now, we'll use a simple approach by updating a status field
        // This would require adding an 'active' column to the accounts table
        $query = "UPDATE accounts SET active = :active WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":active", $is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $account_id);
        return $stmt->execute();
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
        // Return both participants and admins in a unified list
        // If account is admin, role = 'admin' and participant fields may be NULL
        $query = "SELECT 
                    p.participant_id,
                    p.student_id,
                    p.year,
                    p.department,
                    p.phone_number,
                    CASE WHEN ad.admin_id IS NOT NULL THEN 'admin' ELSE p.role END AS role,
                    COALESCE(p.created_at, a.created_at) AS created_at,
                    a.id AS account_id,
                    a.nom,
                    a.email,
                    (
                      SELECT GROUP_CONCAT(c.nom SEPARATOR ', ')
                      FROM organizers o
                      JOIN clubs c ON c.club_id = o.club_id
                      WHERE o.participant_id = p.participant_id
                    ) AS organizer_clubs
                  FROM accounts a
                  LEFT JOIN participants p ON p.account_id = a.id
                  LEFT JOIN admins ad ON ad.account_id = a.id
                  WHERE p.participant_id IS NOT NULL OR ad.admin_id IS NOT NULL
                  ORDER BY created_at DESC";

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

    // Get organizer request history
    public function getOrganizerRequestHistory() {
        $query = "SELECT r.*, p.student_id, p.department, p.year, p.phone_number,
                  a.nom as user_name, a.email,
                  c.nom as club_name,
                  ad_acc.nom as decided_by_name
                  FROM organizer_requests r
                  INNER JOIN participants p ON r.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  INNER JOIN clubs c ON r.club_id = c.club_id
                  LEFT JOIN admins ad ON r.decided_by = ad.admin_id
                  LEFT JOIN accounts ad_acc ON ad.account_id = ad_acc.id
                  WHERE r.status != 'pending'
                  ORDER BY r.decided_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // Get all events
    public function getAllEvents() {
        $query = "SELECT e.*, c.nom as club_name,
                  (SELECT COUNT(*) FROM registered WHERE event_id = e.event_id) as registered_count
                  FROM events e
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  ORDER BY e.date_event DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update event
    public function updateEvent($eventId, $title, $description, $dateEvent, $startTime, $endTime, $location, $capacity, $imageUrl, $clubId) {
        $query = "UPDATE events 
                  SET title = :title, description = :description, date_event = :date_event, 
                      start_time = :start_time, end_time = :end_time, location = :location, capacity = :capacity, 
                      image_url = :image_url, club_id = :club_id
                  WHERE event_id = :event_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":date_event", $dateEvent);
        $stmt->bindParam(":start_time", $startTime);
        $stmt->bindParam(":end_time", $endTime);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":capacity", $capacity);
        $stmt->bindParam(":image_url", $imageUrl);
        $stmt->bindParam(":club_id", $clubId);
        $stmt->bindParam(":event_id", $eventId);

        return $stmt->execute();
    }

    // Delete event
    public function deleteEvent($eventId) {
        $query = "DELETE FROM events WHERE event_id = :event_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $eventId);
        return $stmt->execute();
    }

    // Get event participants
    public function getEventParticipants($eventId) {
        $query = "SELECT p.participant_id, a.nom, a.email, p.student_id, p.department, p.year,
                  r.registered_at
                  FROM registered r
                  INNER JOIN participants p ON r.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  WHERE r.event_id = :event_id
                  ORDER BY r.registered_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $eventId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
