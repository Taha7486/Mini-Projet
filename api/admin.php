<?php
// Content type will be set dynamically depending on request type
require_once '../config/database.php';
require_once '../classes/Admin.php';
require_once '../classes/Club.php';
require_once '../includes/session.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Support both JSON and HTML form submissions
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = stripos($contentType, 'application/json') === 0;
if ($isJson) {
    header('Content-Type: application/json');
}

if ($isJson) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
} else {
    $input = $_POST;
    $action = $_POST['action'] ?? '';
}

$database = new Database();
$db = $database->getConnection();

try {
    switch ($action) {
        case 'request_organizer':
            // Regular users can request organizer access
            if (!isLoggedIn()) {
                throw new Exception('You must be logged in');
            }

            $clubIds = $input['club_ids'] ?? [];
            if (empty($clubIds)) {
                throw new Exception('Please select at least one club');
            }

            $admin = new Admin($db);
            foreach ($clubIds as $clubId) {
                $success = $admin->createOrganizerRequest($_SESSION['participant_id'], $clubId);
                if (!$success) {
                    throw new Exception('Failed to create request');
                }
            }

            echo json_encode(['success' => true, 'message' => 'Request submitted successfully']);
            break;

        case 'approve_organizer_request':
            // Only admins can approve requests
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $requestId = $input['request_id'] ?? null;
            if (!$requestId) {
                throw new Exception('Request ID is required');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->approveOrganizerRequest($requestId);
            if (!$success) {
                throw new Exception('Failed to approve request');
            }

            echo json_encode(['success' => true, 'message' => 'Request approved successfully']);
            break;

        case 'reject_organizer_request':
            // Only admins can reject requests
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $requestId = $input['request_id'] ?? null;
            if (!$requestId) {
                throw new Exception('Request ID is required');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->rejectOrganizerRequest($requestId);
            if (!$success) {
                throw new Exception('Failed to reject request');
            }

            echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
            break;

        case 'create_club':
            // Only admins can create clubs
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $nom = trim($input['nom'] ?? '');
            $description = trim($input['description'] ?? '');

            if (empty($nom)) {
                throw new Exception('Club name is required');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            if (!$admin->getProfile()) {
                throw new Exception('Admin profile not found');
            }

            $club = new Club($db);
            $club->nom = $nom;
            $club->description = $description;

            $success = $club->create($admin->admin_id);
            if (!$success) {
                throw new Exception('Failed to create club');
            }

            echo json_encode(['success' => true, 'message' => 'Club created successfully']);
            break;

        case 'update_club':
            // Only admins can update clubs
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $clubId = $input['club_id'] ?? null;
            $nom = trim($input['nom'] ?? '');
            $description = trim($input['description'] ?? '');

            if (!$clubId || empty($nom)) {
                throw new Exception('Club ID and name are required');
            }

            $club = new Club($db);
            $club->club_id = $clubId;
            $club->nom = $nom;
            $club->description = $description;

            $success = $club->update();
            if (!$success) {
                throw new Exception('Failed to update club');
            }

            echo json_encode(['success' => true, 'message' => 'Club updated successfully']);
            break;

        case 'delete_club':
            // Only admins can delete clubs
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $clubId = $input['club_id'] ?? null;
            if (!$clubId) {
                throw new Exception('Club ID is required');
            }

            $club = new Club($db);
            $club->club_id = $clubId;

            $success = $club->delete();
            if (!$success) {
                throw new Exception('Failed to delete club');
            }

            echo json_encode(['success' => true, 'message' => 'Club deleted successfully']);
            break;

        case 'change_user_role':
            // Only admins can change user roles
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $accountId = $input['account_id'] ?? null;
            $newRole = $input['new_role'] ?? null;
            $clubId = $input['club_id'] ?? null;

            if (!$accountId || !$newRole) {
                throw new Exception('Account ID and new role are required');
            }

            if (!in_array($newRole, ['user', 'organizer', 'admin'])) {
                throw new Exception('Invalid role');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->changeUserRole($accountId, $newRole);
            if (!$success) {
                throw new Exception('Failed to change user role');
            }

            // If promoting to organizer and a club is specified, ensure organizer-club link exists
            if ($newRole === 'organizer' && !empty($clubId)) {
                // Lookup participant_id by account_id
                $stmt = $db->prepare("SELECT participant_id FROM participants WHERE account_id = :account_id");
                $stmt->bindParam(':account_id', $accountId);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['participant_id'])) {
                    $participantId = (int)$row['participant_id'];
                    // Insert organizer assignment if not exists
                    $ins = $db->prepare("INSERT IGNORE INTO organizers (participant_id, club_id) VALUES (:pid, :cid)");
                    $ins->bindParam(':pid', $participantId);
                    $ins->bindParam(':cid', $clubId);
                    $ins->execute();
                }
            }

            if ($isJson) {
                echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
            } else {
                // Redirect back to admin panel for non-JS form submissions
                header('Location: ../public/admin-panel.php?role_updated=1');
            }
            break;

        case 'create_admin':
            // Only admins can create other admins
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $nom = trim($input['nom'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($nom) || empty($email) || empty($password)) {
                throw new Exception('Name, email and password are required');
            }

            // Basic email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];

            $success = $admin->createAdmin($nom, $email, $password);
            if (!$success) {
                throw new Exception('Failed to create admin (email may already exist)');
            }

            echo json_encode(['success' => true, 'message' => 'Admin created successfully']);
            break;

        case 'delete_user':
            // Only admins can delete users
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $accountId = $input['account_id'] ?? null;
            if (!$accountId) {
                throw new Exception('Account ID is required');
            }

            // Prevent admin from deleting themselves
            if ($accountId == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->deleteUser($accountId);
            if (!$success) {
                throw new Exception('Failed to delete user');
            }

            if ($isJson) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                header('Location: ../public/admin-panel.php?deleted=1');
            }
            break;

        case 'toggle_user_status':
            // Only admins can toggle user status
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $accountId = $input['account_id'] ?? null;
            $isActive = $input['is_active'] ?? null;

            if (!$accountId || $isActive === null) {
                throw new Exception('Account ID and status are required');
            }

            // Prevent admin from deactivating themselves
            if ($accountId == $_SESSION['user_id'] && !$isActive) {
                throw new Exception('You cannot deactivate your own account');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->toggleUserStatus($accountId, $isActive);
            if (!$success) {
                throw new Exception('Failed to update user status');
            }

            $statusText = $isActive ? 'activated' : 'deactivated';
            echo json_encode(['success' => true, 'message' => "User {$statusText} successfully"]);
            break;

        case 'update_event':
            // Only admins can update events
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $eventId = $input['event_id'] ?? null;
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $dateEvent = $input['date_event'] ?? '';
            $startTime = trim($input['start_time'] ?? '');
            $endTime = trim($input['end_time'] ?? '');
            $location = trim($input['location'] ?? '');
            $capacity = (int)($input['capacity'] ?? 0);
            $imageUrl = trim($input['image_url'] ?? '');
            $clubId = $input['club_id'] ?? null;

            if (!$eventId || empty($title) || empty($description) || empty($dateEvent) || 
                empty($startTime) || empty($endTime) || empty($location) || $capacity <= 0 || 
                empty($imageUrl) || !$clubId) {
                throw new Exception('All fields are required');
            }

            // Validate that end time is after start time
            if ($startTime >= $endTime) {
                throw new Exception('End time must be after start time');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->updateEvent($eventId, $title, $description, $dateEvent, 
                                          $startTime, $endTime, $location, $capacity, $imageUrl, $clubId);
            if (!$success) {
                throw new Exception('Failed to update event');
            }

            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            break;

        case 'delete_event':
            // Only admins can delete events
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $eventId = $input['event_id'] ?? null;
            if (!$eventId) {
                throw new Exception('Event ID is required');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $success = $admin->deleteEvent($eventId);
            if (!$success) {
                throw new Exception('Failed to delete event');
            }

            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
            break;

        case 'get_event_participants':
            // Only admins can view event participants
            if (!isAdmin()) {
                throw new Exception('Unauthorized');
            }

            $eventId = $input['event_id'] ?? null;
            if (!$eventId) {
                throw new Exception('Event ID is required');
            }

            $admin = new Admin($db);
            $admin->id = $_SESSION['user_id'];
            
            $participants = $admin->getEventParticipants($eventId);
            
            // Get event title for display
            $eventQuery = "SELECT title FROM events WHERE event_id = :event_id";
            $eventStmt = $db->prepare($eventQuery);
            $eventStmt->bindParam(':event_id', $eventId);
            $eventStmt->execute();
            $event = $eventStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'participants' => $participants,
                'event_title' => $event['title'] ?? 'Unknown Event'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
