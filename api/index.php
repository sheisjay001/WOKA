<?php
session_start();

// 1. Security Headers (World Class Security)
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com;");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// 2. Rate Limiting (Simple Session-based)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['last_sub_time']) && (time() - $_SESSION['last_sub_time'] < 1)) {
        die(json_encode(['error' => 'Please wait a moment before trying again.']));
    }
    $_SESSION['last_sub_time'] = time();
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function for sanitization
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Load database connection
require_once 'db.php';

// Fetch Continents for Selection
$continents = [];
$result = mysqli_query($conn, "SELECT name FROM continents ORDER BY name");
while ($row = mysqli_fetch_assoc($result)) {
    $continents[] = $row['name'];
}

// Icon Mapping for Visual Selector
$continentIcons = [
    'Africa' => 'fa-globe-africa',
    'Asia' => 'fa-globe-asia',
    'Europe' => 'fa-globe-europe',
    'North America' => 'fa-globe-americas',
    'South America' => 'fa-globe-americas',
    'Oceania' => 'fa-water',
    'Antarctica' => 'fa-snowflake'
];

// Fetch Job Types for Dropdown
$jobTypes = [];
$result = mysqli_query($conn, "SELECT type FROM jobs ORDER BY type");
while ($row = mysqli_fetch_assoc($result)) {
    $jobTypes[] = $row['type'];
}

$selectedContinent = '';
$selectedJob = '';
$resultData = null;
$error = null;

