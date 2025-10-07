<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once '../config/database.php';
    require_once '../classes/Evenement.php';
    require_once '../classes/Organisateur.php';
    require_once '../includes/session.php';

    $database = new Database();
    $db = $database->getConnection();

    if(!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch($action) {
        case 'create':
            handleCreateEvent($db, $input);
            break;
        case 'update':
            handleUpdateEvent($db, $input);
            break;
        case 'delete':
            handleDeleteEvent($db, $input);
            break;
        case 'get_participants':
            handleGetParticipants($db, $input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function handleCreateEvent($db, $input) {
    // Check if user is logged in and is organizer or admin
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }

    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $location = trim($input['location'] ?? '');
    $date_event = $input['date_event'] ?? '';
    $time_event = $input['time_event'] ?? '';
    $capacity = intval($input['capacity'] ?? 0);
    $image_url = trim($input['image_url'] ?? '');
    $club_id = intval($input['club_id'] ?? 0);

    if(empty($title) || empty($description) || empty($location) || empty($date_event) || 
       empty($time_event) || $capacity <= 0 || $club_id <= 0) {
        throw new Exception('All fields are required');
    }

    $organizer = new Organisateur($db);
    $organizer->id = $_SESSION['user_id'];
    
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    // Check if organizer manages this club
    if (!isAdmin() && !$organizer->isOrganizerForClub($organizer->participant_id, $club_id)) {
        throw new Exception('You are not authorized to create events for this club');
    }

    $eventData = [
        'title' => $title,
        'description' => $description,
        'location' => $location,
        'date_event' => $date_event,
        'time_event' => $time_event,
        'capacity' => $capacity,
        'image_url' => $image_url,
        'club_id' => $club_id
    ];

    if($organizer->createEvent($eventData)) {
        echo json_encode(['success' => true, 'message' => 'Event created successfully']);
    } else {
        throw new Exception('Failed to create event');
    }
}

function handleUpdateEvent($db, $input) {
    // Check if user is logged in and is organizer or admin
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }

    $event_id = intval($input['event_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $location = trim($input['location'] ?? '');
    $date_event = $input['date_event'] ?? '';
    $time_event = $input['time_event'] ?? '';
    $capacity = intval($input['capacity'] ?? 0);
    $image_url = trim($input['image_url'] ?? '');

    if($event_id <= 0 || empty($title) || empty($description) || empty($location) || 
       empty($date_event) || empty($time_event) || $capacity <= 0) {
        throw new Exception('All fields are required');
    }

    $organizer = new Organisateur($db);
    $organizer->id = $_SESSION['user_id'];
    
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    $eventData = [
        'title' => $title,
        'description' => $description,
        'location' => $location,
        'date_event' => $date_event,
        'time_event' => $time_event,
        'capacity' => $capacity,
        'image_url' => $image_url
    ];

    if($organizer->modifyEvent($event_id, $eventData)) {
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
    } else {
        throw new Exception('Failed to update event or you are not authorized');
    }
}

function handleDeleteEvent($db, $input) {
    // Check if user is logged in and is organizer or admin
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }

    $event_id = intval($input['event_id'] ?? 0);

    if($event_id <= 0) {
        throw new Exception('Event ID is required');
    }

    $organizer = new Organisateur($db);
    $organizer->id = $_SESSION['user_id'];
    
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    if($organizer->deleteEvent($event_id)) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        throw new Exception('Failed to delete event or you are not authorized');
    }
}

function handleGetParticipants($db, $input) {
    // Check if user is logged in and is organizer or admin
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }

    $event_id = intval($input['event_id'] ?? 0);

    if($event_id <= 0) {
        throw new Exception('Event ID is required');
    }

    $organizer = new Organisateur($db);
    $organizer->id = $_SESSION['user_id'];
    
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    $participants = $organizer->viewParticipants($event_id);
    
    if($participants !== false) {
        echo json_encode(['success' => true, 'participants' => $participants]);
    } else {
        throw new Exception('Failed to load participants or you are not authorized');
    }
}
?>





