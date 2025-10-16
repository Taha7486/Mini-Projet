<?php
require_once '../includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - EventsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 max-w-5xl flex-1">
        <div class="space-y-8">
            <!-- Mission Section -->
            <div class="bg-white rounded-lg shadow-md px-12 py-6">
                <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                    Our Mission
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    EventsHub is designed to streamline event management across university campuses. 
                    We provide a comprehensive platform that connects students, organizers, and administrators 
                    to create, discover, and manage campus events efficiently.
                </p>
            </div>

            <!-- Features Section -->
            <div class="bg-white rounded-lg shadow-md px-12 py-6">
                <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                    Key Features
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-calendar-alt mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800">Event Discovery</h3>
                                <p class="text-gray-600 text-sm">Browse and search through all campus events with advanced filtering options.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-users mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800">Easy Registration</h3>
                                <p class="text-gray-600 text-sm">Simple one-click registration for events with automatic capacity management.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-tools mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800">Event Management</h3>
                                <p class="text-gray-600 text-sm">Comprehensive tools for organizers to create and manage their events.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-envelope mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800">Email Notifications</h3>
                                <p class="text-gray-600 text-sm">Automated email system for event updates and participant communication.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-certificate mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800">Attestations</h3>
                                <p class="text-gray-600 text-sm">Generate and send personalized participation certificates automatically.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-shield-alt mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800">Admin Controls</h3>
                                <p class="text-gray-600 text-sm">Robust administrative tools for system management and oversight.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works Section -->
            <div class="bg-white rounded-lg shadow-md px-12 py-6">
                <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                    How It Works
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-plus text-2xl text-gray-800"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">1. Sign Up</h3>
                        <p class="text-gray-600 text-sm">Create your account and verify your student information.</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-2xl text-gray-800"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">2. Discover Events</h3>
                        <p class="text-gray-600 text-sm">Browse events by club, date, or search for specific interests.</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-check text-2xl text-gray-800"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">3. Register & Participate</h3>
                        <p class="text-gray-600 text-sm">Register for events and receive automatic confirmations and updates.</p>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="bg-white rounded-lg shadow-md px-12 py-6">
                <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-envelope text-gray-600"></i>
                    Contact Us
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Technical Support</h3>
                        <p class="text-gray-600 text-sm mb-2">For technical issues or questions about the platform:</p>
                        <p class="text-gray-800 font-medium">support1@eventshub.edu</p>
                        <p class="text-gray-800 font-medium">support2@eventshub.edu</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">General Inquiries</h3>
                        <p class="text-gray-600 text-sm mb-2">For general questions or feedback:</p>
                        <p class="text-gray-800 font-medium">info@eventshub.edu</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
