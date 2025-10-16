<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once '../config/database.php';
    require_once '../classes/Event.php';
    require_once '../classes/Organizer.php';
    require_once '../classes/Participant.php';
    // Ensure Composer libraries (PHPMailer, Dompdf, etc.) are autoloaded
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
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

// Handle both JSON and FormData requests
$input = [];
$action = '';

if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
} else {
    // Handle FormData (for file uploads)
    $input = $_POST;
    $action = $_POST['action'] ?? '';
}

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
        case 'register':
            handleRegisterForEvent($db, $input);
            break;
        case 'send_emails':
            handleSendEmails($db, $input);
            break;
        case 'send_attestations':
            handleSendAttestations($db, $input);
            break;
        case 'send_custom_email':
            handleSendCustomEmail($db, $input);
            break;
        case 'get_email_history':
            handleGetEmailHistory($db, $input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function handleCreateEvent($db, $input) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }

    // Disallow admins from creating events
    if (isAdmin()) {
        throw new Exception('Admins cannot create events');
    }

    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $location = trim($input['location'] ?? '');
    $date_event = $input['date_event'] ?? '';
    $start_time = $input['start_time'] ?? '';
    $end_time = $input['end_time'] ?? '';
    $capacity = intval($input['capacity'] ?? 0);
    $club_id = intval($input['club_id'] ?? 0);

    if(empty($title) || empty($description) || empty($location) || empty($date_event) || 
       empty($start_time) || empty($end_time) || $capacity <= 0 || $club_id <= 0) {
        throw new Exception('All fields are required');
    }

    // Validate that end time is after start time
    if ($start_time >= $end_time) {
        throw new Exception('End time must be after start time');
    }

    $organizer = new Organizer($db);
    $organizer->id = $_SESSION['user_id'];
    
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    // Check if organizer manages this club
    if (!$organizer->isOrganizerForClub($organizer->participant_id, $club_id)) {
        throw new Exception('You are not authorized to create events for this club');
    }

    // Handle image upload
    $image_url = 'assets/images/no_image_placeholder.png'; // Default fallback
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../storage/event_images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['event_image'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File too large. Maximum size is 5MB.');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'event_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $image_url = 'storage/event_images/' . $filename;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    $eventData = [
        'title' => $title,
        'description' => $description,
        'location' => $location,
        'date_event' => $date_event,
        'start_time' => $start_time,
        'end_time' => $end_time,
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
    $start_time = $input['start_time'] ?? '';
    $end_time = $input['end_time'] ?? '';
    $capacity = intval($input['capacity'] ?? 0);
    $club_id = intval($input['club_id'] ?? 0);

    if($event_id <= 0 || empty($title) || empty($description) || empty($location) || 
       empty($date_event) || empty($start_time) || empty($end_time) || $capacity <= 0 || $club_id <= 0) {
        throw new Exception('All fields are required');
    }

    // Validate that end time is after start time
    if ($start_time >= $end_time) {
        throw new Exception('End time must be after start time');
    }

    // Get current event data to check for existing image
    $currentEventQuery = "SELECT image_url FROM events WHERE event_id = :event_id";
    $currentEventStmt = $db->prepare($currentEventQuery);
    $currentEventStmt->bindParam(':event_id', $event_id);
    $currentEventStmt->execute();
    $currentEvent = $currentEventStmt->fetch(PDO::FETCH_ASSOC);
    $oldImageUrl = $currentEvent ? $currentEvent['image_url'] : null;

    // Handle image upload for updates
    $image_url = $input['image_url'];
    
    // Check if user wants to delete current image (set to placeholder)
    if ($image_url === 'assets/images/no_image_placeholder.png') {
        // Delete old image if it exists and is not a placeholder
        if ($oldImageUrl && 
            strpos($oldImageUrl, 'no_image_placeholder') === false && 
            strpos($oldImageUrl, 'storage/event_images/') === 0) {
            $oldImagePath = '../' . $oldImageUrl;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    } else if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        // Delete old image if uploading new one
        if ($oldImageUrl && 
            strpos($oldImageUrl, 'no_image_placeholder') === false && 
            strpos($oldImageUrl, 'storage/event_images/') === 0) {
            $oldImagePath = '../' . $oldImageUrl;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $uploadDir = '../storage/event_images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['event_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WebP are allowed.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File too large. Maximum size is 5MB.');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'event_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $image_url = 'storage/event_images/' . $filename;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    // Handle admin access
    if (isAdmin()) {
        require_once '../classes/Admin.php';
        $admin = new Admin($db);
        $admin->id = $_SESSION['user_id'];
        
        if($admin->updateEvent($event_id, $title, $description, $date_event, $start_time, $end_time, 
                              $location, $capacity, $image_url, $club_id)) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            throw new Exception('Failed to update event');
        }
    } else {
        // Handle organizer access
        $organizer = new Organizer($db);
        $organizer->id = $_SESSION['user_id'];
        
        if (!$organizer->getProfile()) {
            throw new Exception('Organizer profile not found');
        }

        $eventData = [
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'date_event' => $date_event,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'capacity' => $capacity,
            'image_url' => $image_url,
            'club_id' => $club_id
        ];

        if($organizer->modifyEvent($event_id, $eventData, false)) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            throw new Exception('Failed to update event or you are not authorized');
        }
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

    // Handle admin access
    if (isAdmin()) {
        require_once '../classes/Admin.php';
        $admin = new Admin($db);
        $admin->id = $_SESSION['user_id'];
        
        if($admin->deleteEvent($event_id)) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            throw new Exception('Failed to delete event');
        }
    } else {
        // Handle organizer access
        $organizer = new Organizer($db);
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

    // Handle admin access
    if (isAdmin()) {
        require_once '../classes/Admin.php';
        $admin = new Admin($db);
        $admin->id = $_SESSION['user_id'];
        
        $participants = $admin->getEventParticipants($event_id);
        
        if($participants !== false) {
            echo json_encode(['success' => true, 'participants' => $participants]);
        } else {
            throw new Exception('Failed to load participants');
        }
    } else {
        // Handle organizer access
        $organizer = new Organizer($db);
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
}

function handleRegisterForEvent($db, $input) {
    // Must be logged in and not an admin
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }
    if (isAdmin()) {
        throw new Exception('Admins cannot register for events');
    }

    $event_id = intval($input['event_id'] ?? 0);
    if ($event_id <= 0) {
        throw new Exception('Event ID is required');
    }

    $participant = new Participant($db);
    $participant->id = $_SESSION['user_id'];
    if (!$participant->getProfile()) {
        throw new Exception('Participant profile not found');
    }

    if ($participant->registerForEvent($event_id)) {
        echo json_encode(['success' => true, 'message' => 'Registered successfully']);
    } else {
        throw new Exception('Already registered or registration failed');
    }
}

function handleSendAttestations($db, $input) {
    // Only organizers who own the event can send attestations; admins are not allowed
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }
    if (isAdmin()) {
        throw new Exception('Admins are not allowed to send attestations');
    }

    $event_id = intval($input['event_id'] ?? 0);
    $participant_ids = $input['participant_ids'] ?? null; // required selection by organizer

    if ($event_id <= 0) {
        throw new Exception('Event ID is required');
    }
    if (!is_array($participant_ids) || count($participant_ids) === 0) {
        throw new Exception('Please select at least one participant');
    }

    require_once '../classes/Organizer.php';
    require_once '../services/Mailer.php';
    require_once '../services/AttestationPdfService.php';

    $organizer = new Organizer($db);
    $organizer->id = $_SESSION['user_id'];
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    // Ensure the organizer owns the event and fetch details
    $checkQuery = "SELECT event_id, title, date_event, start_time, end_time, location FROM events WHERE event_id=:event_id AND created_by=:created_by";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":event_id", $event_id);
    $checkStmt->bindParam(":created_by", $organizer->organizer_id);
    $checkStmt->execute();
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        throw new Exception('You are not authorized to send attestations for this event');
    }

    // Fetch selected participants (and their registration IDs)
    $params = [":event_id" => $event_id];
    $placeholders = [];
    foreach ($participant_ids as $idx => $pid) {
        $ph = ":pid_" . $idx;
        $placeholders[] = $ph;
        $params[$ph] = (int)$pid;
    }
    $q = "SELECT i.registration_id, p.participant_id, a.nom, a.email
          FROM registered i
          INNER JOIN participants p ON i.participant_id = p.participant_id
          INNER JOIN accounts a ON p.account_id = a.id
          WHERE i.event_id = :event_id AND p.participant_id IN (" . implode(",", $placeholders) . ")";
    $stmt = $db->prepare($q);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows || count($rows) === 0) {
        echo json_encode(['success' => true, 'sent' => 0, 'results' => []]);
        return;
    }

    $pdfService = new AttestationPdfService();
    $mailer = new Mailer();

    $sent = 0;
    $results = [];

    foreach ($rows as $row) {
        $registrationId = (int)$row['registration_id'];
        $participant = ['nom' => $row['nom'], 'email' => $row['email']];

        // Generate or reuse existing attestation
        $pdfPath = null;
        if (class_exists('Dompdf\\Dompdf')) {
            $pdfPath = $pdfService->generateForRegistration($db, $registrationId, $event, $participant, $organizer->nom);
        }

        // Upsert into attestations table if pdf exists
        if ($pdfPath) {
            $upsert = $db->prepare(
                "INSERT INTO attestations (registration_id, pdf_path) VALUES (:rid, :path)
                 ON DUPLICATE KEY UPDATE pdf_path = VALUES(pdf_path), generated_at = CURRENT_TIMESTAMP"
            );
            $upsert->bindValue(":rid", $registrationId, PDO::PARAM_INT);
            $upsert->bindValue(":path", $pdfPath, PDO::PARAM_STR);
            $upsert->execute();
        }

        // Build email
        $toEmail = $row['email'];
        $toName = $row['nom'];
        $subject = 'Your Attendance Certificate - ' . $event['title'];
        $htmlBody = '<p>Dear ' . htmlspecialchars($toName) . ',</p>' .
                    '<p>Please find attached your attendance certificate for <strong>' . htmlspecialchars($event['title']) . '</strong>.</p>' .
                    '<p>Event details: ' . htmlspecialchars($event['date_event']) . ' from ' . date('g:i A', strtotime($event['start_time'])) . ' to ' . date('g:i A', strtotime($event['end_time'])) . ' - ' . htmlspecialchars($event['location']) . '.</p>' .
                    '<p>Best regards,<br/>' . htmlspecialchars($organizer->nom) . '</p>';

        $attachments = [];
        if ($pdfPath && file_exists($pdfPath)) { $attachments[] = $pdfPath; }

        $ok = $mailer->sendEmail($toEmail, $toName, $subject, $htmlBody, $attachments);
        $results[] = [
            'participant_id' => (int)$row['participant_id'],
            'email' => $toEmail,
            'sent' => (bool)$ok,
            'pdf' => $pdfPath ?: null,
            'error' => $ok ? null : (method_exists($mailer, 'getLastError') ? $mailer->getLastError() : 'Unknown error')
        ];
        if ($ok) { $sent++; }
    }

    // Record email history for attestations
    $subject = 'Your Attendance Certificate - ' . $event['title'];
    $message = 'Please find attached your attendance certificate for ' . $event['title'] . '.';
    recordEmailHistory($db, $event_id, $organizer->organizer_id, 'attestation', $subject, $message, count($rows), $sent, count($rows) - $sent, []);

    echo json_encode(['success' => true, 'sent' => $sent, 'total' => count($rows), 'results' => $results]);
}

