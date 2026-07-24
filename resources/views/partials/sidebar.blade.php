@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
        ['label' => 'Sales Orders', 'route' => 'sales-orders.index', 'icon' => 'doc'],
        ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'users'],
        ['label' => 'Support System', 'route' => 'support.index', 'icon' => 'support'],
        ['label' => 'Reports', 'route' => 'reports.sales', 'match' => 'reports.*', 'icon' => 'chart'],
        ['label' => 'MCM', 'route' => 'mcm.index', 'icon' => 'grid-alt'],
    ];
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-navy text-white flex flex-col transition-transform duration-300 ease-in-out"
>
    <div class="px-6 py-6 text-lg font-bold leading-snug border-b border-white/10">
        Sales &amp; Costumers<br>Management
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="group flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 ease-out
               {{ request()->routeIs($item['match'] ?? $item['route']) ? 'bg-brand text-white shadow-lg shadow-brand/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <span class="w-5 h-5 shrink-0">@include('partials.icons.'.$item['icon'])</span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <!-- Exit pinned to the bottom of the sidebar, separate from the main nav list -->
    <div class="px-3 py-4 border-t border-white/10">
        <a href="{{ route('exit.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-200 ease-out">
            <span class="w-5 h-5 shrink-0">@include('partials.icons.exit')</span>
            Exit
        </a>
    </div>
</aside>
