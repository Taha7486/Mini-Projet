<?php
require_once 'config/database.php';
require_once 'classes/Event.php';
require_once 'classes/Club.php';
require_once 'includes/session.php';

$database = new Database();
$db = $database->getConnection();

$event = new Event($db);
$club = new Club($db);

$events = $event->getAll();
$clubs = $club->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Events - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-10 shadow-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                        <div>
                            <h1 class="text-2xl font-semibold">Campus Events</h1>
                            <p class="text-gray-600 text-sm">Discover and register for upcoming events on campus</p>
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
                
                <!-- Search and Filter -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               id="searchInput"
                               placeholder="Search events..." 
                               class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <select id="clubFilter" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="all">All Clubs</option>
                        <?php foreach ($clubs as $clubItem): ?>
                            <option value="<?= $clubItem['club_id'] ?>"><?= htmlspecialchars($clubItem['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </header>

    <!-- Events Grid -->
    <main class="container mx-auto px-4 py-8">
        <div id="eventsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow event-card" 
                     data-club="<?= $event['club_id'] ?>"
                     data-title="<?= strtolower($event['title']) ?>"
                     data-description="<?= strtolower($event['description']) ?>">
                    <div class="relative h-48 bg-gray-200">
                        <?php if ($event['image_url']): ?>
                            <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                 class="w-full h-full object-cover">
                        <?php endif; ?>
                        <span class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-sm font-medium">
                            <?= htmlspecialchars($event['club_name']) ?>
                        </span>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($event['title']) ?></h3>
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
                        </div>
                        
                        <?php 
                        $spotsLeft = $event['capacity'] - $event['registered_count'];
                        ?>
                        <button onclick="registerForEvent(<?= $event['event_id'] ?>)" 
                                class="w-full py-2 bg-black text-white rounded-lg hover:bg-gray-800 <?= $spotsLeft == 0 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                <?= $spotsLeft == 0 ? 'disabled' : '' ?>>
                            <?= $spotsLeft == 0 ? 'Event Full' : 'Register Now' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($events)): ?>
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600">No events available at the moment.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="border-t mt-8 bg-white">
        <div class="container mx-auto px-4 py-4 text-center">
            <p class="text-gray-600 text-sm">&copy; 2025 Campus Events Management System</p>
        </div>
    </footer>

    <script>
        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const clubFilter = document.getElementById('clubFilter');
        const eventCards = document.querySelectorAll('.event-card');

        function filterEvents() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedClub = clubFilter.value;

            eventCards.forEach(card => {
                const title = card.dataset.title;
                const description = card.dataset.description;
                const club = card.dataset.club;

                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchesClub = selectedClub === 'all' || club === selectedClub;

                card.style.display = (matchesSearch && matchesClub) ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterEvents);
        clubFilter.addEventListener('change', filterEvents);

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