function handleSendEmails($db, $input) {
    // Only organizers who own the event can send emails; admins are not allowed
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }
    if (isAdmin()) {
        throw new Exception('Admins are not allowed to send emails');
    }

    $event_id = intval($input['event_id'] ?? 0);
    $participant_ids = $input['participant_ids'] ?? null; // null => all registered

    if ($event_id <= 0) {
        throw new Exception('Event ID is required');
    }

    require_once '../classes/Organizer.php';
    require_once '../services/Mailer.php';

    $organizer = new Organizer($db);
    $organizer->id = $_SESSION['user_id'];
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    // Ensure the organizer owns the event
    $checkQuery = "SELECT event_id, title, date_event, start_time, end_time, location FROM events WHERE event_id=:event_id AND created_by=:created_by";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":event_id", $event_id);
    $checkStmt->bindParam(":created_by", $organizer->organizer_id);
    $checkStmt->execute();
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        throw new Exception('You are not authorized to send emails for this event');
    }

    // Build recipient list
    $params = [":event_id" => $event_id];
    $filter = "";
    if (is_array($participant_ids) && count($participant_ids) > 0) {
        // restrict to provided participant ids
        $placeholders = [];
        foreach ($participant_ids as $idx => $pid) {
            $ph = ":pid_" . $idx;
            $placeholders[] = $ph;
            $params[$ph] = (int)$pid;
        }
        $filter = " AND i.participant_id IN (" . implode(",", $placeholders) . ")";
    }

    // Currently use confirmed as a proxy for attendance
    $q = "SELECT i.registration_id, a.nom, a.email, p.participant_id
          FROM registered i
          INNER JOIN participants p ON i.participant_id = p.participant_id
          INNER JOIN accounts a ON p.account_id = a.id
          WHERE i.event_id = :event_id AND i.confirmed = 1" . $filter;
    $stmt = $db->prepare($q);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows || count($rows) === 0) {
        echo json_encode(['success' => true, 'sent' => 0, 'results' => []]);
        return;
    }

    $mailer = new Mailer();

    $sent = 0;
    $results = [];
    foreach ($rows as $row) {
        $toEmail = $row['email'];
        $toName = $row['nom'];
        $subject = 'Information about your event registration';
        // Simple template for now; Attestation attachment to be added later
        $htmlBody = '<p>Dear ' . htmlspecialchars($toName) . ',</p>' .
                    '<p>Thank you for attending <strong>' . htmlspecialchars($event['title']) . '</strong> on ' .
                    htmlspecialchars($event['date_event']) . ' from ' . date('g:i A', strtotime($event['start_time'])) . ' to ' . date('g:i A', strtotime($event['end_time'])) . ' (' . htmlspecialchars($event['location']) . ').</p>' .
                    '<p>Your attendance has been recorded.</p>' .
                    '<p>Best regards,<br/>Campus Events Team</p>';

        $ok = $mailer->sendEmail($toEmail, $toName, $subject, $htmlBody, []);
        $results[] = [
            'participant_id' => (int)$row['participant_id'],
            'email' => $toEmail,
            'sent' => (bool)$ok
        ];
        if ($ok) { $sent++; }
    }

    echo json_encode(['success' => true, 'sent' => $sent, 'total' => count($rows), 'results' => $results]);
}

