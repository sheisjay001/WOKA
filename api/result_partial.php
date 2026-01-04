<?php if ($resultData): ?>
<div id="results-container" class="animate-fade-in-up space-y-6">
    
    <!-- Header & Print/Download -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Your Health Plan</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <span class="font-semibold text-teal-600 dark:text-teal-400"><?= e($resultData['job']) ?></span> in 
                <span class="font-semibold text-teal-600 dark:text-teal-400"><?= e($resultData['continent']) ?></span>
            </p>
        </div>
        <div class="flex gap-2 mt-4 md:mt-0 no-print">
            <button id="download-pdf" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition flex items-center gap-2 transform hover:scale-105 active:scale-95">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition flex items-center gap-2 transform hover:scale-105 active:scale-95">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Charts & Hydration -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Visual Breakdown -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-t-4 border-indigo-500 hover:shadow-xl transition-shadow">
            <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white text-center">Daily Balance</h3>
            <div class="relative h-48 w-full">
                <canvas id="balanceChart"></canvas>
            </div>
        </div>

        <!-- Hydration Tracker -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-t-4 border-blue-400 flex flex-col justify-center items-center text-center hover:shadow-xl transition-shadow">
            <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-white"><i class="fas fa-tint text-blue-500 animate-bounce"></i> Hydration Goal</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Based on your job activity level.</p>
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-1" id="water-amount">
                <?= ($resultData['job'] === 'Manual Labor' || $resultData['job'] === 'Healthcare') ? '3.0 - 4.0L' : '2.0 - 2.5L' ?>
            </div>
            <p class="text-xs text-gray-500">Recommended daily intake</p>
        </div>
    </div>

    <!-- Job Health Risks & Tips -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-l-4 border-teal-500 print-break-inside-avoid transition-colors duration-200">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800 dark:text-white">
            <i class="fas fa-briefcase text-teal-600 dark:text-teal-400"></i> Work & Health Profile
        </h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4 italic"><?= e($resultData['health_tips']['description']) ?></p>
        
        <div class="mb-6">
            <h4 class="font-semibold text-red-500 dark:text-red-400 mb-2">Common Health Risks:</h4>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($resultData['health_tips']['health_risks'] as $risk): ?>
                    <span class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-3 py-1 rounded-full text-sm font-medium"><?= e($risk) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-700 dark:text-blue-300 mb-2"><i class="fas fa-bed"></i> Sleep</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300"><?= e($resultData['health_tips']['recommendations']['sleep']) ?></p>
            </div>
            <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">
                <h4 class="font-semibold text-teal-700 dark:text-teal-300 mb-2"><i class="fas fa-running"></i> Exercise</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300"><?= e($resultData['health_tips']['recommendations']['exercise']) ?></p>
            </div>
        </div>
    </div>

    <!-- Diet & Nutrition -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-l-4 border-orange-500 print-break-inside-avoid transition-colors duration-200">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800 dark:text-white">
            <i class="fas fa-utensils text-orange-600 dark:text-orange-400"></i> Nutrition & Diet
        </h3>
        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg mb-6">
            <p class="text-gray-800 dark:text-gray-200"><strong>Focus:</strong> <?= e($resultData['health_tips']['recommendations']['diet_focus']) ?></p>
        </div>
        
        <div class="mb-6">
            <h4 class="font-semibold text-orange-600 dark:text-orange-400 mb-2"><i class="fas fa-apple-alt"></i> Recommended Fruits</h4>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($resultData['local_fruits'] as $fruit): ?>
                    <span class="bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200 text-sm font-medium px-3 py-1 rounded-full"><?= e($fruit) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <h4 class="font-semibold text-orange-600 dark:text-orange-400 mb-2"><i class="fas fa-hamburger"></i> Recommended Meals</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($resultData['meals'] as $meal): ?>
                    <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-600 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="font-bold text-gray-800 dark:text-white"><?= e($meal['name']) ?></h5>
                            <span class="text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded"><?= e($meal['type']) ?></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 italic"><?= e($meal['benefits']) ?></p>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach ($meal['ingredients'] as $ing): ?>
                                <span class="text-xs text-teal-600 dark:text-teal-400">• <?= e($ing) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Countries Covered -->
    <div class="text-center pb-8">
        <p class="text-sm text-gray-500 dark:text-gray-500">
            Ingredients based on availability in: <?= implode(', ', array_map('e', $resultData['countries'])) ?> and neighboring regions.
        </p>
    </div>
</div>
<?php endif; ?>
