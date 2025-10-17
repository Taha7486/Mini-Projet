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
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <!-- Tabs -->
    <div class="container mx-auto px-12 mt-6">
        <div class="bg-white rounded-lg shadow-sm p-2 flex gap-2">
            <button onclick="showTab('requests')" id="tab-requests" class="flex-1 px-4 py-2 rounded-lg bg-black text-white">
                <i class="fas fa-user-clock mr-2"></i>Requests (<?= count($pendingRequests) ?>)
            </button>
            <button onclick="showTab('clubs')" id="tab-clubs" class="flex-1 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-users mr-2"></i>Clubs (<?= count($allClubs) ?>)
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
                                    <div class="flex gap-2">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                        <button onclick="changeUserRole(<?= $user['account_id'] ?>, '<?= $user['role'] ?>')" 
                                                class="px-3 py-1 border rounded hover:bg-gray-50 text-sm">
                                            <i class="fas fa-user-cog mr-1"></i>Change Role
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['account_id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?= $user['account_id'] ?>, '<?= htmlspecialchars($user['nom']) ?>')" 
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
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

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/admin-panel.js"></script>
</body>
</html>
