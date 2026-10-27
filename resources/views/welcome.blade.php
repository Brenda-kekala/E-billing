<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'E-Billing') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <span class="text-lg font-bold">E-Billing</span>
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-800 text-white rounded text-sm">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-800 text-white rounded text-sm">Register</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="text-3xl sm:text-5xl font-bold leading-tight">
                Tax payments made simple for<br class="hidden sm:block"> non-salaried earners
            </h1>
            <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
                Record your irregular income, see the Turnover Tax you owe, and stay on top of
                deadlines and payments, all in one place.
            </p>
            <div class="mt-8 flex justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-800 text-white rounded">Open your dashboard</a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-800 text-white rounded">Get started</a>
                    @endif
                    <a href="{{ route('login') }}" class="px-6 py-3 border border-gray-300 rounded">Log in</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-2xl font-bold text-center mb-10">What you can do</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold text-lg mb-2">Record irregular income</h3>
                <p class="text-gray-600">
                    Add what you earn whenever you earn it, whether daily sales, weekly jobs or
                    one-off payments. No fixed monthly salary needed.
                </p>
            </div>
            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold text-lg mb-2">Calculate Turnover Tax</h3>
                <p class="text-gray-600">
                    See your monthly tax estimate automatically, worked out from the income you
                    have recorded, with no tax knowledge required.
                </p>
            </div>
            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold text-lg mb-2">Track your compliance</h3>
                <p class="text-gray-600">
                    Watch deadlines, balances and payment history in one dashboard, and see at a
                    glance when something is overdue.
                </p>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h2 class="text-2xl font-bold text-center mb-10">How it works</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div>
                    <div class="mx-auto w-10 h-10 rounded-full bg-gray-800 text-white flex items-center justify-center font-bold">1</div>
                    <h3 class="font-semibold mt-3">Create an account</h3>
                    <p class="text-gray-600 mt-1">Register in under a minute.</p>
                </div>
                <div>
                    <div class="mx-auto w-10 h-10 rounded-full bg-gray-800 text-white flex items-center justify-center font-bold">2</div>
                    <h3 class="font-semibold mt-3">Add your income</h3>
                    <p class="text-gray-600 mt-1">Enter amounts and dates as you earn.</p>
                </div>
                <div>
                    <div class="mx-auto w-10 h-10 rounded-full bg-gray-800 text-white flex items-center justify-center font-bold">3</div>
                    <h3 class="font-semibold mt-3">Know what to pay</h3>
                    <p class="text-gray-600 mt-1">Check your tax due, pay by the deadline and record it.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-sm text-gray-500">
        <p>Estimates are based on the income you record. Rates and thresholds can change with each Finance Act, so confirm with KRA.</p>
        <p class="mt-2">&copy; {{ date('Y') }} E-Billing</p>
    </footer>

</body>
</html>