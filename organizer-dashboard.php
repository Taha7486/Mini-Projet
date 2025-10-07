<?php
require_once 'config/database.php';
require_once 'classes/Organisateur.php';
require_once 'classes/Club.php';
require_once 'includes/session.php';

requireOrganizer();

$database = new Database();
$db = $database->getConnection();

$organizer = new Organisateur($db);
$organizer->id = $_SESSION['user_id'];
$organizer->participant_id = $_SESSION['participant_id'];
$organizer->getProfile();

$myEvents = $organizer->getMyEvents();
$myClubs = $organizer->getManagedClubs();

$club = new Club($db);
$allClubs = $club->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Campus Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-10 shadow-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                    <div>
                        <h1 class="text-2xl font-semibold">Event Management</h1>
                        <p class="text-gray-600 text-sm">Manage your events • Welcome, <?= htmlspecialchars($organizer->nom) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="index.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-eye mr-2"></i>View Public Page
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="admin-panel.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-user-shield mr-2"></i>Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="api/auth.php?action=logout" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold">Your Events</h2>
                <p class="text-gray-600"><?= count($myEvents) ?> <?= count($myEvents) === 1 ? 'event' : 'events' ?> total</p>
            </div>
            <button onclick="openCreateDialog()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                <i class="fas fa-plus mr-2"></i>Create Event
            </button>
        </div>

        <?php if (empty($myEvents)): ?>
        <div class="text-center py-12 bg-white rounded-lg border-2 border-dashed">
            <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No events yet</h3>
            <p class="text-gray-600 mb-4">Get started by creating your first event</p>
            <button onclick="openCreateDialog()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                <i class="fas fa-plus mr-2"></i>Create Event
            </button>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($myEvents as $event): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="relative h-48 bg-gray-200">
                    <?php if ($event['image_url']): ?>
                    <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                    <span class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-sm font-medium">
                        <?= htmlspecialchars($event['club_name']) ?>
                    </span>
                </div>
                
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($event['title']) ?></h3>
                    <p class="text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($event['description']) ?></p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                            <i class="fas fa-calendar"></i>
                            <span><?= date('M d, Y', strtotime($event['date_event'])) ?> • <?= htmlspecialchars($event['time_event']) ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($event['location']) ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                            <i class="fas fa-users"></i>
                            <span><?= $event['registered_count'] ?> / <?= $event['capacity'] ?> registered</span>
                        </div>
                        
                        <?php $percent = ($event['registered_count'] / $event['capacity']) * 100; ?>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-black h-2 rounded-full" style="width: <?= min($percent, 100) ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <button onclick='editEvent(<?= json_encode($event) ?>)' class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteEvent(<?= $event['event_id'] ?>)" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                        <button onclick="viewParticipants(<?= $event['event_id'] ?>)" class="w-full px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
                            <i class="fas fa-users mr-1"></i>View Participants (<?= $event['registered_count'] ?>)
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Create/Edit Event Modal -->
    <div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 id="modalTitle" class="text-2xl font-semibold">Create Event</h2>
                    <button onclick="closeEventModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form id="eventForm" class="space-y-4">
                    <input type="hidden" id="eventId" name="event_id">
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Event Title *</label>
                        <input type="text" id="title" name="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black" placeholder="Tech Innovation Summit 2025">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description *</label>
                        <textarea id="description" name="description" required rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black" placeholder="Describe your event..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Date *</label>
                            <input type="date" id="date_event" name="date_event" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Time *</label>
                            <input type="text" id="time_event" name="time_event" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black" placeholder="9:00 AM - 5:00 PM">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Location *</label>
                            <input type="text" id="location" name="location" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black" placeholder="Main Auditorium">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Club *</label>
                            <select id="club_id" name="club_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                                <option value="">Select a club</option>
                                <?php foreach (isAdmin() ? $allClubs : $myClubs as $clubItem): ?>
                                <option value="<?= $clubItem['club_id'] ?>"><?= htmlspecialchars($clubItem['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Capacity *</label>
                            <input type="number" id="capacity" name="capacity" required min="1" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black" value="50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Image URL *</label>
                            <input type="url" id="image_url" name="image_url" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black" placeholder="https://...">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeEventModal()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit" id="submitBtn" class="flex-1 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Participants Modal -->
    <div id="participantsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-semibold" id="participantsTitle">Event Participants</h2>
                        <p class="text-gray-600" id="participantsCount">0 participants registered</p>
                    </div>
                    <button onclick="closeParticipantsModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <div id="participantsList" class="overflow-x-auto">
                    <!-- Participants will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/organizer-dashboard.js"></script>
</body>
</html>
