<?php
require_once 'config/database.php';
require_once 'classes/Event.php';
require_once 'classes/Club.php';
require_once 'includes/session.php';

// Check if event_id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$event_id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

$event = new Event($db);
$eventDetails = $event->getById($event_id);

if (!$eventDetails) {
    header('Location: index.php');
    exit();
}

// Get participants for this event
$participantsQuery = "SELECT p.participant_id, p.student_id, p.year, p.department, p.phone_number,
                      a.nom, a.email, r.registered_at
                      FROM registered r
                      INNER JOIN participants p ON r.participant_id = p.participant_id
                      INNER JOIN accounts a ON p.account_id = a.id
                      WHERE r.event_id = :event_id
                      ORDER BY r.registered_at ASC";

$stmt = $db->prepare($participantsQuery);
$stmt->bindParam(":event_id", $event_id);
$stmt->execute();
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($eventDetails['title']) ?> - Campus Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-10 shadow-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-semibold">Event Details</h1>
                        <p class="text-gray-600 text-sm">View complete event information</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <?php if (isOrganizer() || isAdmin()): ?>
                            <a href="organizer-dashboard.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                                Manage Events
                            </a>
                        <?php endif; ?>
                        <?php if (isAdmin()): ?>
                            <a href="admin-panel.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="api/auth.php?action=logout" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                            Sign Out
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Event Details -->
    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Event Info -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Event Image -->
                    <div class="relative h-64 bg-gray-200">
                        <?php if ($eventDetails['image_url']): ?>
                            <img src="<?= htmlspecialchars($eventDetails['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($eventDetails['title']) ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-6xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-sm font-medium">
                            <?= htmlspecialchars($eventDetails['club_name']) ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($eventDetails['title']) ?></h1>
                        
                        <div class="prose max-w-none mb-6">
                            <p class="text-gray-700 text-lg leading-relaxed"><?= nl2br(htmlspecialchars($eventDetails['description'])) ?></p>
                        </div>

                        <!-- Event Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-100 p-3 rounded-lg">
                                    <i class="fas fa-calendar text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Date & Time</h3>
                                    <p class="text-gray-600">
                                        <?= date('l, F j, Y', strtotime($eventDetails['date_event'])) ?><br>
                                        <?= htmlspecialchars($eventDetails['time_event']) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="bg-green-100 p-3 rounded-lg">
                                    <i class="fas fa-map-marker-alt text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Location</h3>
                                    <p class="text-gray-600"><?= htmlspecialchars($eventDetails['location']) ?></p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="bg-purple-100 p-3 rounded-lg">
                                    <i class="fas fa-users text-purple-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Capacity</h3>
                                    <p class="text-gray-600">
                                        <?= $eventDetails['registered_count'] ?> / <?= $eventDetails['capacity'] ?> registered
                                    </p>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-purple-600 h-2 rounded-full" 
                                             style="width: <?= ($eventDetails['registered_count'] / $eventDetails['capacity']) * 100 ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="bg-orange-100 p-3 rounded-lg">
                                    <i class="fas fa-user-tie text-orange-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Organized by</h3>
                                    <p class="text-gray-600"><?= htmlspecialchars($eventDetails['creator_name'] ?? 'Unknown') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Button -->
                        <?php 
                        $spotsLeft = $eventDetails['capacity'] - $eventDetails['registered_count'];
                        $isRegistered = false;
                        
                        if (isLoggedIn()) {
                            // Check if user is already registered
                            $checkQuery = "SELECT registration_id FROM registered 
                                         WHERE event_id = :event_id AND participant_id = :participant_id";
                            $checkStmt = $db->prepare($checkQuery);
                            $checkStmt->bindParam(":event_id", $event_id);
                            $checkStmt->bindParam(":participant_id", $_SESSION['participant_id']);
                            $checkStmt->execute();
                            $isRegistered = $checkStmt->rowCount() > 0;
                        }
                        ?>
                        
                        <div class="border-t pt-6">
                            <?php if ($isRegistered): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                    <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                                    <h3 class="font-semibold text-green-800">You're registered for this event!</h3>
                                    <p class="text-green-600 text-sm">We'll send you updates about this event.</p>
                                </div>
                            <?php elseif ($spotsLeft > 0): ?>
                                <button onclick="registerForEvent(<?= $event_id ?>)" 
                                        class="w-full py-3 bg-black text-white rounded-lg hover:bg-gray-800 font-semibold text-lg">
                                    <i class="fas fa-user-plus mr-2"></i>Register for Event
                                </button>
                            <?php else: ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                                    <i class="fas fa-times-circle text-red-600 text-2xl mb-2"></i>
                                    <h3 class="font-semibold text-red-800">Event is Full</h3>
                                    <p class="text-red-600 text-sm">Sorry, this event has reached its capacity.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Event Stats -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Event Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Capacity</span>
                            <span class="font-semibold"><?= $eventDetails['capacity'] ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Registered</span>
                            <span class="font-semibold text-blue-600"><?= $eventDetails['registered_count'] ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Available Spots</span>
                            <span class="font-semibold text-green-600"><?= $spotsLeft ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Registration Rate</span>
                            <span class="font-semibold"><?= round(($eventDetails['registered_count'] / $eventDetails['capacity']) * 100, 1) ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Participants List -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Participants (<?= count($participants) ?>)</h3>
                    <?php if (empty($participants)): ?>
                        <p class="text-gray-500 text-center py-4">No participants yet</p>
                    <?php else: ?>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <?php foreach ($participants as $participant): ?>
                                <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm truncate"><?= htmlspecialchars($participant['nom']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($participant['student_id']) ?></p>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        <?= date('M j', strtotime($participant['registered_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t mt-8 bg-white">
        <div class="container mx-auto px-4 py-4 text-center">
            <p class="text-gray-600 text-sm">&copy; 2025 Campus Events Management System</p>
        </div>
    </footer>

    <script>
        // Register for event
        function registerForEvent(eventId) {
            <?php if (!isLoggedIn()): ?>
                alert('Please login to register for events');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            if (!confirm('Are you sure you want to register for this event?')) {
                return;
            }

            fetch('api/events.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'register',
                    event_id: eventId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully registered for event!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to register for event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>