function handleSendCustomEmail($db, $input) {
    // Only organizers who own the event can send custom emails; admins are not allowed
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }
    if (isAdmin()) {
        throw new Exception('Admins are not allowed to send custom emails');
    }

    $event_id = intval($input['event_id'] ?? 0);
    $participant_ids = $input['participant_ids'] ?? null; // null => all registered
    $subject = trim($input['subject'] ?? '');
    $message = trim($input['message'] ?? '');
    $attachments = $input['attachments'] ?? []; // Array of file paths

    if ($event_id <= 0) {
        throw new Exception('Event ID is required');
    }
    if (empty($subject)) {
        throw new Exception('Subject is required');
    }
    if (empty($message)) {
        throw new Exception('Message is required');
    }

    require_once '../classes/Organizer.php';
    require_once '../services/Mailer.php';

    $organizer = new Organizer($db);
    $organizer->id = $_SESSION['user_id'];
    if (!$organizer->getProfile()) {
        throw new Exception('Organizer profile not found');
    }

    // Ensure the organizer owns the event
    $checkQuery = "SELECT event_id, title, date_event, start_time, end_time, location FROM events WHERE event_id=:event_id AND created_by=:created_by";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":event_id", $event_id);
    $checkStmt->bindParam(":created_by", $organizer->organizer_id);
    $checkStmt->execute();
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        throw new Exception('You are not authorized to send emails for this event');
    }

    // Build recipient list
    $params = [":event_id" => $event_id];
    $filter = "";
    if (is_array($participant_ids) && count($participant_ids) > 0) {
        // restrict to provided participant ids
        $placeholders = [];
        foreach ($participant_ids as $idx => $pid) {
            $ph = ":pid_" . $idx;
            $placeholders[] = $ph;
            $params[$ph] = (int)$pid;
        }
        $filter = " AND i.participant_id IN (" . implode(",", $placeholders) . ")";
    }

    // Get registered participants
    $q = "SELECT i.registration_id, a.nom, a.email, p.participant_id
          FROM registered i
          INNER JOIN participants p ON i.participant_id = p.participant_id
          INNER JOIN accounts a ON p.account_id = a.id
          WHERE i.event_id = :event_id AND i.confirmed = 1" . $filter;
    $stmt = $db->prepare($q);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows || count($rows) === 0) {
        echo json_encode(['success' => true, 'sent' => 0, 'results' => []]);
        return;
    }

    $mailer = new Mailer();

    $sent = 0;
    $results = [];
    foreach ($rows as $row) {
        $toEmail = $row['email'];
        $toName = $row['nom'];
        
        // Create personalized message
        $personalizedMessage = str_replace(
            ['{name}', '{event_title}', '{event_date}', '{event_time}', '{event_location}'],
            [
                htmlspecialchars($toName),
                htmlspecialchars($event['title']),
                htmlspecialchars($event['date_event']),
                date('g:i A', strtotime($event['start_time'])) . ' - ' . date('g:i A', strtotime($event['end_time'])),
                htmlspecialchars($event['location'])
            ],
            $message
        );

        // Build HTML body
        $htmlBody = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $htmlBody .= '<h2 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">' . htmlspecialchars($subject) . '</h2>';
        $htmlBody .= '<div style="line-height: 1.6; color: #555;">';
        $htmlBody .= nl2br(htmlspecialchars($personalizedMessage));
        $htmlBody .= '</div>';
        $htmlBody .= '<hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">';
        $htmlBody .= '<p style="font-size: 12px; color: #888;">';
        $htmlBody .= 'This email was sent regarding the event: <strong>' . htmlspecialchars($event['title']) . '</strong><br>';
        $htmlBody .= 'Best regards,<br>' . htmlspecialchars($organizer->nom ?? 'Event Organizer');
        $htmlBody .= '</p></div>';

        // Validate attachments
        $validAttachments = [];
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (is_string($attachment) && file_exists($attachment)) {
                    $validAttachments[] = $attachment;
                }
            }
        }

        $ok = $mailer->sendEmail($toEmail, $toName, $subject, $htmlBody, $validAttachments);
        $results[] = [
            'participant_id' => (int)$row['participant_id'],
            'email' => $toEmail,
            'sent' => (bool)$ok,
            'error' => $ok ? null : $mailer->getLastError()
        ];
        if ($ok) { $sent++; }
    }

    // Keep uploaded files for history (optional cleanup after 30 days)
    // Files are kept in storage/email_attachments/ for future reference

    // Record email history
    recordEmailHistory($db, $event_id, $organizer->organizer_id, 'custom_email', $subject, $message, count($rows), $sent, count($rows) - $sent, $validAttachments);

    echo json_encode(['success' => true, 'sent' => $sent, 'total' => count($rows), 'results' => $results]);
}

