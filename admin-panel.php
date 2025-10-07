<?php
require_once 'config/database.php';
require_once 'classes/Admin.php';
require_once 'classes/Club.php';
require_once 'includes/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);
$admin->id = $_SESSION['user_id'];

// Get all data
$pendingRequests = $admin->getPendingRequests();
$allUsers = $admin->getAllUsers();
$allClubs = $admin->getAllClubs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Campus Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-10 shadow-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-shield text-2xl"></i>
                    <h1 class="text-2xl font-semibold">Admin Panel</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="index.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-eye mr-2"></i>View Public Page
                    </a>
                    <a href="organizer-dashboard.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-tasks mr-2"></i>Manage Events
                    </a>
                    <a href="api/auth.php?action=logout" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Tabs -->
    <div class="container mx-auto px-4 mt-6">
        <div class="bg-white rounded-lg shadow-sm p-2 flex gap-2">
            <button onclick="showTab('requests')" id="tab-requests" class="flex-1 px-4 py-2 rounded-lg bg-black text-white">
                <i class="fas fa-user-clock mr-2"></i>Organizer Requests (<?= count($pendingRequests) ?>)
            </button>
            <button onclick="showTab('clubs')" id="tab-clubs" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-users mr-2"></i>Clubs (<?= count($allClubs) ?>)
            </button>
            <button onclick="showTab('users')" id="tab-users" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-user mr-2"></i>Users (<?= count($allUsers) ?>)
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Organizer Requests Tab -->
        <div id="content-requests" class="tab-content">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Pending Organizer Requests</h2>
                
                <?php if (empty($pendingRequests)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">All caught up!</h3>
                    <p class="text-gray-600">No pending organizer requests at the moment</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pendingRequests as $request): ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <h3 class="font-semibold"><?= htmlspecialchars($request['user_name']) ?></h3>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm">
                                        <?= htmlspecialchars($request['email']) ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-3">
                                    <div>
                                        <i class="fas fa-id-card mr-1"></i>
                                        <?= htmlspecialchars($request['student_id']) ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-building mr-1"></i>
                                        <?= htmlspecialchars($request['department']) ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-graduation-cap mr-1"></i>
                                        <?= $request['year'] === 'graduate' ? 'Graduate' : $request['year'] . ' Year' ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-phone mr-1"></i>
                                        <?= htmlspecialchars($request['phone_number']) ?>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-users text-gray-400"></i>
                                    <span class="font-medium">Requested Club:</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                        <?= htmlspecialchars($request['club_name']) ?>
                                    </span>
                                </div>

                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Requested <?= date('M d, Y \a\t g:i A', strtotime($request['requested_at'])) ?>
                                </div>
                            </div>

                            <div class="flex gap-2 ml-4">
                                <button onclick="handleRequest(<?= $request['request_id'] ?>, 'approved')" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                                <button onclick="handleRequest(<?= $request['request_id'] ?>, 'rejected')" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Clubs Tab -->
        <div id="content-clubs" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Campus Clubs</h2>
                    <button onclick="openCreateClubDialog()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        <i class="fas fa-plus mr-2"></i>Create Club
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($allClubs as $club): ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="font-semibold"><?= htmlspecialchars($club['nom']) ?></h3>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($club['description'] ?? 'No description') ?></p>
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-500 mb-3">
                            <i class="fas fa-calendar mr-1"></i>
                            Created <?= date('M d, Y', strtotime($club['created_at'])) ?>
                        </div>

                        <div class="flex gap-2">
                            <button onclick='editClub(<?= json_encode($club) ?>)' 
                                    class="flex-1 px-3 py-2 border rounded-lg hover:bg-gray-50 text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteClub(<?= $club['club_id'] ?>)" 
                                    class="flex-1 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="content-users" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">System Users</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Role</th>
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
                                <td class="px-4 py-3"><?= htmlspecialchars($user['student_id']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['department']) ?></td>
                                <td class="px-4 py-3"><?= $user['year'] === 'graduate' ? 'Graduate' : $user['year'] . ' Year' ?></td>
                                <td class="px-4 py-3"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <button onclick="changeUserRole(<?= $user['account_id'] ?>, '<?= $user['role'] ?>')" 
                                            class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">
                                        <i class="fas fa-user-cog mr-1"></i>Change Role
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

    <script src="assets/js/admin-panel.js"></script>
</body>
</html>
