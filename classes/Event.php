<?php
class Event {
    private $conn;
    private $table = "events";

    public $event_id;
    public $title;
    public $description;
    public $location;
    public $date_event;
    public $start_time;
    public $end_time;
    public $capacity;
    public $registered_count;
    public $image_url;
    public $club_id;
    public $created_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all events
    public function getAll() {
        $query = "SELECT e.*, c.nom as club_name, 
                  a.nom as creator_name
                  FROM " . $this->table . " e
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  LEFT JOIN organizers o ON e.created_by = o.organizer_id
                  LEFT JOIN participants p ON o.participant_id = p.participant_id
                  LEFT JOIN accounts a ON p.account_id = a.id
                  ORDER BY e.date_event ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get event by ID
    public function getById($event_id) {
        $query = "SELECT e.*, c.nom as club_name,
                  a.nom as creator_name
                  FROM " . $this->table . " e
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  LEFT JOIN organizers o ON e.created_by = o.organizer_id
                  LEFT JOIN participants p ON o.participant_id = p.participant_id
                  LEFT JOIN accounts a ON p.account_id = a.id
                  WHERE e.event_id = :event_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Filter events by club
    public function getByClub($club_id) {
        $query = "SELECT e.*, c.nom as club_name
                  FROM " . $this->table . " e
                  INNER JOIN clubs c ON e.club_id = c.club_id
                  WHERE e.club_id = :club_id
                  ORDER BY e.date_event ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":club_id", $club_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
