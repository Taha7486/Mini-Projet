<?php
require_once 'includes/session.php';

if(isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-4">
        <div class="text-center space-y-2">
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-calendar-alt text-3xl"></i>
                <h1 class="text-3xl font-semibold">Campus Events</h1>
            </div>
            <p class="text-gray-600">Sign in to your account</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold mb-2">Sign In</h2>
            <p class="text-gray-600 mb-6">Enter your credentials to continue</p>

            <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <span id="errorText"></span>
            </div>

            <form id="loginForm" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                           placeholder="your.email@university.edu">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                           placeholder="••••••••">
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-black text-white py-2 rounded-lg hover:bg-gray-800 font-medium">
                    Sign In
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-gray-600">Don't have an account? 
                    <a href="signup.php" class="text-black font-medium hover:underline">Sign up</a>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="index.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Events
                </a>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing in...';
            errorMessage.classList.add('hidden');

            const formData = new FormData(loginForm);
            const payload = {
                action: 'login',
                email: formData.get('email'),
                password: formData.get('password')
            };

            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorText.textContent = data.message || 'Invalid email or password';
                    errorMessage.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            } catch (error) {
                console.error('Error:', error);
                errorText.textContent = 'Network error. Please try again.';
                errorMessage.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
