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
    <title>Sign Up - Campus Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl space-y-4">
        <div class="text-center space-y-2">
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-calendar-alt text-3xl"></i>
                <h1 class="text-3xl font-semibold">Campus Events</h1>
            </div>
            <p class="text-gray-600">Create your account</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold mb-2">Sign Up</h2>
            <p class="text-gray-600 mb-6">Fill in your details to create an account</p>

            <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <span id="errorText"></span>
            </div>

            <form id="signupForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                               placeholder="john.doe@university.edu">
                    </div>

                    <div>
                        <label for="student_id" class="block text-sm font-medium mb-1">Student ID *</label>
                        <input type="text" id="student_id" name="student_id" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                               placeholder="2024001234">
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium mb-1">Year of Study *</label>
                        <select id="year" name="year" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                            <option value="">Select year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                            <option value="graduate">Graduate</option>
                        </select>
                    </div>

                    <div id="departmentWrapper">
                        <label for="department" class="block text-sm font-medium mb-1">Department *</label>
                        <input type="text" id="department" name="department" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                               placeholder="Department">
                    </div>

                    <div id="filiereWrapper" class="hidden">
                        <label for="filiere" class="block text-sm font-medium mb-1">Filière *</label>
                        <select id="filiere" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                            <option value="">Choose filière</option>
                            <option value="GI">GI</option>
                            <option value="SCM">SCM</option>
                            <option value="BDIA">BDIA</option>
                            <option value="GM">GM</option>
                            <option value="GSTR">GSTR</option>
                        </select>
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium mb-1">Phone Number *</label>
                        <input type="tel" id="phone_number" name="phone_number" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                               placeholder="+1 (555) 123-4567">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium mb-1">Password *</label>
                        <input type="password" id="password" name="password" required minlength="6"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium mb-1">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                               placeholder="••••••••">
                    </div>
                </div>

                <!-- CAPTCHA -->
                <div class="pt-4 border-t">
                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="captcha" name="captcha" required
                                       class="w-5 h-5 cursor-pointer">
                                <label for="captcha" class="cursor-pointer select-none">
                                    I'm not a robot
                                </label>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" 
                                     alt="reCAPTCHA" class="h-10 w-10">
                                <div class="flex flex-col items-end">
                                    <span class="text-xs text-gray-600">reCAPTCHA</span>
                                    <div class="flex gap-1 text-xs text-gray-500">
                                        <a href="#" class="hover:underline">Privacy</a>
                                        <span>-</span>
                                        <a href="#" class="hover:underline">Terms</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-black text-white py-2 rounded-lg hover:bg-gray-800 font-medium">
                    Create Account
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-gray-600">Already have an account? 
                    <a href="login.php" class="text-black font-medium hover:underline">Sign in</a>
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
        const signupForm = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        const yearSelect = document.getElementById('year');
        const departmentWrapper = document.getElementById('departmentWrapper');
        const departmentInput = document.getElementById('department');
        const filiereWrapper = document.getElementById('filiereWrapper');
        const filiereSelect = document.getElementById('filiere');

        function updateFiliereVisibility() {
            const y = yearSelect.value;
            const needsFiliere = y === '3' || y === '4' || y === '5';
            const needsDepartment = y === 'graduate';
            
            if (needsFiliere) {
                filiereWrapper.classList.remove('hidden');
                departmentWrapper.classList.add('hidden');
                departmentInput.removeAttribute('required');
            } else if (needsDepartment) {
                filiereWrapper.classList.add('hidden');
                departmentWrapper.classList.remove('hidden');
                departmentInput.setAttribute('required', 'required');
            } else {
                // Years 1-2: hide both department and filiere
                filiereWrapper.classList.add('hidden');
                departmentWrapper.classList.add('hidden');
                departmentInput.removeAttribute('required');
            }
        }

        yearSelect.addEventListener('change', updateFiliereVisibility);
        updateFiliereVisibility();

        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(signupForm);
            
            // Validate passwords match
            if (formData.get('password') !== formData.get('confirm_password')) {
                errorText.textContent = "Passwords don't match";
                errorMessage.classList.remove('hidden');
                return;
            }

            // Validate CAPTCHA
            if (!document.getElementById('captcha').checked) {
                errorText.textContent = "Please verify that you're not a robot";
                errorMessage.classList.remove('hidden');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
            errorMessage.classList.add('hidden');

            // For years 3-5, send filiere value as department
            // For years 1-2, send empty department
            // For graduate, send department input
            let effectiveDepartment = '';
            if (yearSelect.value === '3' || yearSelect.value === '4' || yearSelect.value === '5') {
                effectiveDepartment = filiereSelect.value || '';
            } else if (yearSelect.value === 'graduate') {
                effectiveDepartment = formData.get('department') || '';
            }

            const payload = {
                action: 'request_signup',
                nom: formData.get('nom'),
                email: formData.get('email'),
                student_id: formData.get('student_id'),
                year: formData.get('year'),
                department: effectiveDepartment,
                phone_number: formData.get('phone_number'),
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
                    alert('We sent you a verification link. Please check your email.');
                    window.location.href = 'login.php';
                } else {
                    errorText.textContent = data.message || 'Failed to create account';
                    errorMessage.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Account';
                }
            } catch (error) {
                console.error('Error:', error);
                errorText.textContent = 'Network error. Please try again.';
                errorMessage.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        });
    </script>
</body>
</html>
