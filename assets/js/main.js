document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Dark Mode Toggle ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const lightIcon = document.getElementById('theme-toggle-light-icon');

    // Initial State
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        darkIcon.classList.remove('hidden');
        document.documentElement.classList.add('dark');
    } else {
        lightIcon.classList.remove('hidden');
        document.documentElement.classList.remove('dark');
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            darkIcon.classList.toggle('hidden');
            lightIcon.classList.toggle('hidden');
            themeToggleBtn.setAttribute('aria-pressed', document.documentElement.classList.contains('dark') ? 'false' : 'true');

            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
            // Update chart colors if exists
            if (window.myChart) {
                window.myChart.options.plugins.legend.labels.color = document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151';
                window.myChart.update();
            }
        });
    }

    // --- 2. Mobile Menu ---
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            const expanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
            mobileMenuBtn.setAttribute('aria-expanded', String(!expanded));
        });
    }

    // --- 3. BMI Calculator ---
    const bmiForm = document.getElementById('bmi-form');
    const bmiResult = document.getElementById('bmi-result');

    if (bmiForm) {
        bmiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const weight = parseFloat(document.getElementById('weight').value);
            const height = parseFloat(document.getElementById('height').value) / 100; // cm to m

            if (weight > 0 && height > 0) {
                const bmi = (weight / (height * height)).toFixed(1);
                let category = '';
                let colorClass = '';

                if (bmi < 18.5) {
                    category = 'Underweight';
                    colorClass = 'text-blue-500';
                } else if (bmi >= 18.5 && bmi < 24.9) {
                    category = 'Normal weight';
                    colorClass = 'text-teal-500';
                } else if (bmi >= 25 && bmi < 29.9) {
                    category = 'Overweight';
                    colorClass = 'text-orange-500';
                } else {
                    category = 'Obesity';
                    colorClass = 'text-red-500';
                }

                bmiResult.innerHTML = `Your BMI is <span class="font-bold ${colorClass}">${bmi}</span> (${category})`;
                bmiResult.classList.remove('hidden');
            }
        });
    }

    // --- 4. AJAX Form Submission ---
    const healthForm = document.getElementById('health-form');
    const resultsContainer = document.getElementById('results-container');
    const loadingSkeleton = document.getElementById('loading-skeleton');
    const continentInput = document.getElementById('continent_input');
    const continentError = document.getElementById('continent-error');

    if (healthForm) {
        healthForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validation
            if (!continentInput.value) {
                continentError.classList.remove('hidden');
                return;
            }
            continentError.classList.add('hidden');

            // Show Loading
            resultsContainer.innerHTML = '';
            loadingSkeleton.classList.remove('hidden');

            // Fetch
            const formData = new FormData(healthForm);
            
            fetch(healthForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingSkeleton.classList.add('hidden');
                
                if (data.error) {
                    resultsContainer.innerHTML = `<div class="bg-red-100 text-red-700 p-4 rounded-lg">${data.error}</div>`;
                } else {
                    // Inject HTML
                    resultsContainer.innerHTML = data.html;
                    // Initialize Dynamic Content
                    initResults(data.data);
                    // Scroll to results
                    resultsContainer.scrollIntoView({ behavior: 'smooth' });
                }
            })
            .catch(err => {
                loadingSkeleton.classList.add('hidden');
                console.error(err);
                resultsContainer.innerHTML = '<div class="bg-red-100 text-red-700 p-4 rounded-lg">An error occurred. Please try again.</div>';
            });
        });
    }
});

// --- 5. Visual Selector Logic (Global Scope) ---
function selectContinent(name, element) {
    document.getElementById('continent_input').value = name;
    
    // UI Update
    document.querySelectorAll('.continent-card').forEach(card => {
        card.classList.remove('selected', 'border-teal-500', 'bg-teal-50', 'dark:bg-teal-900/30');
        card.classList.add('border-gray-100', 'dark:border-gray-700', 'bg-gray-50', 'dark:bg-gray-700/50');
    });
    
    element.classList.remove('border-gray-100', 'dark:border-gray-700', 'bg-gray-50', 'dark:bg-gray-700/50');
    element.classList.add('selected', 'border-teal-500', 'bg-teal-50', 'dark:bg-teal-900/30');
    
    // Hide error
    document.getElementById('continent-error').classList.add('hidden');
}

// --- 6. Result Initialization (Global Scope) ---
function initResults(data) {
    if (!data) return;

    // Chart.js
    const ctx = document.getElementById('balanceChart');
    if (ctx) {
        // Destroy existing chart if any
        if (window.myChart) window.myChart.destroy();

        window.myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Work', 'Sleep', 'Exercise', 'Leisure'],
                datasets: [{
                    label: 'Daily Hours',
                    data: [8, 8, 1, 7], // Static for now, could be dynamic based on job
                    backgroundColor: [
                        'rgba(4, 120, 87, 0.8)',   // Work (Deep Emerald)
                        'rgba(59, 130, 246, 0.8)', // Sleep (Blue)
                        'rgba(16, 185, 129, 0.8)', // Exercise (Light Emerald)
                        'rgba(249, 115, 22, 0.8)'  // Leisure (Orange)
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { 
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                            font: { family: "'Poppins', sans-serif" }
                        }
                    }
                }
            }
        });
    }

    // PDF Download
    const downloadBtn = document.getElementById('download-pdf');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            const element = document.getElementById('results-container');
            const opt = {
                margin:       0.5,
                filename:     `WOKA-Health-Plan-${data.job}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        });
    }
}
