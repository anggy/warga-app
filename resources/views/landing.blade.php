<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $app_name }} - Modern Housing System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        .leaflet-container {
            width: 100%;
            height: 100%;
            z-index: 1;
        }
    </style>
    <script>
        window.housingConfig = {
            center: [{{ $latitude }}, {{ $longitude }}],
            zoom: {{ $zoom }},
            houses: @json($houses)
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 text-slate-800 font-sans">
    
    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex-shrink-0 flex items-center">
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-violet-600">
                        {{ $app_name }}
                    </span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Beranda</a>
                    <a href="#financial" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Laporan Keuangan</a>
                    <a href="#features" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Fitur</a>
                    <a href="#map-section" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Peta Area</a>
                    <a href="/api/documentation" target="_blank" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">API Docs</a>
                    <a href="#contact" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Kontak</a>
                </div>
                <div>
                    <a href="/warga" class="px-6 py-2.5 rounded-full bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-0.5">
                        Login Warga
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="relative pt-32 pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-white -z-10"></div>
        <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-violet-100/50 to-transparent -z-10 blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-6 text-slate-900">
                Hunian Nyaman <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Hidup Lebih Aman</span>
            </h1>
            <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto leading-relaxed">
                Sistem manajemen perumahan terintegrasi untuk keamanan, kenyamanan, dan kerukunan warga. Pantau iuran, keamanan, dan informasi warga dalam satu genggaman.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#map-section" class="px-8 py-4 rounded-full bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.806-.98l-6-2.62M15 7V4m0 0L9 7"></path></svg>
                    Lihat Peta Digital
                </a>
                <a href="#financial" class="px-8 py-4 rounded-full bg-white text-gray-700 border border-gray-200 font-semibold hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    Laporan Keuangan
                </a>
            </div>
        </div>
    </section>

    <!-- Financial Summary Section -->
    <section id="financial" class="py-20 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900">Transparansi Keuangan</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Laporan pemasukan dan pengeluaran dana IPL warga secara real-time dan transparan.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 text-center">
                <!-- Income Card -->
                <div class="p-8 rounded-2xl bg-gradient-to-b from-green-50 to-white border border-green-100 shadow-sm hover:shadow-lg transition-all">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4 text-green-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                    </div>
                    <h3 class="text-gray-500 font-medium mb-2">Total Pemasukan IPL</h3>
                    <p class="text-3xl font-bold text-slate-900">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>

                <!-- Expense Card -->
                <div class="p-8 rounded-2xl bg-gradient-to-b from-red-50 to-white border border-red-100 shadow-sm hover:shadow-lg transition-all">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4 text-red-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                    </div>
                    <h3 class="text-gray-500 font-medium mb-2">Total Pengeluaran</h3>
                    <p class="text-3xl font-bold text-slate-900">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                </div>

                <!-- Balance Card -->
                <div class="p-8 rounded-2xl bg-gradient-to-b from-blue-50 to-white border border-blue-100 shadow-sm hover:shadow-lg transition-all">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4 text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-gray-500 font-medium mb-2">Saldo Saat Ini</h3>
                    <p class="text-3xl font-bold text-slate-900">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section (The Core Request) -->
    <section id="map-section" class="py-20 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900">Denah Digital Area Perumahan</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Eksplorasi layout perumahan, cek status hunian, fasilitas umum, dan rute keamanan secara real-time melalui peta interaktif.
                </p>
            </div>

            <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white h-[600px] w-full bg-gray-100" style="height: 600px;">
                <!-- Map Container -->
                <div id="housing-map" class="w-full h-full"></div>
                
                <!-- Map Overlay Legend (Optional) -->
                <div class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-sm p-4 rounded-2xl shadow-lg z-[1000] border border-gray-100 max-w-xs">
                    <h4 class="font-semibold mb-2 text-sm text-gray-800">Keterangan</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-gray-600">Terbangun</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                            <span class="text-gray-600">Kavling</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-gray-600">Fasilitas Umum</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900">Fitur Unggulan</h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                    <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center mb-6 group-hover:bg-indigo-600 transition-colors">
                        <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-800">Sistem Keamanan</h3>
                    <p class="text-gray-600 leading-relaxed">Panic button terintegrasi dengan pos satpam dan monitoring tamu secara digital.</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                    <div class="w-14 h-14 rounded-xl bg-violet-50 flex items-center justify-center mb-6 group-hover:bg-violet-600 transition-colors">
                        <svg class="w-7 h-7 text-violet-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-800">Transparansi Keuangan</h3>
                    <p class="text-gray-600 leading-relaxed">Pantau pembayaran IPL dan kas warga secara transparan dengan laporan real-time.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                    <div class="w-14 h-14 rounded-xl bg-pink-50 flex items-center justify-center mb-6 group-hover:bg-pink-600 transition-colors">
                        <svg class="w-7 h-7 text-pink-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-800">Data Warga</h3>
                    <p class="text-gray-600 leading-relaxed">Database warga digital yang aman untuk memudahkan administrasi dan sensus.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-violet-400">
                        {{ $app_name }}
                    </span>
                    <p class="mt-2 text-slate-400 text-sm">© {{ date('Y') }} PT Warga Digital Indonesia.<br>All rights reserved.</p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-slate-400 hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="text-slate-400 hover:text-white transition-colors">Terms</a>
                     <a href="/api/documentation" target="_blank" class="text-slate-400 hover:text-white transition-colors">API Docs</a>
                    <a href="#" class="text-slate-400 hover:text-white transition-colors">Twitter</a>
                    <a href="#" class="text-slate-400 hover:text-white transition-colors">Instagram</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
