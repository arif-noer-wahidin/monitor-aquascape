<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aquascape Monitoring Dashboard</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
    <!-- Day.js library for date parsing and formatting -->
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/dayjs.min.js"></script>
    <!-- Day.js Plugins (PENTING untuk adapter Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/utc.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/weekday.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/advancedFormat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/localizedFormat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/weekOfYear.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/timezone.js"></script> <!-- Tambahkan plugin timezone -->
    <!-- Chart.js adapter for Day.js -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-dayjs"></script>
 
    <!-- Custom CSS for better UX/UI -->
    <style>
        /* Custom scrollbar for better aesthetics */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* Styling for toggle switch */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #4ade80; /* green-400 */
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #4ade80; /* green-400 */
        }
        
        /* Spinner for background loading */
        .loading-spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #4ade80; /* green-400 */
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Basic table styling for responsiveness (kept for reference, but chart replaces it) */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Ensures columns are fixed width */
        }
        .history-table th, .history-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #374151; /* gray-700 */
            word-wrap: break-word; /* Prevents long text from overflowing */
        }
        .history-table th {
            background-color: #1f2937; /* gray-800 */
            font-weight: bold;
            color: #e5e7eb; /* gray-200 */
        }
        .history-table tbody tr:hover {
            background-color: #374151; /* gray-700 */
        }
        /* Style for scrollable table container */
        .table-scroll-container {
            max-height: 400px; /* Max height for the table content */
            overflow-y: auto; /* Enable vertical scrolling */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            border-radius: 0.75rem; /* rounded-lg */
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-200 font-sans antialiased">

    <!-- Error Toast (tetap ada untuk notifikasi penting) -->
    <div id="error-toast" class="hidden fixed top-5 right-5 bg-red-600 text-white py-3 px-5 rounded-lg shadow-lg flex items-center z-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="error-message"></span>
    </div>

    <main class="container mx-auto p-4 md:p-6 lg:p-8">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Aquascape Dashboard</h1>
                <p class="text-gray-400 mt-1">Monitoring & Kontrol Sistem Berbasis AI Fuzzy</p>
            </div>
            <div class="text-sm text-gray-400 mt-4 md:mt-0 flex items-center">
                <p>Terakhir diperbarui: <span id="last-updated" class="font-semibold text-emerald-400">Memuat...</span></p>
                <div id="loading-spinner" class="loading-spinner ml-2 hidden"></div>
            </div>
        </header>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kolom Kiri: Sensor & Rekomendasi -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- Sensor Cards -->
                <div id="sensor-suhu" class="bg-gray-800 p-6 rounded-2xl shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Suhu Air</p>
                        <p id="suhu-value" class="text-4xl font-bold text-white">-</p>
                    </div>
                    <div class="text-right">
                        <div id="suhu-status" class="px-3 py-1 text-sm font-semibold rounded-full">-</div>
                        <p class="text-xs text-gray-500 mt-1">Celcius</p>
                    </div>
                </div>

                <div id="sensor-ph" class="bg-gray-800 p-6 rounded-2xl shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Tingkat pH</p>
                        <p id="ph-value" class="text-4xl font-bold text-white">-</p>
                    </div>
                     <div class="text-right">
                        <div id="ph-status" class="px-3 py-1 text-sm font-semibold rounded-full">-</div>
                        <p class="text-xs text-gray-500 mt-1">Potential Hydrogen</p>
                    </div>
                </div>

                <div id="sensor-tds" class="bg-gray-800 p-6 rounded-2xl shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">TDS</p>
                        <p id="tds-value" class="text-4xl font-bold text-white">-</p>
                    </div>
                     <div class="text-right">
                        <div id="tds-status" class="px-3 py-1 text-sm font-semibold rounded-full">-</div>
                        <p class="text-xs text-gray-500 mt-1">PPM</p>
                    </div>
                </div>

                <!-- Fuzzy Recommendation Card -->
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-2xl shadow-lg text-white">
                    <div class="flex items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                        <div>
                            <p class="font-semibold">Rekomendasi Sistem Fuzzy</p>
                            <p id="fuzzy-rekomendasi" class="text-lg font-bold mt-1">Memuat...</p>
                        </div>
                    </div>
                </div>
                
                  <!-- NEW: Panel Range Definisi -->
                <div class="bg-gray-800 p-4 md:p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">Definisi Rentang Parameter</h2>
                    <div class="table-scroll-container bg-gray-700 rounded-lg">
                        <table id="range-definitions-table" class="history-table">
                            <thead>
                                <tr>
                                    <th>Variabel</th>
                                    <th>Kategori</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan dimasukkan di sini oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <p id="range-definitions-loading" class="text-gray-400 text-center mt-4">Memuat data...</p>
                </div>

               
            </div>

            <!-- Kolom Kanan: Kontrol & Riwayat Sensor (Sekarang Grafik) -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Control Panel -->
                <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">Panel Kontrol</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Relay 1 Control (Lampu) -->
                        <div class="bg-gray-700 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-3">
                                <label for="relay1" class="font-semibold text-lg">Lampu</label>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="relay1" id="relay1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                                    <label for="relay1" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-500 cursor-pointer"></label>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <label for="timer1On" class="block mb-1 text-gray-400">Timer ON</label>
                                    <input type="time" id="timer1On" class="w-full bg-gray-800 border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label for="timer1Off" class="block mb-1 text-gray-400">Timer OFF</label>
                                    <input type="time" id="timer1Off" class="w-full bg-gray-800 border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>
                        <!-- Relay 2 Control (CO₂ / Kipas) -->
                        <div class="bg-gray-700 p-4 rounded-lg">
                           <div class="flex justify-between items-center mb-3">
                                <label for="relay2" class="font-semibold text-lg">CO₂ / Kipas</label>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="relay2" id="relay2" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                                    <label for="relay2" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-500 cursor-pointer"></label>
                                </div>
                            </div>
                             <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <label for="timer2On" class="block mb-1 text-gray-400">Timer ON</label>
                                    <input type="time" id="timer2On" class="w-full bg-gray-800 border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label for="timer2Off" class="block mb-1 text-gray-400">Timer OFF</label>
                                    <input type="time" id="timer2Off" class="w-full bg-gray-800 border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="mt-6 flex justify-end">
                        <button id="save-timers-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                            Simpan Pengaturan Timer
                        </button>
                    </div>
                </div>

                <!-- History Chart -->
                <div class="bg-gray-800 p-4 md:p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">Riwayat Sensor</h2>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button class="filter-btn bg-gray-700 hover:bg-gray-600 text-gray-200 py-2 px-4 rounded-lg text-sm transition duration-300" data-timeframe="1hour">1 Jam Terakhir</button>
                        <button class="filter-btn bg-gray-700 hover:bg-gray-600 text-gray-200 py-2 px-4 rounded-lg text-sm transition duration-300" data-timeframe="1day">1 Hari Terakhir</button>
                        <button class="filter-btn bg-gray-700 hover:bg-gray-600 text-gray-200 py-2 px-4 rounded-lg text-sm transition duration-300" data-timeframe="1week">1 Minggu Terakhir</button>
                        <button class="filter-btn bg-gray-700 hover:bg-gray-600 text-gray-200 py-2 px-4 rounded-lg text-sm transition duration-300" data-timeframe="all">Semua Data</button> <!-- Default selected -->
                    </div>
                    <div id="history-chart-container" class="relative bg-gray-700 p-4 rounded-lg flex items-center justify-center" style="height: 400px;">
                        <canvas id="historyChart"></canvas>
                        <p id="chart-loading-message" class="absolute text-gray-400 hidden">Memuat grafik...</p>
                    </div>
                </div>
                <!-- NEW: Panel Fuzzy Rules -->
                <div class="bg-gray-800 p-4 md:p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">Aturan Fuzzy</h2>
                    <div class="table-scroll-container bg-gray-700 rounded-lg">
                        <table id="fuzzy-rules-table" class="history-table">
                            <thead>
                                <tr>
                                    <th>Rule ID</th>
                                    <th>Suhu</th>
                                    <th>pH</th>
                                    <th>TDS</th>
                                    <th>Aksi Direkomendasikan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan dimasukkan di sini oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <p id="fuzzy-rules-loading" class="text-gray-400 text-center mt-4">Memuat data...</p>
                </div>
            </div>
            
            
        </div>
    </main>

    <script>
    // Declare DOM element variables in a broader scope (global or early script)
    const errorToast = document.getElementById('error-toast');
    const errorMessage = document.getElementById('error-message');
    const loadingSpinner = document.getElementById('loading-spinner');
    const lastUpdatedEl = document.getElementById('last-updated');

    
    // Function showError should be accessible anytime
    function showError(message) {
        if (errorMessage && errorToast) { // Ensure elements exist before accessing them
            errorMessage.textContent = message;
            errorToast.classList.remove('hidden');
            setTimeout(() => {
                errorToast.classList.add('hidden');
            }, 5000);
        } else {
            console.error("Error Toast elements not found:", message);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Get base URL for API
        let BASE_API_URL = '';
        try {
            const currentHref = window.location.href; // Get current full URL
            const urlObj = new URL(currentHref); // Try to parse original URL

            // More robust logic for determining BASE_API_URL for Canvas environment (blob:)
            if (urlObj.protocol === 'blob:') {
                // In Canvas environment, iframes have blob: URLs, but API is served from parent origin.
                // We need to get the origin from the parent window.
                const parentOrigin = window.parent.location.origin;
                const pathSegments = urlObj.pathname.split('/');
                
                const indexOfIndex = pathSegments.indexOf('index.php');
                let basePath = '';
                if (indexOfIndex > -1) {
                    basePath = pathSegments.slice(0, indexOfIndex).join('/') + '/'; 
                } else {
                    const lastSlash = urlObj.pathname.lastIndexOf('/');
                    basePath = urlObj.pathname.substring(0, lastSlash + 1);
                }
                
                BASE_API_URL = `${parentOrigin}${basePath}api.php`;
                console.log("BASE_API_URL (Canvas Preview - Blob URL) successfully determined (using parent.location.origin):", BASE_API_URL);

            } else if (urlObj.protocol.startsWith('http')) {
                // This is a standard web server (HTTP/HTTPS)
                const pathSegments = urlObj.pathname.split('/');
                pathSegments[pathSegments.length - 1] = 'api.php'; // Replace index.php with api.php
                BASE_API_URL = `${urlObj.origin}${pathSegments.join('/')}`;
                console.log("BASE_API_URL (HTTP/HTTPS Server) successfully determined:", BASE_API_URL);

            } else {
                // For file:// or other unsupported protocols
                console.error("Application must be run from a web server (HTTP/HTTPS) to function. Cannot make API calls.");
                showError("Application must be run via a web server (e.g., Apache, Nginx, or XAMPP/WAMP/MAMP) to load data. Please set up your server environment.");
                window.fetch = () => Promise.reject(new Error("Fetch API not allowed for local files."));
                return; 
            }
        } catch (e) {
            console.error("Error determining BASE_API_URL:", e);
            showError("Failed to determine base API URL. Please check console for details.");
            window.fetch = () => Promise.reject(new Error("Fetch API not allowed because base URL could not be determined."));
            return;
        }
        console.log("Final BASE_API_URL used:", BASE_API_URL);

        // --- IMPORTANT: Check Day.js Library Availability ---
        const isDayjsLoaded = typeof dayjs !== 'undefined';
        if (!isDayjsLoaded) {
            console.error("Error: Day.js library not loaded. Date functionality may be impaired.");
            showError("Pustaka Day.js tidak dimuat. Mohon periksa koneksi internet Anda.");
            return; // Stop execution if essential library is missing
        }

        // Extend dayjs with plugins
        dayjs.extend(window.dayjs_plugin_utc);
        dayjs.extend(window.dayjs_plugin_weekday);
        dayjs.extend(window.dayjs_plugin_advancedFormat);
        dayjs.extend(window.dayjs_plugin_localizedFormat);
        dayjs.extend(window.dayjs_plugin_weekOfYear);
        dayjs.extend(window.dayjs_plugin_timezone); // Extend with timezone plugin

        // Set default timezone to Asia/Jakarta (WIB)
        dayjs.tz.setDefault('Asia/Jakarta'); // Set default timezone for Day.js

        // Check Chart.js availability
        const isChartJsLoaded = typeof Chart !== 'undefined';
        if (!isChartJsLoaded) {
            console.error("Error: Chart.js library not loaded. Chart functionality may be impaired.");
            showError("Pustaka Chart.js tidak dimuat. Mohon periksa koneksi internet Anda.");
            return; // Stop execution if essential library is missing
        }


        // Other elements that only need to be accessed after DOMContentLoaded
        const suhuValueEl = document.getElementById('suhu-value');
        const suhuStatusEl = document.getElementById('suhu-status');
        const phValueEl = document.getElementById('ph-value');
        const phStatusEl = document.getElementById('ph-status');
        const tdsValueEl = document.getElementById('tds-value');
        const tdsStatusEl = document.getElementById('tds-status'); 

        const fuzzyEl = document.getElementById('fuzzy-rekomendasi');

        const relay1Toggle = document.getElementById('relay1');
        const relay2Toggle = document.getElementById('relay2');
        const timer1OnInput = document.getElementById('timer1On');
        const timer1OffInput = document.getElementById('timer1Off');
        const timer2OnInput = document.getElementById('timer2On');
        const timer2OffInput = document.getElementById('timer2Off');
        const saveTimersBtn = document.getElementById('save-timers-btn');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const historyChartCanvas = document.getElementById('historyChart'); // Reference to the canvas element
        const chartLoadingMessage = document.getElementById('chart-loading-message'); // Loading message for chart
                  // Elemen-elemen baru untuk tabel
        const rangeDefinitionsTableBody = document.querySelector('#range-definitions-table tbody');
        const fuzzyRulesTableBody = document.querySelector('#fuzzy-rules-table tbody');
        const rangeDefinitionsLoading = document.getElementById('range-definitions-loading');
        const fuzzyRulesLoading = document.getElementById('fuzzy-rules-loading');

        // Mengubah filter default dari 'all' menjadi '1hour'
        let isUpdating = false; 
        let currentFilter = '1hour'; // Mengubah nilai default filter
        let refreshIntervalId; 
        let historyChartInstance = null; // To store Chart.js instance
        let dataCache = {}; // Cache for prefetched history data

        // --- Utility Functions ---
        function showLoading() {
            loadingSpinner.classList.remove('hidden');
            lastUpdatedEl.textContent = 'Memuat...';
        }

        function hideLoading() {
            loadingSpinner.classList.add('hidden');
            const now = dayjs().tz('Asia/Jakarta'); // Get current time in WIB
            lastUpdatedEl.textContent = now.format('HH:mm:ss'); // Format to WIB
        }
        
        function getStatusColor(status) {
            status = String(status).toLowerCase(); 
            if (status === 'ideal' || status === 'optimal') return 'bg-green-500 text-green-900';
            if (status === 'panas' || status === 'dingin' || status === 'asam' || status === 'basa' || status === 'tinggi' || status === 'rendah') return 'bg-yellow-500 text-yellow-900';
            return 'bg-gray-500 text-gray-900'; 
        }
        
        

        /**
         * Set CSS classes for the active filter button.
         * This provides visual feedback to the user about which filter is currently active.
         * @param {string} selectedTimeframe - The `data-timeframe` value of the selected button.
         */
        function setActiveFilterButton(selectedTimeframe) {
            filterButtons.forEach(button => {
                if (button.dataset.timeframe === selectedTimeframe) {
                    button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'text-gray-200');
                    button.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
                } else {
                    button.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
                    button.classList.add('bg-gray-700', 'hover:bg-gray-600', 'text-gray-200');
                }
                // Ensure filter buttons are enabled
                button.disabled = false; 
            });
        }
        async function fetchAPI(params) {
            const url = 'api.php?' + new URLSearchParams(params);
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                return data;
            } catch (error) {
                console.error('Fetch API error:', error);
                showToast(`Gagal memuat data: ${error.message}`);
                return null;
            }
        }
          // NEW: Fungsi untuk mengambil data dan merender tabel
        async function fetchAndRenderTables() {
            // Tampilkan pesan loading
            rangeDefinitionsLoading.classList.remove('hidden');
            fuzzyRulesLoading.classList.remove('hidden');

            // Ambil data range definitions dari api.php
            const rangeData = await fetchAPI({ action: 'getRangeDefinitions' });
            if (rangeData) {
                renderTable(rangeDefinitionsTableBody, rangeData);
                rangeDefinitionsLoading.classList.add('hidden');
            } else {
                rangeDefinitionsTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-gray-500">Gagal memuat data.</td></tr>';
            }

            // Ambil data fuzzy rules dari api.php
            const fuzzyData = await fetchAPI({ action: 'getFuzzyRules' });
            if (fuzzyData) {
                renderFuzzyRulesTable(fuzzyRulesTableBody, fuzzyData);
                fuzzyRulesLoading.classList.add('hidden');
            } else {
                fuzzyRulesTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500">Gagal memuat data.</td></tr>';
            }
        }

        // NEW: Fungsi untuk merender tabel range definitions
        function renderTable(tableBody, data) {
            tableBody.innerHTML = ''; // Kosongkan tabel
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.Variabel}</td>
                    <td>${item.Kategori}</td>
                    <td>${item.Min}</td>
                    <td>${item.Max}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        // NEW: Fungsi untuk merender tabel fuzzy rules
        function renderFuzzyRulesTable(tableBody, data) {
            tableBody.innerHTML = ''; // Kosongkan tabel
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.RuleID}</td>
                    <td>${item.Suhu}</td>
                    <td>${item.pH}</td>
                    <td>${item.TDS}</td>
                    <td>${item['Aksi Direkomendasikan']}</td>
                `;
                tableBody.appendChild(row);
            });
        }
        

        // --- API Communication ---
        /**
         * Fetches real-time and history data from the API.
         * This process is performed asynchronously in the background.
         * @param {boolean} forceFetch - If true, bypass cache and force a new fetch.
         */
        async function fetchData(forceFetch = false) {
            if (isUpdating && !forceFetch) { // Allow forceFetch to bypass this check
                console.log("Sudah dalam proses pembaruan, melewati pengambilan data.");
                return; 
            }
            if (!BASE_API_URL || !(BASE_API_URL.startsWith('http://') || BASE_API_URL.startsWith('https://'))) {
                console.error("Tidak dapat mengambil data: BASE_API_URL tidak valid.");
                showError("Tidak dapat mengambil data: URL API tidak valid. Pastikan aplikasi dijalankan di server web.");
                return;
            }

            if (!forceFetch && dataCache[currentFilter]) {
                console.log(`Menggunakan data histori dari cache untuk filter: ${currentFilter}`);
                updateUI(dataCache[currentFilter]);
                // Still do a background fetch for fresh data, but don't block UI
                prefetchData(currentFilter, true); // Refresh cache in background
                return;
            }

            isUpdating = true;
            showLoading(); 
            chartLoadingMessage.classList.remove('hidden'); // Show chart loading message

            try {
                const url = `${BASE_API_URL}?action=getAllData&timeframe=${currentFilter}`;
                console.log("URL yang akan di-fetch:", url);
                const response = await fetch(url);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Respon HTTP error:', response.status, errorText);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }
                const data = await response.json();
                console.log("Data diterima dari API:", data);

                if (data.error) {
                    throw new Error(data.error);
                }
                if (data.realtime === null && data.history === null && data.error) {
                    throw new Error(`Error Data: ${data.error}`);
                }

                dataCache[currentFilter] = data; // Cache the fetched data
                updateUI(data);

            } catch (error) {
                console.error('Error pengambilan data:', error);
                showError('Gagal memuat data: ' + error.message);
                // Clear chart if error occurs
                if (historyChartInstance) {
                    historyChartInstance.destroy();
                    historyChartInstance = null;
                }
                chartLoadingMessage.textContent = 'Gagal memuat grafik.';
            } finally {
                isUpdating = false;
                hideLoading(); 
                chartLoadingMessage.classList.add('hidden'); // Hide chart loading message
            }
        }

        /**
         * Prefetches data for a given timeframe and stores it in the cache.
         * @param {string} timeframe - The timeframe to prefetch (e.g., '1hour', '1day').
         * @param {boolean} force - If true, force prefetch even if already in cache.
         */
        async function prefetchData(timeframe, force = false) {
            if (!force && dataCache[timeframe]) {
                console.log(`Data untuk ${timeframe} sudah ada di cache.`); // Corrected console.log
                return;
            }
            if (!BASE_API_URL || !(BASE_API_URL.startsWith('http://') || BASE_API_URL.startsWith('https://'))) {
                console.error("Tidak dapat prefetch data: BASE_API_URL tidak valid.");
                return;
            }

            console.log(`Memulai prefetch data untuk ${timeframe}...`);
            try {
                const url = `${BASE_API_URL}?action=getAllData&timeframe=${timeframe}`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                dataCache[timeframe] = data;
                console.log(`Prefetch data untuk ${timeframe} berhasil.`);
            } catch (error) {
                console.error(`Error prefetching data untuk ${timeframe}:`, error);
            }
        }


        /**
         * Mengirim perintah kontrol ke API.
         * Proses ini juga dilakukan secara asinkron.
         * @param {Object} params - Objek parameter yang akan dikirim (misal: { relay1: 'on' }).
         */
        async function sendCommand(params) {
            if (isUpdating) { 
                console.warn("Sedang dalam proses pembaruan data, perintah ditunda.");
                showError("Sistem sedang sibuk, coba lagi sebentar.");
                return;
            }
            if (!BASE_API_URL || !(BASE_API_URL.startsWith('http://') || BASE_API_URL.startsWith('https://'))) {
                console.error("Tidak dapat mengirim perintah: BASE_API_URL tidak valid.");
                showError("Tidak dapat mengirim perintah: URL API tidak valid. Pastikan aplikasi dijalankan di server web.");
                return;
            }

            showLoading(); 
            try {
                const formData = new FormData();
                for (const key in params) {
                    if (params[key] !== null && params[key] !== undefined) {
                        formData.append(key, params[key]);
                    }
                }
                console.log("Mengirim perintah dengan parameter:", Object.fromEntries(formData));

                const response = await fetch(`${BASE_API_URL}?action=setStatus`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json(); 
                console.log("Respon perintah dari API:", result);

                if (result.error) {
                    throw new Error(result.error);
                }
                if (result.message && result.message === 'OK') { 
                    console.log("Perintah berhasil, memperbarui data.");
                    await fetchData(true); // Force fetch after command to get latest state
                } else {
                    throw new Error('Gagal mengirim perintah: Respon tidak terduga dari API.');
                }

            } catch (error) {
                console.error('Error perintah:', error);
                showError('Gagal mengirim perintah: ' + error.message);
            } finally {
                hideLoading(); 
            }
        }

        // --- UI Update Functions ---
        /**
         * Memperbarui elemen UI dengan data real-time dan histori yang diterima.
         * @param {Object} data - Objek data yang diterima dari API, berisi `realtime` dan `history`.
         */
        function updateUI(data) {
            // Perbarui Data Real-time
            const realtime = data.realtime;
            if (realtime) {
                suhuValueEl.textContent = realtime.suhu ?? '-';
                suhuStatusEl.textContent = realtime.suhu_status ?? '-';
                suhuStatusEl.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(realtime.suhu_status || '')}`;

                phValueEl.textContent = realtime.ph ?? '-';
                phStatusEl.textContent = realtime.ph_status ?? '-';
                phStatusEl.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(realtime.ph_status || '')}`;

                tdsValueEl.textContent = realtime.tds ?? '-';
                tdsStatusEl.textContent = realtime.tds_status ?? '-';
                tdsStatusEl.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(realtime.tds_status || '')}`;

                fuzzyEl.textContent = realtime.fuzzy_rekomendasi ?? 'Tidak ada rekomendasi';

                relay1Toggle.checked = realtime.relay1 === 'on';
                relay2Toggle.checked = realtime.relay2 === 'on';

                const formatTimeOrEmpty = (isoString) => {
                    // Hanya gunakan dayjs jika dimuat
                    if (isDayjsLoaded && isoString) {
                        // Parse as UTC, then convert to WIB for display
                        const date = dayjs.utc(isoString).tz('Asia/Jakarta'); 
                        if (date.isValid()) {
                            return date.format('HH:mm');
                        } else {
                            console.warn(`String ISO timer tidak valid: '${isoString}'. Mengatur ke kosong.`);
                        }
                    }
                    return '';
                };

                if (document.activeElement !== timer1OnInput) {
                    timer1OnInput.value = formatTimeOrEmpty(realtime.timer1On);
                }
                if (document.activeElement !== timer1OffInput) {
                    timer1OffInput.value = formatTimeOrEmpty(realtime.timer1Off);
                }
                if (document.activeElement !== timer2OnInput) {
                    timer2OnInput.value = formatTimeOrEmpty(realtime.timer2On);
                }
                if (document.activeElement !== timer2OffInput) {
                    timer2OffInput.value = formatTimeOrEmpty(realtime.timer2Off);
                }
            } else {
                console.warn("Tidak ada data real-time yang diterima atau objek real-time null/undefined.");
                suhuValueEl.textContent = '-';
                suhuStatusEl.textContent = '-';
                suhuStatusEl.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor('')}`;
                phValueEl.textContent = '-';
                phStatusEl.textContent = '-';
                phStatusEl.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor('')}`;
                tdsValueEl.textContent = '-';
                tdsStatusEl.textContent = '-';
                tdsStatusEl.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor('')}`;
                fuzzyEl.textContent = 'Tidak ada rekomendasi';
                relay1Toggle.checked = false;
                relay2Toggle.checked = false;
                timer1OnInput.value = '';
                timer1OffInput.value = '';
                timer2OnInput.value = '';
                timer2OffInput.value = '';
            }

            // Perbarui Grafik Riwayat
            const history = data.history;
            if (history && history.length > 0) {
                createOrUpdateChart(history, currentFilter);
            } else {
                console.warn("Tidak ada data histori yang diterima atau array histori kosong.");
                if (historyChartInstance) {
                    historyChartInstance.destroy();
                    historyChartInstance = null;
                }
                chartLoadingMessage.textContent = 'Tidak ada data untuk grafik.';
                chartLoadingMessage.classList.remove('hidden');
            }
        }

        /**
         * Creates or updates the Chart.js graph with sensor history data.
         * @param {Array<Object>} historyData - Array of historical sensor data.
         * @param {string} timeframe - The current selected timeframe (e.g., '1hour', '1day').
         */
        function createOrUpdateChart(historyData, timeframe) {
            console.log("Raw historyData received by createOrUpdateChart:", historyData);

            // Filter out invalid data points and parse timestamps as UTC then convert to WIB
            const labels = historyData
                .filter(entry => entry && (entry.timestamp || entry.Timestamp)) // Ensure entry and timestamp/Timestamp exist
                .map(entry => {
                    const ts = entry.timestamp || entry.Timestamp; // Use either 'timestamp' or 'Timestamp'
                    const d = dayjs.utc(ts).tz('Asia/Jakarta');
                    if (!d.isValid()) {
                        console.warn(`Invalid timestamp in history data: '${ts}'. This data point will be skipped.`);
                        return null; // Return null for invalid dates
                    }
                    return d.toDate(); // Ensure this is a valid Date object
                }).filter(date => date !== null); // Remove null entries

            // Parse numeric data, handling potential string values and comma decimals, and checking for different casing
            const suhuData = historyData.map(entry => parseFloat(String(entry.suhu ?? entry.Suhu ?? '0').replace(',', '.')));
            const phData = historyData.map(entry => parseFloat(String(entry.ph ?? entry.PH ?? '0').replace(',', '.')));
            const tdsData = historyData.map(entry => parseFloat(String(entry.tds ?? entry.TDS ?? '0').replace(',', '.')));

            console.log("Processed labels for chart:", labels);
            console.log("Processed Suhu Data for chart:", suhuData);
            console.log("Processed pH Data for chart:", phData);
            console.log("Processed TDS Data for chart:", tdsData);

            if (labels.length === 0 || suhuData.every(isNaN) || phData.every(isNaN) || tdsData.every(isNaN)) {
                console.warn("No valid data points or all data values are NaN after processing. Chart will not be rendered.");
                if (historyChartInstance) {
                    historyChartInstance.destroy();
                    historyChartInstance = null;
                }
                chartLoadingMessage.textContent = 'Tidak ada data yang valid untuk grafik.';
                chartLoadingMessage.classList.remove('hidden');
                return;
            }

            // Determine time unit and display format based on timeframe
            let unit, displayFormat;
            switch (timeframe) {
                case '1hour':
                    unit = 'minute';
                    displayFormat = 'HH:mm';
                    break;
                case '1day':
                    unit = 'hour';
                    displayFormat = 'DD/MM HH:mm'; // Changed to include date and time for 1 day
                    break;
                case '1week':
                    unit = 'day';
                    displayFormat = 'ddd, DD/MM';
                    break;
                case 'all':
                default:
                    unit = 'day';
                    displayFormat = 'DD/MM/YYYY';
                    if (historyData.length > 24) { // If more than 24 points, show daily
                        unit = 'day';
                        displayFormat = 'DD/MM/YYYY';
                    } else if (historyData.length > 0) { // If less, show hourly
                        unit = 'hour';
                        displayFormat = 'DD/MM HH:mm';
                    }
                    break;
            }

            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Suhu (°C)',
                        data: suhuData,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        fill: false,
                        pointRadius: 3, // Add point radius
                        pointBackgroundColor: 'rgb(75, 192, 192)', // Add point background color
                        yAxisID: 'ySuhu',
                    },
                    {
                        label: 'pH',
                        data: phData,
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1,
                        fill: false,
                        pointRadius: 3, // Add point radius
                        pointBackgroundColor: 'rgb(255, 99, 132)', // Add point background color
                        yAxisID: 'yPH',
                    },
                    {
                        label: 'TDS (PPM)',
                        data: tdsData,
                        borderColor: 'rgb(54, 162, 235)',
                        tension: 0.1,
                        fill: false,
                        pointRadius: 3, // Add point radius
                        pointBackgroundColor: 'rgb(54, 162, 235)', // Add point background color
                        yAxisID: 'yTDS',
                    }
                ]
            };

            const config = {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { // Add chart title
                            display: true,
                            text: 'Riwayat Sensor Aquascape',
                            color: '#e5e7eb', // gray-200
                            font: {
                                size: 18
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    // Format title (timestamp) to WIB
                                    return dayjs(context[0].parsed.x).tz('Asia/Jakarta').format('DD/MM/YYYY HH:mm:ss [WIB]');
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: unit,
                                displayFormats: {
                                    [unit]: displayFormat
                                },
                                tooltipFormat: 'DD/MM/YYYY HH:mm:ss [WIB]', // Ensure tooltip also uses WIB
                                // Removed parser here as labels are already Date objects
                            },
                            title: {
                                display: true,
                                text: 'Waktu', // Changed text to "Waktu"
                                color: '#e5e7eb' // gray-200
                            },
                            ticks: {
                                color: '#9ca3af', // gray-400
                                maxRotation: 45, // Allow some rotation for readability
                                minRotation: 0   // Prevent labels from being completely horizontal if space is tight
                            },
                            grid: {
                                color: '#374151' // gray-700
                            }
                        },
                        ySuhu: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Suhu (°C)',
                                color: 'rgb(75, 192, 192)'
                            },
                            ticks: {
                                color: '#9ca3af'
                            },
                            grid: {
                                color: '#374151'
                            }
                        },
                        yPH: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'pH',
                                color: 'rgb(255, 99, 132)'
                            },
                            ticks: {
                                color: '#9ca3af'
                            },
                            grid: {
                                drawOnChartArea: false, // only draw grid lines for the first y-axis
                                color: '#374151'
                            }
                        },
                        yTDS: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'TDS (PPM)',
                                color: 'rgb(54, 162, 235)'
                            },
                            ticks: {
                                color: '#9ca3af'
                            },
                            grid: {
                                drawOnChartArea: false, // only draw grid lines for the first y-axis
                                color: '#374151'
                            }
                        }
                    }
                }
            };

            if (historyChartInstance) {
                historyChartInstance.destroy();
            }
            historyChartInstance = new Chart(historyChartCanvas, config);
            chartLoadingMessage.classList.add('hidden'); // Hide loading message once chart is drawn
        }
        
        
        // --- Event Listeners ---
        relay1Toggle.addEventListener('change', async function() {
            await sendCommand({ relay1: this.checked ? 'on' : 'off' });
        });

        relay2Toggle.addEventListener('change', async function() {
            await sendCommand({ relay2: this.checked ? 'on' : 'off' });
        });

        saveTimersBtn.addEventListener('click', async function() {
            // Ketika menyimpan, pastikan waktu dikirim dalam format ISO string dengan zona waktu yang benar (misalnya, WIB atau UTC jika backend mengharapkan UTC)
            // Untuk konsistensi dengan mock API yang menggunakan Z (UTC), kita akan mengirim dalam UTC
            const timer1On = timer1OnInput.value ? dayjs().tz('Asia/Jakarta').hour(timer1OnInput.value.split(':')[0]).minute(timer1OnInput.value.split(':')[1]).utc().format() : null;
            const timer1Off = timer1OffInput.value ? dayjs().tz('Asia/Jakarta').hour(timer1OffInput.value.split(':')[0]).minute(timer1OffInput.value.split(':')[1]).utc().format() : null;
            const timer2On = timer2OnInput.value ? dayjs().tz('Asia/Jakarta').hour(timer2OnInput.value.split(':')[0]).minute(timer2OnInput.value.split(':')[1]).utc().format() : null;
            const timer2Off = timer2OffInput.value ? dayjs().tz('Asia/Jakarta').hour(timer2OffInput.value.split(':')[0]).minute(timer2OffInput.value.split(':')[1]).utc().format() : null;

            await sendCommand({
                timer1On: timer1On,
                timer1Off: timer1Off,
                timer2On: timer2On,
                timer2Off: timer2Off
            });
        });

        filterButtons.forEach(button => {
            button.addEventListener('click', async function() {
                currentFilter = this.dataset.timeframe;
                setActiveFilterButton(currentFilter);
                await fetchData(true); // Force fetch new data for the selected timeframe
            });
        });
        

        // Initial fetch and set active button
        setActiveFilterButton(currentFilter); // Set the default active button
        fetchData(true); // Initial fetch when page loads
        fetchAndRenderTables(); // tabel fuzzy

        // Prefetch other data ranges in the background
        prefetchData('1day');
        prefetchData('1week');
        prefetchData('all');

        // Set up refresh interval
        if (refreshIntervalId) {
            clearInterval(refreshIntervalId);
        }
        refreshIntervalId = setInterval(() => fetchData(true), 15000); // Refresh every 15 seconds
    });

   
  

    </script>
</body>
</html>
