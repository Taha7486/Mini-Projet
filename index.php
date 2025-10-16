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
    <title>EventsHub - Campus Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>

    <!-- Search and Filter Section -->
    <div class="container mx-auto px-12 py-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" 
                        id="searchInput"
                        placeholder="Search events..." 
                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
            </div>
            <select id="clubFilter" class="min-w-[300px] px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                <option value="all">All Clubs</option>
                <?php foreach ($clubs as $clubItem): ?>
                    <option value="<?= $clubItem['club_id'] ?>"><?= htmlspecialchars($clubItem['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Events Grid -->
    <main class="container mx-auto px-12 py-6 flex-1"">
        <div id="eventsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow event-card" 
                     data-club="<?= $event['club_id'] ?>"
                     data-title="<?= strtolower($event['title']) ?>"
                     data-description="<?= strtolower($event['description']) ?>"
                     data-event-id="<?= $event['event_id'] ?>"
                     data-event-title="<?= htmlspecialchars($event['title'], ENT_QUOTES) ?>"
                     data-event-description="<?= htmlspecialchars($event['description'], ENT_QUOTES) ?>"
                     data-event-date="<?= htmlspecialchars($event['date_event'], ENT_QUOTES) ?>"
                     data-event-start-time="<?= htmlspecialchars($event['start_time'], ENT_QUOTES) ?>"
                     data-event-end-time="<?= htmlspecialchars($event['end_time'], ENT_QUOTES) ?>"
                     data-event-location="<?= htmlspecialchars($event['location'], ENT_QUOTES) ?>"
                     data-event-capacity="<?= (int)$event['capacity'] ?>"
                     data-event-registered="<?= (int)$event['registered_count'] ?>"
                     data-event-club-name="<?= htmlspecialchars($event['club_name'], ENT_QUOTES) ?>"
                     data-event-image-url="<?= htmlspecialchars($event['image_url'] ?? '', ENT_QUOTES) ?>">
                    <div class="relative h-48 bg-gray-200">
                        <?php if ($event['image_url']): ?>
                            <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                 class="w-full h-full object-cover">
                        <?php endif; ?>
                        <span onclick="event.stopPropagation();" class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-sm font-medium">
                            <?= htmlspecialchars($event['club_name']) ?>
                        </span>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($event['title']) ?></h3>
                        <p class="text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($event['description']) ?></p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-gray-600 text-sm">
                                <i class="fas fa-calendar"></i>
                                <span><?= date('M d, Y', strtotime($event['date_event'])) ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600 text-sm">
                                <i class="fas fa-clock"></i>
                                <span><?= date('g:i A', strtotime($event['start_time'])) ?> - <?= date('g:i A', strtotime($event['end_time'])) ?></span>
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
                        <button onclick="event.stopPropagation(); registerForEvent(<?= $event['event_id'] ?>)" 
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

    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-6xl rounded-lg overflow-hidden shadow-lg max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-12 py-4 border-b bg-gray-50">
                <div class="flex items-center gap-3">
                    <i class="fas fa-calendar-alt text-2xl text-gray-600"></i>
                    <h3 id="detailTitle" class="text-2xl font-bold text-gray-800">Event Details</h3>
                </div>
                <button onclick="closeEventDetails()" class="text-gray-500 hover:text-gray-800 p-2 rounded-full hover:bg-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="px-12 py-4 space-y-6">
                <!-- Event Image and Club -->
                <div id="detailImageWrapper" class="relative h-64 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg overflow-hidden hidden">
                    <img id="detailImage" src="" alt="Event image" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                    <div class="absolute top-4 right-4">
                        <span id="detailClubName" class="bg-white/90 backdrop-blur px-4 py-2 rounded-full text-sm font-semibold text-gray-800 shadow-lg"></span>
                    </div>
                    <div class="absolute bottom-4 left-4 text-white">
                        <h4 id="detailTitleOverlay" class="text-2xl font-bold drop-shadow-lg"></h4>
                    </div>
                </div>

                <!-- Event Title (when no image) -->
                <div id="detailTitleSection" class="text-center">
                    <h4 id="detailTitleText" class="text-3xl font-bold text-gray-800 mb-2"></h4>
                    <div class="flex items-center justify-center gap-2 text-gray-600">
                        <i class="fas fa-building"></i>
                        <span id="detailClubNameText" class="text-lg font-medium"></span>
                    </div>
                </div>

                <!-- Event Description -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-file-alt text-gray-600"></i>
                        Description
                    </h5>
                    <p id="detailDescription" class="text-gray-700 leading-relaxed"></p>
                </div>

                <!-- Event Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Date & Time -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-blue-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-calendar text-blue-600"></i>
                            Date & Time
                        </h5>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-calendar-day text-blue-500"></i>
                                <span id="detailDate" class="font-medium"></span>
                            </div>
                            <div class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-clock text-blue-500"></i>
                                <span id="detailTime" class="font-medium"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-green-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                            Location
                        </h5>
                        <div class="flex items-center gap-3 text-gray-700">
                            <i class="fas fa-location-dot text-green-500"></i>
                            <span id="detailLocation" class="font-medium"></span>
                        </div>
                    </div>

                    <!-- Capacity & Registration -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-purple-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-users text-purple-600"></i>
                            Registration
                        </h5>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700">Registered:</span>
                                <span id="detailRegistered" class="font-bold text-purple-600"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700">Capacity:</span>
                                <span id="detailCapacity" class="font-bold text-purple-600"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div id="detailProgressBar" class="bg-purple-500 h-3 rounded-full transition-all duration-300"></div>
                            </div>
                            <div class="text-center">
                                <span id="detailSpotsLeft" class="text-sm font-medium text-gray-600"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-12 py-4 border-t bg-gray-50 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Click outside the modal or press ESC to close
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="closeEventDetails()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                    <button id="detailRegisterBtn" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-user-plus mr-2"></i>Register Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const clubFilter = document.getElementById('clubFilter');
        const eventCards = document.querySelectorAll('.event-card');
        const detailsModal = document.getElementById('eventDetailsModal');
        const detailTitle = document.getElementById('detailTitle');
        const detailDescription = document.getElementById('detailDescription');
        const detailDateTime = document.getElementById('detailDateTime');
        const detailLocation = document.getElementById('detailLocation');
        const detailCapacity = document.getElementById('detailCapacity');
        const detailImage = document.getElementById('detailImage');
        const detailImageWrapper = document.getElementById('detailImageWrapper');
        const detailClubName = document.getElementById('detailClubName');
        const detailRegisterBtn = document.getElementById('detailRegisterBtn');
        const detailTitleSection = document.getElementById('detailTitleSection');
        const detailTitleText = document.getElementById('detailTitleText');
        const detailClubNameText = document.getElementById('detailClubNameText');
        const detailTitleOverlay = document.getElementById('detailTitleOverlay');

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

        // Open details on card click
        eventCards.forEach(card => {
            card.addEventListener('click', () => openEventDetails(card));
        });

        function openEventDetails(card) {
            const id = parseInt(card.dataset.eventId, 10);
            const title = card.dataset.eventTitle || '';
            const desc = card.dataset.eventDescription || '';
            const date = card.dataset.eventDate || '';
            const startTimeRaw = card.dataset.eventStartTime || '';
            const endTimeRaw = card.dataset.eventEndTime || '';
            const location = card.dataset.eventLocation || '';
            const capacity = parseInt(card.dataset.eventCapacity || '0', 10);
            const registered = parseInt(card.dataset.eventRegistered || '0', 10);
            const clubName = card.dataset.eventClubName || '';
            const imageUrl = card.dataset.eventImageUrl || '';
            const clubId = card.dataset.club || '';

            // Calculate additional details
            const spotsLeft = capacity - registered;
            const fillRate = capacity > 0 ? Math.round((registered / capacity) * 100) : 0;
            const isFull = spotsLeft <= 0;
            const isAlmostFull = spotsLeft <= 5 && spotsLeft > 0;

            // Format date
            const eventDate = new Date(date);
            const formattedDate = eventDate.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Update basic info
            detailTitle.textContent = title;
            detailDescription.textContent = desc;

            // Update image section
            if (imageUrl) {
                detailImageWrapper.classList.remove('hidden');
                detailTitleSection.classList.add('hidden');
                detailImage.src = imageUrl;
                detailImage.alt = title;
                detailClubName.textContent = clubName;
                detailTitleOverlay.textContent = title;
            } else {
                detailImageWrapper.classList.add('hidden');
                detailTitleSection.classList.remove('hidden');
                detailTitleText.textContent = title;
                detailClubNameText.textContent = clubName;
            }

            // Update date and time
            document.getElementById('detailDate').textContent = formattedDate;
            const formatTime = (t) => {
                if (!t) return '';
                const d = new Date(`1970-01-01T${t}`);
                if (isNaN(d.getTime())) return t;
                return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            };
            const startTime = formatTime(startTimeRaw);
            const endTime = formatTime(endTimeRaw);
            const timeRange = startTime && endTime ? `${startTime} - ${endTime}` : (startTime || endTime || '');
            document.getElementById('detailTime').textContent = timeRange;

            // Update location
            document.getElementById('detailLocation').textContent = location;

            // Update registration details
            document.getElementById('detailRegistered').textContent = registered;
            document.getElementById('detailCapacity').textContent = capacity;
            document.getElementById('detailSpotsLeft').textContent = 
                spotsLeft === 0 ? 'Event is full' : 
                spotsLeft === 1 ? '1 spot left' : 
                `${spotsLeft} spots left`;

            // Update progress bar
            const progressBar = document.getElementById('detailProgressBar');
            progressBar.style.width = `${fillRate}%`;
            progressBar.className = `h-3 rounded-full transition-all duration-300 ${
                isFull ? 'bg-red-500' : 
                isAlmostFull ? 'bg-orange-500' : 
                'bg-purple-500'
            }`;

            // Update register button
            detailRegisterBtn.disabled = isFull;
            detailRegisterBtn.textContent = isFull ? 'Event Full' : 'Register Now';
            detailRegisterBtn.className = `px-6 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${
                isFull ? 'bg-gray-400 text-gray-600 cursor-not-allowed' : 'bg-black text-white hover:bg-gray-800'
            }`;

            detailRegisterBtn.onclick = (e) => { 
                e.stopPropagation(); 
                if (!isFull) {
                    registerForEvent(id); 
                }
            };

            detailsModal.classList.remove('hidden');
        }

        function closeEventDetails() {
            detailsModal.classList.add('hidden');
        }

        // Close modal when clicking outside or pressing ESC
        detailsModal.addEventListener('click', (e) => {
            if (e.target === detailsModal) {
                closeEventDetails();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !detailsModal.classList.contains('hidden')) {
                closeEventDetails();
            }
        });

        // Register for event
        function registerForEvent(eventId) {
            <?php if (!isLoggedIn()): ?>
                alert('Please login to register for events');
                window.location.href = 'public/login.php';
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