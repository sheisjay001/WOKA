<?php
session_start();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">Thank you for your message! We will get back to you shortly.</div>';
}
// Security headers
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com;");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact WOKA Support.">
    <title>Contact Us - WOKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        teal: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out'
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { transform: 'translateY(20px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200 flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-teal-600 dark:bg-teal-900 text-white shadow-lg sticky top-0 z-50 transition-colors duration-200 backdrop-blur-sm bg-opacity-95">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold flex items-center gap-2 tracking-tight">
                <i class="fas fa-heartbeat"></i> WOKA
            </a>
            <div class="flex items-center gap-6">
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="hidden md:flex space-x-6 items-center">
                    <a href="/about" class="hover:text-teal-200 transition-colors font-medium">About</a>
                    <a href="/contact" class="hover:text-teal-200 transition-colors font-medium">Contact</a>
                </div>
                <button id="theme-toggle" type="button" class="text-white hover:bg-teal-700/50 rounded-full p-2 transition-all transform hover:scale-110">
                    <i id="theme-toggle-dark-icon" class="fas fa-moon hidden"></i>
                    <i id="theme-toggle-light-icon" class="fas fa-sun hidden"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-teal-700 dark:bg-teal-800 pb-4 px-6">
            <a href="/about" class="block py-2 hover:text-teal-200">About</a>
            <a href="/contact" class="block py-2 hover:text-teal-200">Contact</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12 flex-grow">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 md:p-12 animate-slide-up">
            <a href="/" class="inline-flex items-center text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Back to Homepage
            </a>
            <h1 class="text-3xl font-bold text-teal-700 dark:text-teal-400 mb-2 text-center">Get in Touch</h1>
            <p class="text-center text-gray-600 dark:text-gray-400 mb-8">We'd love to hear from you. Fill out the form below.</p>
            
            <?= $msg ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Name</label>
                    <input type="text" name="name" class="w-full p-4 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none transition-shadow shadow-sm" placeholder="John Doe" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" class="w-full p-4 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none transition-shadow shadow-sm" placeholder="john@example.com" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
                    <textarea name="message" rows="5" class="w-full p-4 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none transition-shadow shadow-sm" placeholder="How can we help you?" required></textarea>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-teal-700 hover:from-teal-600 hover:to-teal-800 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 shadow-lg transform hover:-translate-y-1">
                    Send Message
                </button>
            </form>
        </div>
    </main>

    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-8 text-center text-sm text-gray-500">
        <p>&copy; <?= date('Y') ?> WOKA Health. All rights reserved.</p>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
