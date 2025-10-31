<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title')</title>
    <!-- JetBrains Mono font -->
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100"
    style="font-family: 'JetBrains Mono', monospace;">
    <!-- Top Bar -->
    <header class="bg-white dark:bg-gray-800 shadow flex items-center px-6 py-4">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 mr-4">
        </a>
        <h1 class="text-2xl font-bold">{{ config('app.name') }} - {{ config('app.version') }}</h1>
    </header>

    <!-- Menu Bar -->
    <nav class="bg-gray-100 dark:bg-gray-700 flex gap-8 px-6 py-3">
        <a href="{{ route('schedule') }}"
            class="font-medium hover:text-blue-600 {{ Route::currentRouteName() === 'schedule' ? 'text-blue-600 underline' : '' }}">Schedule</a>
        <a href="{{ route('setup') }}"
            class="font-medium hover:text-blue-600 {{ Route::currentRouteName() === 'setup' ? 'text-blue-600 underline' : '' }}">Setup</a>
        <a href="{{ route('export') }}"
            class="font-medium hover:text-blue-600 {{ Route::currentRouteName() === 'export' ? 'text-blue-600 underline' : '' }}">Export/Import</a>
    </nav>

    <!-- Content Section -->
    <main class="flex-1 px-6 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 text-center py-4 mt-auto shadow">
        <span class="text-sm">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
        <div class="text-xs text-gray-500 mt-2">This site uses only essential cookies for session management. No
            tracking or analytics cookies are used.</div>
        <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">
            Active sessions: {{ \DB::table('sessions')->distinct('ip_address')->count('ip_address') }}
        </div>
    </footer>
</body>

</html>