// AJAX Handling
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please refresh.";
        if ($isAjax) { echo json_encode(['error' => $error]); exit; }
    } else {
        // Input Validation
        $selectedContinent = mysqli_real_escape_string($conn, $_POST['continent'] ?? '');
        $selectedJob = mysqli_real_escape_string($conn, $_POST['job'] ?? '');

        if (!in_array($selectedContinent, $continents)) {
            $error = "Invalid continent selected.";
        } elseif (!in_array($selectedJob, $jobTypes)) {
            $error = "Invalid job type selected.";
        } else {
            // 1. Get Continent ID
            $stmt = $conn->prepare("SELECT id FROM continents WHERE name = ?");
            $stmt->bind_param("s", $selectedContinent);
            $stmt->execute();
            $contResult = $stmt->get_result();
            if ($contResult->num_rows > 0) {
                $continentId = $contResult->fetch_assoc()['id'];
                $stmt->close();

                // 2. Get Job Data
                $stmt = $conn->prepare("SELECT * FROM jobs WHERE type = ?");
                $stmt->bind_param("s", $selectedJob);
                $stmt->execute();
                $jobResult = $stmt->get_result();
                $jobDataRow = $jobResult->fetch_assoc();
                $stmt->close();

                // Structure Job Data
                $jobData = [
                    'type' => $jobDataRow['type'],
                    'description' => $jobDataRow['description'],
                    'health_risks' => json_decode($jobDataRow['health_risks'], true),
                    'recommendations' => [
                        'exercise' => $jobDataRow['rec_exercise'],
                        'sleep' => $jobDataRow['rec_sleep'],
                        'diet_focus' => $jobDataRow['rec_diet_focus'],
                        'fruit_intake' => $jobDataRow['rec_fruit_intake']
                    ]
                ];

                // 3. Get Meals
                $meals = [];
                $stmt = $conn->prepare("SELECT name, ingredients, benefits, type FROM meals WHERE continent_id = ?");
                $stmt->bind_param("i", $continentId);
                $stmt->execute();
                $mealsResult = $stmt->get_result();
                while ($row = $mealsResult->fetch_assoc()) {
                    $row['ingredients'] = json_decode($row['ingredients'], true);
                    $meals[] = $row;
                }
                $stmt->close();

                // 4. Get Local Fruits
                $localFruits = [];
                $stmt = $conn->prepare("SELECT name FROM local_fruits WHERE continent_id = ?");
                $stmt->bind_param("i", $continentId);
                $stmt->execute();
                $fruitsResult = $stmt->get_result();
                while ($row = $fruitsResult->fetch_assoc()) {
                    $localFruits[] = $row['name'];
                }
                $stmt->close();

                // 5. Get Countries
                $countries = [];
                $stmt = $conn->prepare("SELECT name FROM countries WHERE continent_id = ?");
                $stmt->bind_param("i", $continentId);
                $stmt->execute();
                $countriesResult = $stmt->get_result();
                while ($row = $countriesResult->fetch_assoc()) {
                    $countries[] = $row['name'];
                }
                $stmt->close();

                $resultData = [
                    'continent' => $selectedContinent,
                    'job' => $selectedJob,
                    'meals' => $meals,
                    'local_fruits' => $localFruits,
                    'countries' => $countries,
                    'health_tips' => $jobData
                ];
            } else {
                $error = "Continent not found.";
            }
        }
    }

    if ($isAjax) {
        if ($error) {
            echo json_encode(['error' => $error]);
        } else {
            // Render the Result Partial HTML
            ob_start();
            include 'result_partial.php'; 
            $html = ob_get_clean();
            echo json_encode(['html' => $html, 'data' => $resultData]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Personalized health and wellness recommendations for workers worldwide.">
    <title>WOKA - Worker Health</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .continent-card.selected { border-color: #059669; background-color: #ecfdf5; }
        .dark .continent-card.selected { border-color: #34d399; background-color: #064e3b; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            .print-break-inside-avoid { break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">

    <!-- Navbar -->
    <nav class="bg-teal-600 dark:bg-teal-900 text-white shadow-lg sticky top-0 z-50 transition-colors duration-200 backdrop-blur-sm bg-opacity-95">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold flex items-center gap-2 tracking-tight">
                <i class="fas fa-heartbeat animate-pulse"></i> WOKA
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

    <!-- Hero Section -->
    <header class="relative bg-teal-700 dark:bg-teal-800 text-white py-20 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/medical-icons.png')]"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-slide-up">Work Healthy, Live Better</h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-2xl mx-auto font-light">
                Tailored health insights for your region and profession.
            </p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 md:px-6 py-12 -mt-10 relative z-20">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: Selection & BMI (4 cols) -->
            <div class="lg:col-span-4 space-y-8 no-print">
                
                <!-- Selection Form -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden p-6 md:p-8 transition-all hover:shadow-2xl">
                    <h2 class="text-2xl font-bold mb-6 text-center text-teal-700 dark:text-teal-400">
                        <i class="fas fa-sliders-h mr-2"></i> Customize Plan
                    </h2>
                    
                    <form id="health-form" action="" method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <!-- Visual Continent Selector -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Select Region</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <?php foreach ($continents as $continent): 
                                    $icon = $continentIcons[$continent] ?? 'fa-globe';
                                ?>
                                    <div class="continent-card p-3 rounded-xl border-2 border-gray-100 dark:border-gray-700 hover:border-teal-500 dark:hover:border-teal-400 cursor-pointer bg-gray-50 dark:bg-gray-700/50 transition-all flex flex-col items-center justify-center gap-2 group" onclick="selectContinent('<?= e($continent) ?>', this)">
                                        <i class="fas <?= $icon ?> text-2xl text-gray-400 group-hover:text-teal-500 transition-colors"></i>
                                        <span class="text-xs font-medium text-center"><?= e($continent) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="continent" id="continent_input" required>
                            <p id="continent-error" class="text-red-500 text-xs mt-1 hidden">Please select a region.</p>
                        </div>

                        <!-- Job Selector -->
                        <div>
                            <label for="job" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Profession</label>
                            <div class="relative">
                                <select name="job" id="job" class="w-full p-4 pl-10 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none appearance-none transition-shadow shadow-sm" required>
                                    <option value="">Select your role...</option>
                                    <?php foreach ($jobTypes as $type): ?>
                                        <option value="<?= e($type) ?>" <?= $selectedJob === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-gradient-to-r from-teal-500 to-teal-700 hover:from-teal-600 hover:to-teal-800 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex justify-center items-center gap-2">
                            <span>Generate Insights</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <!-- BMI Calculator -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-t-4 border-gray-600 dark:border-gray-500">
                    <h3 class="text-lg font-bold mb-4 text-center text-gray-700 dark:text-gray-300"><i class="fas fa-calculator mr-2"></i>BMI Calculator</h3>
                    <form id="bmi-form" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Weight (kg)</label>
                                <input type="number" id="weight" class="w-full p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border-0 focus:ring-2 focus:ring-teal-500" placeholder="0">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Height (cm)</label>
                                <input type="number" id="height" class="w-full p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border-0 focus:ring-2 focus:ring-teal-500" placeholder="0">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold py-2 rounded-lg transition text-sm">Calculate</button>
                    </form>
                    <div id="bmi-result" class="mt-4 text-center text-sm font-medium hidden p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"></div>
                </div>

            </div>

            <!-- Right Column: Results (8 cols) -->
            <div class="lg:col-span-8">
                <!-- Loading State -->
                <div id="loading-skeleton" class="hidden space-y-6 animate-pulse">
                    <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                        <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                    </div>
                    <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                </div>

                <!-- Results Container -->
                <div id="results-container">
                    <?php if ($resultData): ?>
                         <?php include 'result_partial.php'; ?>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-12 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 opacity-75">
                            <i class="fas fa-heartbeat text-6xl text-teal-100 dark:text-teal-900 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-500 dark:text-gray-400">Ready to optimize your health?</h3>
                            <p class="text-gray-400 mt-2">Select your region and profession to see your personalized dashboard.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 mt-12 py-8 text-center text-sm text-gray-500 no-print">
        <p>&copy; <?= date('Y') ?> WOKA Health. All rights reserved.</p>
    </footer>

    <script src="/assets/js/main.js"></script>
    <?php if ($resultData): ?>
    <script>
        // Init chart if loaded via PHP
        document.addEventListener('DOMContentLoaded', () => {
             // Mock chart init for PHP load (will be handled by main.js logic ideally, but fallback here)
             initResults(<?= json_encode($resultData) ?>);
        });
    </script>
    <?php endif; ?>
</body>
</html>
