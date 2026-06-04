<?php
require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (register($name, $email, $password)) {
        // Auto login after registration
        login($email, $password);
        redirect('../dashboard.php');
    } else {
        $error = 'Email already exists or registration failed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - REMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-building text-3xl text-blue-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                <p class="text-gray-500 mt-2">Join REMS today</p>
            </div>
            
            <!-- Error message -->
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-center space-x-2">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Register form -->
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" 
                            placeholder="Enter your full name" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" 
                            placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" id="password"
                            class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" 
                            placeholder="Create a password" required>
                        <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="confirm_password" id="confirm_password"
                            class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" 
                            placeholder="Confirm your password" required>
                        <button type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <input type="checkbox" class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500" required>
                    <span class="ml-2 text-sm text-gray-600">
                        I agree to the <a href="#" class="text-blue-600 hover:text-blue-700">Terms of Service</a> 
                        and <a href="#" class="text-blue-600 hover:text-blue-700">Privacy Policy</a>
                    </span>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition">
                    Create Account
                </button>
            </form>
            
            <!-- Login link -->
            <p class="text-center mt-6 text-gray-600">
                Already have an account? 
                <a href="../index.php" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</a>
            </p>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
