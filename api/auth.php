<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once '../config/database.php';
    require_once '../classes/Account.php';
    require_once '../classes/Participant.php';
    require_once '../classes/Admin.php';
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

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    switch($action) {
        case 'login':
            handleLogin($db, $input);
            break;
        case 'signup':
            handleSignup($db, $input);
            break;
        case 'logout':
            handleLogout();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function handleLogin($db, $input) {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if(empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }

    $account = new Account($db);
    $account->email = $email;
    $account->password = $password;

    if($account->login()) {
        // Check if admin
        $admin = new Admin($db);
        $admin->id = $account->id;
        
        if($admin->getProfile()) {
            setUserSession($account->id, 'admin', [
                'admin_id' => $admin->admin_id,
                'nom' => $admin->nom,
                'email' => $admin->email
            ]);
            echo json_encode(['success' => true, 'user_type' => 'admin']);
            return;
        }

        // Check if participant
        $participant = new Participant($db);
        $participant->id = $account->id;
        
        if($participant->getProfile()) {
            setUserSession($account->id, 'participant', [
                'participant_id' => $participant->participant_id,
                'nom' => $participant->nom,
                'email' => $participant->email,
                'role' => $participant->role
            ]);
            echo json_encode(['success' => true, 'user_type' => 'participant', 'role' => $participant->role]);
            return;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}

function handleSignup($db, $input) {
    $nom = $input['nom'] ?? '';
    $email = $input['email'] ?? '';
    $student_id = $input['student_id'] ?? '';
    $year = $input['year'] ?? '';
    $department = $input['department'] ?? '';
    $phone_number = $input['phone_number'] ?? '';
    $password = $input['password'] ?? '';

    if(empty($nom) || empty($email) || empty($student_id) || empty($year) || 
       empty($department) || empty($phone_number) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }

    $participant = new Participant($db);
    
    if($participant->registerParticipant($nom, $email, $password, $student_id, $year, $department, $phone_number)) {
        // Auto login
        setUserSession($participant->id, 'participant', [
            'participant_id' => $participant->participant_id,
            'nom' => $participant->nom,
            'email' => $participant->email,
            'role' => 'user'
        ]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email already exists or registration failed']);
    }
}

function handleLogout() {
    destroyUserSession();
    header('Location: ../index.php');
    exit();
}
?>
