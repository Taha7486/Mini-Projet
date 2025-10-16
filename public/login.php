<?php
require_once '../includes/session.php';

if(isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>
    
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md space-y-4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-2">Login</h2>
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

                    <div class="pt-4 border-t">
                        <div class="h-captcha" data-sitekey="1396d050-6650-4506-b164-48ac8fe4a3b0"></div>
                    </div>

                    <button type="submit" id="submitBtn"
                            class="w-full bg-black text-white py-2 rounded-lg hover:bg-gray-800 font-medium">
                        Log in
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-gray-600">Don't have an account? 
                        <a href="signup.php" class="text-black font-medium hover:underline">Sign up</a>
                    </p>
                </div>

                <div class="mt-4 text-center">
                    <a href="../index.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Events
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script>
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            errorMessage.classList.add('hidden');

            const formData = new FormData(loginForm);
            const payload = {
                action: 'login',
                email: formData.get('email'),
                password: formData.get('password')
            };

            try {
                const response = await fetch('../api/auth.php', {
                    method: 'POST',
                    body: JSON.stringify({ ...payload, hcaptcha_token: (document.querySelector('[name="h-captcha-response"]')?.value || '') }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                // always reset captcha before handling next step
                if (typeof hcaptcha !== 'undefined') {
                    hcaptcha.reset();
                }
                
                if (data.success) {
                    window.location.href = '../index.php';
                } else {
                    errorText.textContent = data.message || 'Invalid email or password';
                    errorMessage.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Log in';
                }
            } catch (error) {
                console.error('Error:', error);
                errorText.textContent = 'Network error. Please try again.';
                errorMessage.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Log in';
            }
        });
    </script>
</body>
</html>
