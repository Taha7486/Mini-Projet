<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - EventsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <i class="fas fa-check text-2xl text-green-600"></i>
            </div>
            
            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Email Verified!</h1>
            
            <!-- Message -->
            <div class="space-y-4 mb-8">
                <p class="text-gray-600">
                    Thank you for signing up! Your email address has been successfully verified.
                </p>
                <p class="text-gray-600">
                    You can now log in to your account and start participating in events.
                </p>
            </div>
            
            <!-- Action Button -->
            <div class="space-y-4">
                <a href="login.php" 
                   class="w-full bg-black text-white py-3 px-4 rounded-lg hover:bg-gray-800 transition-colors duration-200 font-medium inline-flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Log In
                </a>
                
                <a href='../index.php' 
                   class="w-full text-gray-600 py-2 px-4 rounded-lg hover:text-gray-800 transition-colors duration-200 inline-flex items-center justify-center">
                    <i class="fas fa-home mr-2"></i>
                    Back to Home
                </a>
            </div>
        </div>
        
        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Have questions? 
                <a href="mailto:support@eventshub.com" class="text-blue-600 hover:text-blue-800">
                    Contact our support team
                </a>
            </p>
        </div>
    </div>
</body>
</html>
