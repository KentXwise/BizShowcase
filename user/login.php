<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BizShowcase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
        }
        
        .input-field {
            transition: all 0.3s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
        
        .login-btn {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .illustration {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .error-message {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
        }
        
        .slide-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            min-height: 100vh;
            transition: transform 0.5s ease-in-out;
        }
        
        .slide-left-to-right {
            transform: translateX(100%);
            animation: slideLeftToRight 0.5s ease-in-out forwards;
        }
        
        .slide-right-to-left {
            transform: translateX(-100%);
            animation: slideRightToLeft 0.5s ease-in-out forwards;
        }
        
        @keyframes slideLeftToRight {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes slideRightToLeft {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'includes/db_connect.php';

    if (isset($_SESSION['user_id'])) {
        header("Location: home.php");
        exit();
    }

    $error = '';
    $modal_warning = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if ($email === false) {
            $error = "Invalid email format";
        } else {
            $stmt = $conn->prepare("SELECT user_id, password, status, suspension_end_date FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check if the account is a Gmail with deleted status
                if (preg_match('/@gmail\.com$/i', $email) && $user['status'] === 'deleted') {
                    $modal_warning = "This Gmail account has been deleted and cannot be used to log in.";
                } else {
                    // Check if the account is suspended
                    $is_suspended = $user['status'] === 'suspended' && !empty($user['suspension_end_date']) && strtotime($user['suspension_end_date']) > time();

                    if ($is_suspended) {
                        $modal_warning = "Your account is suspended until " . date('F j, Y, g:i a', strtotime($user['suspension_end_date'])) . ".";
                    } elseif ($user['status'] === 'active' && password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['user_id'];
                        header("Location: home.php");
                        exit();
                    } else {
                        $error = "Invalid email or password";
                    }
                }
            } else {
                $error = "Invalid email or password";
            }
        }
    }
    ?>

    <div class="slide-container">
        <div class="slide-content" id="pageContent">
            <div class="min-h-screen flex flex-col md:flex-row">
                <!-- Left side - Form -->
                <div class="w-full md:w-1/2 flex items-center justify-center p-8">
                    <div class="w-full max-w-md">
                        <div class="flex items-center justify-center mb-8">
                            <div class="bg-indigo-100 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h1 class="ml-3 text-2xl font-bold text-gray-800">BizShowcase</h1>
                        </div>
                        
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome back!</h2>
                        <p class="text-gray-600 mb-8">Enter your credentials to access your account</p>
                        
                        <?php if ($error): ?>
                            <div class="error-message bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <span><?php echo htmlspecialchars($error); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="input-field pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="you@example.com" required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input type="password" id="password" name="password" class="input-field pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="••••••••" required>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                                </div>
                                
                                <div class="text-sm">
                                    <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                                </div>
                            </div>
                            
                            <div>
                                <button type="submit" class="login-btn w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Sign in
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-6 text-center text-sm">
                            <p class="text-gray-600">
                                Don't have an account?
                                <a href="signup.php" class="font-medium text-indigo-600 hover:text-indigo-500">Sign up</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Right side - Illustration -->
                <div class="hidden md:flex md:w-1/2 gradient-bg items-center justify-center p-12">
                    <div class="text-center">
                        <img src="../assets/img/Background 1.png" alt="Business collaboration" class="illustration w-full max-w-lg mx-auto">
                        <h3 class="mt-8 text-2xl font-bold text-white">Connect with businesses worldwide</h3>
                        <p class="mt-2 text-indigo-100">Showcase your products and services to potential customers and partners</p>
                    </div>
                </div>
            </div>

            <!-- Modal for warnings -->
            <div class="fixed z-10 inset-0 overflow-y-auto hidden" id="warningModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
                    
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Account Warning</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($modal_warning); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" id="modalOkButton" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.getElementById('pageContent');
            const referrer = document.referrer;
            const isFromSignup = referrer.includes('signup.php');

            // Apply animation based on navigation direction
            if (isFromSignup) {
                content.classList.add('slide-right-to-left');
            } else {
                content.classList.add('slide-left-to-right');
            }

            // Remove animation class after transition ends
            content.addEventListener('animationend', function() {
                content.classList.remove('slide-left-to-right', 'slide-right-to-left');
            });

            // Show modal if there is a warning
            <?php if ($modal_warning): ?>
                const modal = document.getElementById('warningModal');
                modal.classList.remove('hidden');
                
                const okButton = document.getElementById('modalOkButton');
                okButton.addEventListener('click', function() {
                    modal.classList.add('hidden');
                });
                
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>