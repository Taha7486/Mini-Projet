<?php
// Get current page for active styling
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<header class="bg-white border-b sticky top-0 z-10 shadow-sm">
    <div class="container mx-auto px-5 py-5">
        <div class="flex items-center justify-between">
            <!-- Logo and Navigation -->
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img src="assets/images/logo.svg" alt="Campus Events Logo" class="h-16 w-48 object-contain">
                </div>
                
                <!-- Navigation Links -->
                <nav class="flex items-center gap-2">
                    <a href="index.php" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'index' ? 'bg-gray-100 font-semibold' : '' ?>">
                        Campus Events
                    </a>
                    <a href="about.php" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'about' ? 'bg-gray-100 font-semibold' : '' ?>">
                        About
                    </a>
                </nav>
            </div>
            
            <!-- User Actions -->
            <div class="flex items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <!-- Admin Panel (for admins only) -->
                    <?php if (isAdmin()): ?>
                        <a href="admin-panel.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'admin-panel' ? 'bg-gray-100 font-semibold' : '' ?>">
                            Admin Panel
                        </a>
                    <?php endif; ?>

                    <!-- Manage Events (for organizers and admins) -->
                    <?php if (isOrganizer() || isAdmin()): ?>
                        <a href="organizer-dashboard.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'organizer-dashboard' ? 'bg-gray-100 font-semibold' : '' ?>">
                            Manage Events
                        </a>
                    <?php endif; ?>
                    
                    <!-- Profile -->
                    <a href="profile.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'profile' ? 'bg-gray-100 font-semibold' : '' ?>">
                        Profile
                    </a>

                    <!-- Sign Out -->
                    <a href="api/auth.php?action=logout" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        Sign Out
                    </a>
                <?php else: ?>
                    <!-- Sign In and Sign Up for non-logged users -->
                    <a href="login.php" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        Sign In
                    </a>
                    <a href="signup.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

