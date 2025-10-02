<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Checkout')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div id="app">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <h1 class="text-2xl font-bold text-gray-900">Maria Checkout</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="min-h-screen py-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-auto">
            <div class="max-w-7xl mx-auto px-4 py-6 text-center text-gray-600 text-sm">
                <p>&copy; {{ date('Y') }} Maria Checkout. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
