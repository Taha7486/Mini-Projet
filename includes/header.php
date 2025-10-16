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
                    <a href="<?= $isInPublicFolder ? '../index.php' : 'index.php' ?>">
                        <img src="<?= $assetsPath ?>images/logo.svg" alt="EventsHub Logo" class="h-12 w-auto object-contain">
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-2">
                    <a href="<?= $isInPublicFolder ? '../index.php' : 'index.php' ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'index' ? 'bg-gray-100 font-semibold' : '' ?>">
                        Campus Events
                    </a>
                    <a href="<?= $isInPublicFolder ? 'about.php' : 'public/about.php' ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'about' ? 'bg-gray-100 font-semibold' : '' ?>">
                        About
                    </a>
                </nav>
            </div>

            <!-- Mobile Menu Button -->
            <button id="menu-btn" class="md:hidden p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300">
                <svg id="menu-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- User Actions -->
            <div class="hidden md:flex items-center gap-2">
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

        <!-- Mobile Dropdown Menu -->
        <div id="mobile-menu" class="hidden flex-col gap-3 mt-4 md:hidden">
            <!-- Navigation Links (Mobile) -->
            <nav class="flex flex-col gap-2 border-t pt-3">
                <a href="<?= $isInPublicFolder ? '../index.php' : 'index.php' ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'index' ? 'bg-gray-100 font-semibold' : '' ?>">Campus Events</a>
                <a href="<?= $isInPublicFolder ? 'about.php' : 'public/about.php' ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $currentPage === 'about' ? 'bg-gray-100 font-semibold' : '' ?>">About</a>
            </nav>

            <!-- User Actions (Mobile) -->
            <div class="flex flex-col gap-2 border-t pt-3">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?= $isInPublicFolder ? 'admin-panel.php' : 'public/admin-panel.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'admin-panel' ? 'bg-gray-100 font-semibold' : '' ?>">Admin Panel</a>
                    <?php endif; ?>
                    <?php if (isOrganizer() || isAdmin()): ?>
                        <a href="<?= $isInPublicFolder ? 'organizer-dashboard.php' : 'public/organizer-dashboard.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'organizer-dashboard' ? 'bg-gray-100 font-semibold' : '' ?>">Manage Events</a>
                    <?php endif; ?>
                    <a href="<?= $isInPublicFolder ? 'profile.php' : 'public/profile.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50 <?= $currentPage === 'profile' ? 'bg-gray-100 font-semibold' : '' ?>">Profile</a>
                    <a href="<?= $isInPublicFolder ? '../api/auth.php?action=logout' : 'api/auth.php?action=logout' ?>" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">Log Out</a>
                <?php else: ?>
                    <a href="<?= $isInPublicFolder ? 'login.php' : 'public/login.php' ?>" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">Log in</a>
                    <a href="<?= $isInPublicFolder ? 'signup.php' : 'public/signup.php' ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu Toggle Script -->
<script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');

    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
        menu.classList.toggle('flex');
    });
</script>
