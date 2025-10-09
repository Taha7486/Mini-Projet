<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once '../config/database.php';
    require_once '../classes/Account.php';
    require_once '../classes/Participant.php';
    require_once '../classes/Admin.php';
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    require_once '../services/EmailVerification.php';
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
        case 'request_signup':
            handleRequestSignup($db, $input);
            break;
        case 'verify_signup':
            handleVerifySignup($db, $input);
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

    $yearNeedsDepartment = ($year === 'graduate');
    $yearNeedsFiliere = ($year === '3' || $year === '4' || $year === '5');

    if(empty($nom) || empty($email) || empty($student_id) || empty($year) ||
       ($yearNeedsDepartment && empty($department)) || empty($phone_number) || empty($password)) {
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

function handleRequestSignup($db, $input) {
    $nom = trim($input['nom'] ?? '');
    $email = trim($input['email'] ?? '');
    $student_id = trim($input['student_id'] ?? '');
    $year = trim($input['year'] ?? '');
    $department = trim($input['department'] ?? '');
    $phone_number = trim($input['phone_number'] ?? '');
    $password = $input['password'] ?? '';

    $yearNeedsDepartment = ($year === 'graduate');

    if(empty($nom) || empty($email) || empty($student_id) || empty($year) ||
       ($yearNeedsDepartment && empty($department)) || empty($phone_number) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill required fields']);
        return;
    }

    $verifier = new EmailVerificationService();
    if (!$verifier->verifyAddress($email)) {
        echo json_encode(['success' => false, 'message' => 'Email could not be verified']);
        return;
    }

    // Check for existing account
    $check = $db->prepare("SELECT id FROM accounts WHERE email = :email");
    $check->bindValue(":email", $email);
    $check->execute();
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }

    // Insert pending signup
    $token = bin2hex(random_bytes(16));
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $ins = $db->prepare("INSERT INTO pending_signups (nom, email, student_id, year, department, phone_number, password_hash, verify_token) VALUES (:nom, :email, :student_id, :year, :department, :phone_number, :password_hash, :verify_token)");
    $ins->bindValue(":nom", $nom);
    $ins->bindValue(":email", $email);
    $ins->bindValue(":student_id", $student_id);
    $ins->bindValue(":year", $year);
    $ins->bindValue(":department", $department);
    $ins->bindValue(":phone_number", $phone_number);
    $ins->bindValue(":password_hash", $passwordHash);
    $ins->bindValue(":verify_token", $token);
    if (!$ins->execute()) {
        echo json_encode(['success' => false, 'message' => 'Could not create pending signup']);
        return;
    }

    // Send verification email
    if (!$verifier->sendVerificationEmail($email, $nom, $token)) {
        echo json_encode(['success' => false, 'message' => 'Failed to send verification email']);
        return;
    }

    echo json_encode(['success' => true, 'message' => 'Please check your email to verify your account']);
}

function handleVerifySignup($db, $input) {
    $token = $_GET['token'] ?? $input['token'] ?? '';
    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        return;
    }

    $sel = $db->prepare("SELECT * FROM pending_signups WHERE verify_token = :t LIMIT 1");
    $sel->bindValue(":t", $token);
    $sel->execute();
    $row = $sel->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Token not found or already used']);
        return;
    }

    // Create account + participant
    require_once '../classes/Account.php';
    require_once '../classes/Participant.php';

    $participant = new Participant($db);
    $participant->nom = $row['nom'];
    $participant->email = $row['email'];
    $participant->password = $row['password_hash']; // register() will hash; we stored hash, so override flow

    // Insert account directly to preserve our hash
    $acc = $db->prepare("INSERT INTO accounts (nom, email, password) VALUES (:n, :e, :p)");
    $acc->bindValue(":n", $row['nom']);
    $acc->bindValue(":e", $row['email']);
    $acc->bindValue(":p", $row['password_hash']);
    if (!$acc->execute()) {
        echo json_encode(['success' => false, 'message' => 'Could not create account']);
        return;
    }
    $accountId = (int)$db->lastInsertId();

    $insP = $db->prepare("INSERT INTO participants (account_id, student_id, year, department, phone_number) VALUES (:aid, :sid, :y, :d, :ph)");
    $insP->bindValue(":aid", $accountId, PDO::PARAM_INT);
    $insP->bindValue(":sid", $row['student_id']);
    $insP->bindValue(":y", $row['year']);
    $insP->bindValue(":d", $row['department']);
    $insP->bindValue(":ph", $row['phone_number']);
    if (!$insP->execute()) {
        echo json_encode(['success' => false, 'message' => 'Could not create participant']);
        return;
    }

    // Cleanup pending record
    $del = $db->prepare("DELETE FROM pending_signups WHERE pending_id = :pid");
    $del->bindValue(":pid", $row['pending_id'], PDO::PARAM_INT);
    $del->execute();

    echo json_encode(['success' => true, 'message' => 'Email verified. You can now log in.']);
}

function handleLogout() {
    destroyUserSession();
    header('Location: ../index.php');
    exit();
}
?>