function recordEmailHistory($db, $event_id, $organizer_id, $email_type, $subject, $message, $recipients_count, $sent_count, $failed_count, $attachments = []) {
    try {
        $attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;
        
        $query = "INSERT INTO email_history (event_id, organizer_id, email_type, subject, message, recipients_count, sent_count, failed_count, attachments) 
                  VALUES (:event_id, :organizer_id, :email_type, :subject, :message, :recipients_count, :sent_count, :failed_count, :attachments)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->bindParam(':organizer_id', $organizer_id, PDO::PARAM_INT);
        $stmt->bindParam(':email_type', $email_type, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':recipients_count', $recipients_count, PDO::PARAM_INT);
        $stmt->bindParam(':sent_count', $sent_count, PDO::PARAM_INT);
        $stmt->bindParam(':failed_count', $failed_count, PDO::PARAM_INT);
        $stmt->bindParam(':attachments', $attachmentsJson, PDO::PARAM_STR);
        
        $stmt->execute();
    } catch (Exception $e) {
        // Log error but don't fail the email sending
        error_log("Failed to record email history: " . $e->getMessage());
    }
}

function handleGetEmailHistory($db, $input) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in');
    }

    $event_id = intval($input['event_id'] ?? 0);
    $organizer_id = null;

    // Get organizer ID
    if (isAdmin()) {
        // Admins can see all email history
        $organizer_id = null;
    } else {
        // Organizers can only see their own email history
        $organizer = new Organizer($db);
        $organizer->id = $_SESSION['user_id'];
        if (!$organizer->getProfile()) {
            throw new Exception('Organizer profile not found');
        }
        $organizer_id = $organizer->organizer_id;
    }

    // Build query
    $query = "SELECT eh.*, e.title as event_title, a.nom as organizer_name 
              FROM email_history eh 
              INNER JOIN events e ON eh.event_id = e.event_id 
              INNER JOIN organizers o ON eh.organizer_id = o.organizer_id
              INNER JOIN participants p ON o.participant_id = p.participant_id
              INNER JOIN accounts a ON p.account_id = a.id
              WHERE 1=1";
    
    $params = [];
    
    if ($event_id > 0) {
        $query .= " AND eh.event_id = :event_id";
        $params[':event_id'] = $event_id;
    }
    
    if ($organizer_id !== null) {
        $query .= " AND eh.organizer_id = :organizer_id";
        $params[':organizer_id'] = $organizer_id;
    }
    
    $query .= " ORDER BY eh.sent_at DESC LIMIT 50";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode attachments JSON
    foreach ($history as &$record) {
        if ($record['attachments']) {
            $record['attachments'] = json_decode($record['attachments'], true);
        } else {
            $record['attachments'] = [];
        }
    }
    
    echo json_encode(['success' => true, 'history' => $history]);
}
?>





