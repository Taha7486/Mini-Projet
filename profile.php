<?php
require_once 'config/database.php';
require_once 'classes/Participant.php';
require_once 'classes/Club.php';
require_once 'includes/session.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$participant = new Participant($db);
$participant->id = $_SESSION['user_id'];
if (!isAdmin()){
    $participant->participant_id = $_SESSION['participant_id'];
}
$participant->getProfile();

$club = new Club($db);
$clubs = $club->getAll();

$myClubs = $participant->getClubs();
$requests = $participant->getOrganizerRequests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Campus Events</title>
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
                    <h1 class="text-2xl font-semibold">My Profile</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="index.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Events
                    </a>
                    <a href="api/auth.php?action=logout" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="space-y-6">
            <!-- Profile Info Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Profile Information</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?= $participant->role === 'organizer' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                        <?= ucfirst($participant->role) ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-user text-gray-400"></i>
                        <div>
                            <p class="text-sm text-gray-600">Full Name</p>
                            <p class="font-medium"><?= htmlspecialchars($participant->nom) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope text-gray-400"></i>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium"><?= htmlspecialchars($participant->email) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <i class="fas fa-id-card text-gray-400"></i>
                        <div>
                            <p class="text-sm text-gray-600">Student ID</p>
                            <p class="font-medium"><?= htmlspecialchars($participant->student_id) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <i class="fas fa-graduation-cap text-gray-400"></i>
                        <div>
                            <p class="text-sm text-gray-600">Year of Study</p>
                            <p class="font-medium"><?= $participant->year === 'graduate' ? 'Graduate' : $participant->year . ' Year' ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <i class="fas fa-building text-gray-400"></i>
                        <div>
                            <p class="text-sm text-gray-600">Department</p>
                            <p class="font-medium"><?= htmlspecialchars($participant->department) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <i class="fas fa-phone text-gray-400"></i>
                        <div>
                            <p class="text-sm text-gray-600">Phone Number</p>
                            <p class="font-medium"><?= htmlspecialchars($participant->phone_number) ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($participant->role === 'organizer' && !empty($myClubs)): ?>
                <div class="mt-6 pt-6 border-t">
                    <p class="text-sm text-gray-600 mb-2">Managing Clubs</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($myClubs as $myClub): ?>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                <?= htmlspecialchars($myClub['nom']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Become Organizer / Change Clubs -->
            <?php if ($participant->role === 'user' || $participant->role === 'organizer'): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">
                    <?= $participant->role === 'user' ? 'Become an Organizer' : 'Request Club Change' ?>
                </h2>
                <p class="text-gray-600 mb-4">
                    <?= $participant->role === 'user' 
                        ? 'Request organizer access to create and manage events for your club' 
                        : 'Request to change the clubs you manage (requires admin approval)' ?>
                </p>

                <div id="organizerSection">
                    <button id="showFormBtn" onclick="showOrganizerForm()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        <?= $participant->role === 'user' ? 'Request Organizer Access' : 'Request Club Change' ?>
                    </button>

                    <form id="organizerForm" class="hidden mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Select clubs you want to manage: *</label>
                            <div class="space-y-2">
                                <?php foreach ($clubs as $clubItem): ?>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="clubs[]" value="<?= $clubItem['club_id'] ?>" class="w-4 h-4">
                                    <span><?= htmlspecialchars($clubItem['nom']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="hideOrganizerForm()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" id="submitRequestBtn" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Organizer Requests History -->
            <?php if (!empty($requests)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">My Requests</h2>
                <div class="space-y-3">
                    <?php foreach ($requests as $request): ?>
                    <div class="p-4 border rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium"><?= htmlspecialchars($request['club_name']) ?></p>
                                <p class="text-sm text-gray-600">
                                    Requested: <?= date('M d, Y', strtotime($request['requested_at'])) ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                <?= $request['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                   ($request['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= ucfirst($request['status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="index.php" class="block px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-calendar-alt mr-2"></i>View All Events
                    </a>
                    <?php if (isOrganizer() || isAdmin()): ?>
                    <a href="organizer-dashboard.php" class="block px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-tasks mr-2"></i>Manage Events
                    </a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                    <a href="admin-panel.php" class="block px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-user-shield mr-2"></i>Admin Panel
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showOrganizerForm() {
            document.getElementById('showFormBtn').classList.add('hidden');
            document.getElementById('organizerForm').classList.remove('hidden');
        }

        function hideOrganizerForm() {
            document.getElementById('showFormBtn').classList.remove('hidden');
            document.getElementById('organizerForm').classList.add('hidden');
        }

        document.getElementById('organizerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const checkboxes = document.querySelectorAll('input[name="clubs[]"]:checked');
            const clubIds = Array.from(checkboxes).map(cb => cb.value);

            if (clubIds.length === 0) {
                alert('Please select at least one club');
                return;
            }

            const submitBtn = document.getElementById('submitRequestBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                const response = await fetch('api/admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'request_organizer',
                        club_ids: clubIds
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Request submitted successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to submit request');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Request';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            }
        });
    </script>
</body>
</html>
