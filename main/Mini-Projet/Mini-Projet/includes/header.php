<?php
// Get current page for active styling
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Determine the correct path for assets based on current directory
$isInPublicFolder = strpos($_SERVER['PHP_SELF'], '/public/') !== false;
$assetsPath = $isInPublicFolder ? '../assets/' : 'assets/';
?>
<header class="bg-white border-b sticky top-0 z-10 shadow-sm">
    <div class="container mx-auto px-12 py-5">
        <div class="flex items-center justify-between">
            <!-- Logo and Navigation -->
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img src="<?= $assetsPath ?>images/logo.svg" alt="Campus Events Logo" class="h-16 w- object-contain">
                </div>
                
                <!-- Navigation Links -->
                <nav class="flex items-center gap-2">
                    <a href="<?= $isInPublicFolder ? '../index.php' : 'index.php' ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'index' ? 'bg-gray-100 font-semibold' : '' ?>">
                        Campus Events
                    </a>
                    <a href="<?= $isInPublicFolder ? 'about.php' : 'public/about.php' ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'about' ? 'bg-gray-100 font-semibold' : '' ?>">
                        About
                    </a>
                </nav>
            </div>
            
            <!-- User Actions -->
            <div class="flex items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <!-- Admin Panel (for admins only) -->
                    <?php if (isAdmin()): ?>
                        <a href="<?= $isInPublicFolder ? 'admin-panel.php' : 'public/admin-panel.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'admin-panel' ? 'bg-gray-100 font-semibold' : '' ?>">
                            Admin Panel
                        </a>
                    <?php endif; ?>

                    <!-- Manage Events (for organizers and admins) -->
                    <?php if (isOrganizer() || isAdmin()): ?>
                        <a href="<?= $isInPublicFolder ? 'organizer-dashboard.php' : 'public/organizer-dashboard.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'organizer-dashboard' ? 'bg-gray-100 font-semibold' : '' ?>">
                            Manage Events
                        </a>
                    <?php endif; ?>
                    
                    <!-- Profile -->
                    <a href="<?= $isInPublicFolder ? 'profile.php' : 'public/profile.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'profile' ? 'bg-gray-100 font-semibold' : '' ?>">
                        Profile
                    </a>

                    <!-- Log Out -->
                    <a href="<?= $isInPublicFolder ? '../api/auth.php?action=logout' : 'api/auth.php?action=logout' ?>" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        Log Out
                    </a>
                <?php else: ?>
                    <!-- Log In and Sign Up for non-logged users -->
                    <a href="<?= $isInPublicFolder ? 'login.php' : 'public/login.php' ?>" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        Log in
                    </a>
                    <a href="<?= $isInPublicFolder ? 'signup.php' : 'public/signup.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

