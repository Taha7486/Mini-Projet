<?php
require_once 'Participant.php';

class Organizer extends Participant {
    private $org_table = "organizers";

    public $organizer_id;

    public function __construct($db) {
        parent::__construct($db);
    }

    // Get organizer info
    public function getInfo($participant_id) {
        $query = "SELECT * FROM " . $this->org_table . " 
                  WHERE participant_id = :participant_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $participant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if participant is organizer for club
    public function isOrganizerForClub($participant_id, $club_id) {
        $query = "SELECT organizer_id FROM " . $this->org_table . " 
                  WHERE participant_id = :participant_id AND club_id = :club_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $participant_id);
        $stmt->bindParam(":club_id", $club_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Create event
    public function createEvent($eventData) {
        $query = "INSERT INTO events 
                  SET title=:title, description=:description, location=:location,
                      date_event=:date_event, time_event=:time_event, capacity=:capacity,
                      image_url=:image_url, club_id=:club_id, created_by=:created_by";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $eventData['title']);
        $stmt->bindParam(":description", $eventData['description']);
        $stmt->bindParam(":location", $eventData['location']);
        $stmt->bindParam(":date_event", $eventData['date_event']);
        $stmt->bindParam(":time_event", $eventData['time_event']);
        $stmt->bindParam(":capacity", $eventData['capacity']);
        $stmt->bindParam(":image_url", $eventData['image_url']);
        $stmt->bindParam(":club_id", $eventData['club_id']);
        $stmt->bindParam(":created_by", $this->organizer_id);

        return $stmt->execute();
    }

    // Modify event
    public function modifyEvent($event_id, $eventData) {
        $query = "UPDATE events 
                  SET title=:title, description=:description, location=:location,
                      date_event=:date_event, time_event=:time_event, capacity=:capacity,
                      image_url=:image_url
                  WHERE event_id=:event_id AND created_by=:created_by";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":title", $eventData['title']);
        $stmt->bindParam(":description", $eventData['description']);
        $stmt->bindParam(":location", $eventData['location']);
        $stmt->bindParam(":date_event", $eventData['date_event']);
        $stmt->bindParam(":time_event", $eventData['time_event']);
        $stmt->bindParam(":capacity", $eventData['capacity']);
        $stmt->bindParam(":image_url", $eventData['image_url']);
        $stmt->bindParam(":created_by", $this->organizer_id);

        return $stmt->execute();
    }

    // Delete event
    public function deleteEvent($event_id) {
        $query = "DELETE FROM events 
                  WHERE event_id=:event_id AND created_by=:created_by";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":created_by", $this->organizer_id);

        return $stmt->execute();
    }

    // View participants of an event
    public function viewParticipants($event_id) {
        // First check if this organizer owns this event
        $checkQuery = "SELECT event_id FROM events 
                       WHERE event_id=:event_id AND created_by=:created_by";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":event_id", $event_id);
        $checkStmt->bindParam(":created_by", $this->organizer_id);
        $checkStmt->execute();

        if($checkStmt->rowCount() == 0) {
            return false;
        }

        $query = "SELECT p.participant_id, p.student_id, p.year, p.department, p.phone_number,
                  a.nom, a.email, i.registered_at
                  FROM registered i
                  INNER JOIN participants p ON i.participant_id = p.participant_id
                  INNER JOIN accounts a ON p.account_id = a.id
                  WHERE i.event_id = :event_id
                  ORDER BY i.registered_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get my events (events created by this organizer)
    public function getMyEvents() {
        $query = "SELECT e.*, c.nom as club_name
                  FROM events e
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  WHERE e.created_by = :created_by
                  ORDER BY e.date_event ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":created_by", $this->organizer_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get managed clubs
    public function getManagedClubs() {
        $query = "SELECT c.*, o.organizer_id
                  FROM " . $this->org_table . " o
                  INNER JOIN clubs c ON o.club_id = c.club_id
                  WHERE o.participant_id = :participant_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get my clubs (uses parent's method)
    public function getMyClubsLegacy($participant_id) {
        $query = "SELECT c.*, o.organizer_id
                  FROM " . $this->org_table . " o
                  INNER JOIN clubs c ON o.club_id = c.club_id
                  WHERE o.participant_id = :participant_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $participant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get organizer profile with organizer_id
    public function getProfile() {
        // First get participant profile
        if (!parent::getProfile()) {
            return false;
        }

        // Then get organizer_id from the first club they manage
        $query = "SELECT organizer_id FROM " . $this->org_table . " 
                  WHERE participant_id = :participant_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":participant_id", $this->participant_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->organizer_id = $row['organizer_id'];
        }

        return true;
    }
}
?>
