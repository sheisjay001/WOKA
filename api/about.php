<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="About WOKA - World Class Health.">
    <title>About Us - WOKA</title>
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
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 md:p-12 animate-fade-in">
            <h1 class="text-4xl font-bold text-teal-700 dark:text-teal-400 mb-6 text-center">About WOKA</h1>
            
            <div class="space-y-6 text-lg leading-relaxed text-gray-700 dark:text-gray-300">
                <p>
                    <strong class="text-teal-600 dark:text-teal-400">WOKA</strong> stands for <span class="italic">Worker</span>. We are dedicated to providing personalized health and wellness recommendations tailored to the unique needs of workers worldwide.
                </p>
                <p>
                    Understanding that a software engineer in Tokyo has different health challenges than a construction worker in New York, WOKA bridges the gap between professional lifestyle and personal well-being.
                </p>
                
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mt-8 mb-4">Our Mission</h2>
                <p>
                    To empower every professional to live a healthier, more balanced life by delivering actionable, data-driven insights based on their location and occupation.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 text-center">
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <i class="fas fa-globe-americas text-4xl text-teal-500 mb-4"></i>
                        <h3 class="font-bold text-lg mb-2">Global Reach</h3>
                        <p class="text-sm">Insights covering all major continents and regions.</p>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <i class="fas fa-briefcase text-4xl text-teal-500 mb-4"></i>
                        <h3 class="font-bold text-lg mb-2">Job Specific</h3>
                        <p class="text-sm">Tailored advice for various professions and lifestyles.</p>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <i class="fas fa-leaf text-4xl text-teal-500 mb-4"></i>
                        <h3 class="font-bold text-lg mb-2">Holistic Health</h3>
                        <p class="text-sm">Focusing on diet, exercise, sleep, and mental well-being.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-8 text-center text-sm text-gray-500">
        <p>&copy; <?= date('Y') ?> WOKA Health. All rights reserved.</p>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
