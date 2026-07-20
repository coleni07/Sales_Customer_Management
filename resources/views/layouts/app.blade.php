<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Dashboard' }} — Sales &amp; Customers Management</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { DEFAULT: '#1B2A4A', light: '#22345F', dark: '#141F38' },
                        navyDark: '#141F38',
                        brand: { DEFAULT: '#0FA98E', dark: '#0C8C75' },
                    },
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

    @include('partials.sidebar')

    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 z-30 lg:hidden" x-cloak></div>

    <div class="flex-1 flex flex-col min-w-0">
        @unless($hideTopbar ?? false)
            @include('partials.topbar')
        @else
            <!-- Topbar hidden on this page. Mobile-only menu button takes its
                 place so the sidebar can still be opened on small screens. -->
            <button @click="sidebarOpen = true"
                    class="lg:hidden fixed top-4 left-4 z-20 bg-white shadow-md rounded-lg p-2 text-slate-600">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>
        @endunless

        <!-- Page transition wrapper: fades + slides content in on load/navigation -->
        <main class="flex-1 p-4 sm:p-6 page-transition">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
