<?php
require_once '../config/database.php';
require_once '../classes/Admin.php';
require_once '../classes/Club.php';
require_once '../includes/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);
$admin->id = $_SESSION['user_id'];

// Get all data
$pendingRequests = $admin->getPendingRequests();
$requestHistory = $admin->getOrganizerRequestHistory();
$allUsers = $admin->getAllUsers();
$allClubs = $admin->getClubs();
// Load all events for statistics
$allEvents = $admin->getAllEvents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - EventsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <!-- Tabs -->
    <div class="container mx-auto px-12 mt-6">
        <div class="bg-white rounded-lg shadow-sm p-2 flex gap-2">
            <button onclick="showTab('stats')" id="tab-stats" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-chart-line mr-2"></i>Stats
            </button>
            <button onclick="showTab('requests')" id="tab-requests" class="flex-1 px-4 py-2 rounded-lg bg-black text-white">
                <i class="fas fa-user-clock mr-2"></i>Requests (<?= count($pendingRequests) ?>)
            </button>
            <button onclick="showTab('clubs')" id="tab-clubs" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-users mr-2"></i>Clubs (<?= count($allClubs) ?>)
            </button>
            <button onclick="showTab('events')" id="tab-events" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-calendar-alt mr-2"></i>Events (<?= count($allEvents) ?>)
            </button>
            <button onclick="showTab('users')" id="tab-users" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-user mr-2"></i>Users (<?= count($allUsers) ?>)
            </button>
            <button onclick="showTab('add-admin')" id="tab-add-admin" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-user-shield mr-2"></i>Create Admin
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-12 py-8 flex-1">
        <!-- Requests Tab -->
        <div id="content-requests" class="tab-content">
            <div class="space-y-6">
                <!-- Pending Requests Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-1">Pending Requests (<?= count($pendingRequests) ?>)</h2>
                        <p class="text-gray-600">Review and approve or reject organizer access and club change requests</p>
                    </div>
                    
                    <?php if (!empty($pendingRequests)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Applicant</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Request Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Current Clubs</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Requested Clubs</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $request): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user text-gray-400"></i>
                                            <span class="font-medium"><?= htmlspecialchars($request['user_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                            <span><?= htmlspecialchars($request['email']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 bg-gray-800 text-white rounded-full text-sm font-medium">
                                            New Organizer
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">-</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                            <?= htmlspecialchars($request['club_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <?= date('n/j/Y', strtotime($request['requested_at'])) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <button onclick="handleRequest(<?= $request['request_id'] ?>, 'approved')" 
                                                    class="px-3 py-1 bg-gray-800 text-white rounded hover:bg-gray-700 text-sm">
                                                <i class="fas fa-check mr-1"></i>Approve
                                            </button>
                                            <button onclick="handleRequest(<?= $request['request_id'] ?>, 'rejected')" 
                                                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                <i class="fas fa-times mr-1"></i>Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Request History Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-1">Request History</h2>
                        <p class="text-gray-600">Previously approved or rejected requests</p>
                    </div>
                    
                    <?php if (empty($requestHistory)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-history text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-1">No History Yet</h3>
                        <p class="text-gray-600">No organizer requests have been processed yet</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Applicant</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Clubs</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requestHistory as $request): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user text-gray-400"></i>
                                            <span class="font-medium"><?= htmlspecialchars($request['user_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 bg-gray-800 text-white rounded-full text-sm font-medium">
                                            New Organizer
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                            <?= htmlspecialchars($request['club_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <?= date('n/j/Y', strtotime($request['requested_at'])) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            <?= $request['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Clubs Tab -->
        <div id="content-clubs" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold mb-1">Campus Clubs</h2>
                        <p class="text-gray-600 mb-4">View and manage clubs and their organizers</p>
                    </div>
                    <button onclick="openCreateClubDialog()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        <i class="fas fa-plus mr-2"></i>Create Club
                    </button>
                </div>

                <div class="space-y-3">
                    <?php foreach ($allClubs as $club): 
                        $clubOrganizers = $admin->getClubOrganizers($club['club_id']);
                    ?>
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-lg"><?= htmlspecialchars($club['nom']) ?></h3>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-sm text-gray-600">
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-user-tie"></i>
                                            <span><?= count($clubOrganizers) ?> organizers</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= date('M d, Y', strtotime($club['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($club['description'])): ?>
                                <p class="text-sm text-gray-600 mt-1 ml-6"><?= htmlspecialchars($club['description']) ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($clubOrganizers)): ?>
                                <div class="mt-2 ml-6">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <?php foreach ($clubOrganizers as $organizer): ?>
                                            <div class="flex items-center gap-1 px-2 py-1 bg-blue-50 rounded text-xs">
                                                <i class="fas fa-user text-blue-600"></i>
                                                <span class="text-blue-800"><?= htmlspecialchars($organizer['nom']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex gap-2 ml-4">
                                <button onclick='editClub(<?= json_encode($club) ?>)' 
                                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button onclick="deleteClub(<?= $club['club_id'] ?>)" 
                                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Events Tab -->
        <div id="content-events" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold mb-1">All Events</h2>
                        <p class="text-gray-600">View, edit, delete, or inspect event participants</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Club</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Capacity</th>
                                <th class="px-4 py-3 text-left">Registered</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allEvents as $ev): ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($ev['title']) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($ev['club_name']) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= date('M d, Y', strtotime($ev['date_event'])) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= (int)$ev['capacity'] ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= (int)$ev['registered_count'] ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button onclick='openAdminViewEvent(<?= json_encode($ev) ?>)'
                                                class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </button>
                                        <button onclick='openAdminParticipantsModal(<?= (int)$ev['event_id'] ?>, "<?= htmlspecialchars($ev['title'], ENT_QUOTES) ?>")'
                                                class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">
                                            <i class="fas fa-users mr-1"></i>Participants
                                        </button>
                                        <button onclick='openAdminEditEvent(<?= json_encode($ev) ?>)'
                                                class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <button onclick='adminDeleteEvent(<?= (int)$ev['event_id'] ?>, "<?= htmlspecialchars($ev['title'], ENT_QUOTES) ?>")'
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="content-users" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-1">System Users</h2>
                <p class="text-gray-600 mb-4">Review all users, change roles, or remove accounts</p>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Role</th>
                                <th class="px-4 py-3 text-left">Clubs</th>
                                <th class="px-4 py-3 text-left">Student ID</th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-left">Year</th>
                                <th class="px-4 py-3 text-left">Joined</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                            <tr class="border-t">
                                <td class="px-4 py-3"><?= htmlspecialchars($user['nom']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-sm font-medium 
                                        <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                           ($user['role'] === 'organizer' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <?= $user['role'] === 'organizer' ? htmlspecialchars($user['organizer_clubs'] ?? '-') : '-' ?>
                                </td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['student_id'] ?? '-') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['department'] ?? '-') ?></td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($user['year'])): ?>
                                        <?= $user['year'] === 'graduate' ? 'Graduate' : $user['year'] . ' Year' ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-2">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="post" action="../api/admin.php" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="change_user_role">
                                            <input type="hidden" name="account_id" value="<?= (int)$user['account_id'] ?>">
                                            <span class="text-sm text-gray-600">Role</span>
                                            <label class="flex items-center gap-1 text-sm">
                                                <input type="radio" name="new_role" value="user" <?= $user['role']==='user'?'checked':'' ?>>
                                                <span>User</span>
                                            </label>
                                            <label class="flex items-center gap-1 text-sm">
                                                <input type="radio" name="new_role" value="organizer" <?= $user['role']==='organizer'?'checked':'' ?>>
                                                <span>Organizer</span>
                                            </label>
                                            
                                            <div class="flex flex-col items-start gap-1 admin-role-club" style="display: <?= $user['role']==='organizer'?'flex':'none' ?>;">
                                             
                                                <span class="text-sm text-gray-600">Club</span>
                                                <select name="club_id" class="px-2 py-1 border rounded text-sm">
                                                    <option value="">-- Select club --</option>
                                                    <?php foreach ($allClubs as $clubOpt): ?>
                                                        <option value="<?= (int)$clubOpt['club_id'] ?>"><?= htmlspecialchars($clubOpt['nom']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                              
                                            </div>
                                            <button type="submit" class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">
                                                <i class="fas fa-user-cog mr-1"></i>Apply
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                        <?php if ($user['account_id'] != $_SESSION['user_id']): ?>
                                        <form method="post" action="../api/admin.php" onsubmit="return confirm('Delete user \"<?= htmlspecialchars($user['nom'], ENT_QUOTES) ?>\"?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="account_id" value="<?= (int)$user['account_id'] ?>">
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stats Tab -->
        <div id="content-stats" class="tab-content hidden">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2"><i class="fas fa-user-tag text-gray-600"></i>Users by Role</h2>
                    <canvas id="chartUsersByRole" height="180" class="mx-auto w-[440px] h-[240px]"></canvas>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2"><i class="fas fa-calendar-days text-gray-600"></i>Events by Month</h2>
                    <canvas id="chartEventsByMonth" height="220"></canvas>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2"><i class="fas fa-users-line text-gray-600"></i>Registrations per Event (Top 10)</h2>
                    <canvas id="chartRegistrationsPerEvent" height="240"></canvas>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2"><i class="fas fa-building text-gray-600"></i>Events per Club</h2>
                    <canvas id="chartEventsPerClub" height="240"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Create Admin Tab -->
        <div id="content-add-admin" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-1">Create Admin Account</h2>
                <p class="text-gray-600 mb-6">Fill in the details to create a new administrator account</p>

                <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <span id="errorText"></span>
                </div>

                <form id="addAdminForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nom" class="block text-sm font-medium mb-1">Full Name *</label>
                            <input type="text" id="nom" name="nom" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                                    placeholder="John Doe">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">Email *</label>
                            <input type="email" id="email" name="email" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                                    placeholder="admin@campus.edu">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium mb-1">Password *</label>
                            <input type="password" id="password" name="password" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                                    placeholder="Enter a strong password">
                        </div>

                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium mb-1">Confirm Password *</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                                    placeholder="Confirm your password">
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="reset" class="flex-1 px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                            Reset Form
                        </button>
                        <button id="addAdminSubmit" type="submit" class="flex-1 px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 font-medium">
                            Create Admin Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </main>

    <!-- Club Modal -->
    <div id="clubModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 id="clubModalTitle" class="text-2xl font-semibold">Create Club</h2>
                    <button onclick="closeClubModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form id="clubForm" class="space-y-4">
                    <input type="hidden" id="clubId" name="club_id">
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Club Name *</label>
                        <input type="text" id="clubName" name="nom" required 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea id="clubDescription" name="description" rows="4" 
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeClubModal()" 
                                class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit" id="clubSubmitBtn" 
                                class="flex-1 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">Create Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin: View Event Modal -->
    <div id="adminViewEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-3xl w-full overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 id="adminViewTitle" class="text-2xl font-semibold">Event</h2>
                <button onclick="closeAdminViewEvent()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <div class="grid md:grid-cols-2 gap-0">
                <div class="h-56 md:h-full bg-gray-100">
                    <img id="adminViewImage" src="" alt="Event image" class="w-full h-full object-cover" onerror="this.src='../assets/images/no_image_placeholder.png'">
                </div>
                <div class="p-4 space-y-3">
                    <p id="adminViewClub" class="text-sm text-gray-600"></p>
                    <p id="adminViewDate" class="text-sm text-gray-600"></p>
                    <p id="adminViewTime" class="text-sm text-gray-600"></p>
                    <p id="adminViewLocation" class="text-sm text-gray-600"></p>
                    <p id="adminViewCapacity" class="text-sm text-gray-600"></p>
                    <p id="adminViewDescription" class="text-gray-700"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin: Participants Modal -->
    <div id="adminParticipantsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-5xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6">
                <div>
                    <h2 id="adminParticipantsTitle" class="text-2xl font-semibold">Event Participants</h2>
                    <p id="adminParticipantsCount" class="text-gray-600">0 participants registered</p>
                </div>
                <button onclick="closeAdminParticipantsModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <div id="adminParticipantsList" class="px-6 pb-6 overflow-x-auto"></div>
        </div>
    </div>

    <!-- Admin: Edit Event Modal -->
    <div id="adminEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 id="adminEventModalTitle" class="text-2xl font-semibold">Edit Event</h2>
                    <button onclick="closeAdminEventModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-2xl"></i></button>
                </div>
                <form id="adminEventForm" class="space-y-4">
                    <input type="hidden" id="admin_event_id" name="event_id">
                    <input type="hidden" id="admin_image_url" name="image_url" value="assets/images/no_image_placeholder.png">
                    <div>
                        <label class="block text-sm font-medium mb-1">Title *</label>
                        <input type="text" id="admin_title" name="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description *</label>
                        <textarea id="admin_description" name="description" required rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Date *</label>
                            <input type="date" id="admin_date_event" name="date_event" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Location *</label>
                            <input type="text" id="admin_location" name="location" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Start Time *</label>
                            <input type="time" id="admin_start_time" name="start_time" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">End Time *</label>
                            <input type="time" id="admin_end_time" name="end_time" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Capacity *</label>
                            <input type="number" id="admin_capacity" name="capacity" required min="1" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Club *</label>
                            <select id="admin_club_id" name="club_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                                <?php foreach ($allClubs as $clubItem): ?>
                                <option value="<?= $clubItem['club_id'] ?>"><?= htmlspecialchars($clubItem['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Replace Image (Optional)</label>
                            <input type="file" id="admin_event_image" name="event_image" accept="image/*" class="block w-full text-sm text-gray-700">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeAdminEventModal()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit" id="adminSubmitBtn" class="flex-1 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/admin-panel.js?v=<?= time() ?>"></script>
    <script>
        // Prepare data for charts from PHP
        const ADMIN_DATA = {
            users: <?php echo json_encode($allUsers, JSON_UNESCAPED_UNICODE); ?>,
            clubs: <?php echo json_encode($allClubs, JSON_UNESCAPED_UNICODE); ?>,
            events: <?php echo json_encode($allEvents, JSON_UNESCAPED_UNICODE); ?>
        };

        function renderAdminCharts() {
            // Users by role
            const roleCounts = ADMIN_DATA.users.reduce((acc, u) => {
                const r = (u.role || 'user').toLowerCase();
                acc[r] = (acc[r] || 0) + 1;
                return acc;
            }, {});
            const usersCtx = document.getElementById('chartUsersByRole');
            if (usersCtx) {
                new Chart(usersCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(roleCounts).map(r => r.charAt(0).toUpperCase() + r.slice(1)),
                        datasets: [{
                            data: Object.values(roleCounts),
                            backgroundColor: ['#7C3AED','#0EA5E9','#10B981','#F59E0B','#EF4444']
                        }]
                    },
                    options: { plugins: { legend: { position: 'bottom' } } }
                });
            }

            // Events by month (current year)
            const now = new Date();
            const year = now.getFullYear();
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const eventsByMonth = new Array(12).fill(0);
            ADMIN_DATA.events.forEach(ev => {
                const d = new Date(ev.date_event);
                if (!isNaN(d) && d.getFullYear() === year) {
                    eventsByMonth[d.getMonth()]++;
                }
            });
            const eventsMonthCtx = document.getElementById('chartEventsByMonth');
            if (eventsMonthCtx) {
                new Chart(eventsMonthCtx, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: `Events in ${year}`,
                            data: eventsByMonth,
                            backgroundColor: '#0EA5E9'
                        }]
                    },
                    options: { scales: { y: { beginAtZero: true, precision: 0 } } }
                });
            }

            // Registrations per event (top 10)
            const topEvents = [...ADMIN_DATA.events]
                .sort((a,b) => (parseInt(b.registered_count||0,10)) - (parseInt(a.registered_count||0,10)))
                .slice(0, 10);
            const regCtx = document.getElementById('chartRegistrationsPerEvent');
            if (regCtx) {
                new Chart(regCtx, {
                    type: 'bar',
                    data: {
                        labels: topEvents.map(e => e.title),
                        datasets: [{
                            label: 'Registrations',
                            data: topEvents.map(e => parseInt(e.registered_count||0,10)),
                            backgroundColor: '#7C3AED'
                        }]
                    },
                    options: { indexAxis: 'y', scales: { x: { beginAtZero: true, precision: 0 } } }
                });
            }

            // Events per club
            const eventsPerClub = ADMIN_DATA.clubs.map(c => ({ name: c.nom, count: parseInt(c.event_count||0,10) }));
            const clubCtx = document.getElementById('chartEventsPerClub');
            if (clubCtx) {
                new Chart(clubCtx, {
                    type: 'bar',
                    data: {
                        labels: eventsPerClub.map(c => c.name),
                        datasets: [{
                            label: 'Events',
                            data: eventsPerClub.map(c => c.count),
                            backgroundColor: '#10B981'
                        }]
                    },
                    options: { indexAxis: 'y', scales: { x: { beginAtZero: true, precision: 0 } } }
                });
            }
        }

        // Render charts on first visit to Stats tab
        let chartsRendered = false;
        const originalShowTab = window.showTab;
        window.showTab = function(name) {
            originalShowTab(name);
            if (name === 'stats' && !chartsRendered) {
                chartsRendered = true;
                renderAdminCharts();
            }
        };
        
        // Toggle club selector visibility without external JS libs
        document.addEventListener('change', function(e) {
            if (e.target && e.target.name === 'new_role') {
                const container = e.target.closest('form')?.querySelector('.admin-role-club');
                if (!container) return;
                const show = e.target.value === 'organizer';
                container.style.display = show ? 'flex' : 'none';
            }
        });
        
        // Admin Events actions
        function openAdminViewEvent(ev) {
            document.getElementById('adminViewTitle').textContent = ev.title || 'Event';
            let img = ev.image_url || 'assets/images/no_image_placeholder.png';
            if (img.startsWith('storage/') || img.startsWith('assets/')) img = '../' + img;
            document.getElementById('adminViewImage').src = img;
            document.getElementById('adminViewClub').textContent = `Club: ${ev.club_name}`;
            document.getElementById('adminViewDate').textContent = `Date: ${new Date(ev.date_event).toLocaleDateString()}`;
            document.getElementById('adminViewTime').textContent = `Time: ${ev.start_time} - ${ev.end_time}`;
            document.getElementById('adminViewLocation').textContent = `Location: ${ev.location}`;
            document.getElementById('adminViewCapacity').textContent = `Capacity: ${ev.registered_count} / ${ev.capacity}`;
            document.getElementById('adminViewDescription').textContent = ev.description || '';
            document.getElementById('adminViewEventModal').classList.remove('hidden');
        }
        function closeAdminViewEvent(){ document.getElementById('adminViewEventModal').classList.add('hidden'); }

        async function adminDeleteEvent(eventId, title) {
            if (!confirm(`Delete event "${title}"? This cannot be undone.`)) return;
            try {
                const res = await fetch('../api/events.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', event_id: eventId })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Event deleted successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete event');
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred.');
            }
        }

        async function openAdminParticipantsModal(eventId, title) {
            try {
                const res = await fetch('../api/events.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_participants', event_id: eventId })
                });
                const data = await res.json();
                if (data.success) {
                    const list = data.participants || [];
                    document.getElementById('adminParticipantsTitle').textContent = `Participants — ${title}`;
                    document.getElementById('adminParticipantsCount').textContent = `${list.length} ${list.length===1?'participant':'participants'} registered`;
                    const html = list.length === 0
                        ? '<p class="text-center text-gray-600 py-8">No participants yet</p>'
                        : `<table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class=\"px-4 py-3 text-left\">Name</th>
                                    <th class=\"px-4 py-3 text-left\">Email</th>
                                    <th class=\"px-4 py-3 text-left\">Student ID</th>
                                    <th class=\"px-4 py-3 text-left\">Department</th>
                                    <th class=\"px-4 py-3 text-left\">Phone</th>
                                    <th class=\"px-4 py-3 text-left\">Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${list.map(p => `
                                    <tr class=\"border-t\">
                                        <td class=\"px-4 py-3\">${p.nom}</td>
                                        <td class=\"px-4 py-3\">${p.email}</td>
                                        <td class=\"px-4 py-3\">${p.student_id||''}</td>
                                        <td class=\"px-4 py-3\">${p.department||''}</td>
                                        <td class=\"px-4 py-3\">${p.phone_number||''}</td>
                                        <td class=\"px-4 py-3\">${new Date(p.registered_at).toLocaleDateString()}</td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>`;
                    document.getElementById('adminParticipantsList').innerHTML = html;
                    document.getElementById('adminParticipantsModal').classList.remove('hidden');
                } else {
                    alert(data.message || 'Failed to load participants');
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred.');
            }
        }
        function closeAdminParticipantsModal(){ document.getElementById('adminParticipantsModal').classList.add('hidden'); }

        function openAdminEditEvent(ev) {
            document.getElementById('adminEventModalTitle').textContent = 'Edit Event';
            document.getElementById('admin_event_id').value = ev.event_id;
            document.getElementById('admin_title').value = ev.title || '';
            document.getElementById('admin_description').value = ev.description || '';
            document.getElementById('admin_date_event').value = ev.date_event || '';
            document.getElementById('admin_location').value = ev.location || '';
            document.getElementById('admin_start_time').value = ev.start_time || '';
            document.getElementById('admin_end_time').value = ev.end_time || '';
            document.getElementById('admin_capacity').value = ev.capacity || 1;
            document.getElementById('admin_club_id').value = ev.club_id;
            document.getElementById('admin_image_url').value = ev.image_url || 'assets/images/no_image_placeholder.png';
            document.getElementById('adminEventModal').classList.remove('hidden');
        }
        function closeAdminEventModal(){ document.getElementById('adminEventModal').classList.add('hidden'); }

        document.getElementById('adminEventForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const submitBtn = document.getElementById('adminSubmitBtn');
            submitBtn.disabled = true; submitBtn.textContent = 'Saving...';
            try {
                const formData = new FormData(form);
                formData.append('action', 'update');
                const res = await fetch('../api/events.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) { alert('Event updated successfully'); location.reload(); }
                else { alert(data.message || 'Failed to update event'); }
            } catch (err) {
                console.error(err); alert('An error occurred.');
            } finally {
                submitBtn.disabled = false; submitBtn.textContent = 'Save Changes';
            }
        });
        // Open Stats tab by default on page load
        document.addEventListener('DOMContentLoaded', function() {
            showTab('stats');
        });
    </script>
</body>
</html>
