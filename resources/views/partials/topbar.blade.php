<header class="sticky top-0 z-20 bg-white border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
        <h1 class="text-xl font-bold text-slate-800">{{ $pageTitle ?? 'Dashboard' }}</h1>
    </div>

    <div class="hidden md:flex flex-1 max-w-sm">
        <div class="relative w-full">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text" placeholder="Search..."
                   class="w-full pl-9 pr-3 py-2 rounded-lg bg-slate-100 border border-transparent focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/20 outline-none text-sm transition-all">
        </div>
    </div>

    <div class="flex items-center gap-4">

        <!-- Notifications: real, live data pulled from Sales Orders, Support System, and MCM -->
        <div x-data="{
                open: false,
                loading: true,
                count: 0,
                items: [],
                colorClasses(c) {
                    return {
                        amber: 'bg-amber-100 text-amber-600',
                        rose: 'bg-rose-100 text-rose-600',
                        blue: 'bg-blue-100 text-blue-600',
                    }[c] ?? 'bg-slate-100 text-slate-600';
                },
                load() {
                    this.loading = true;
                    fetch('{{ route('notifications.index') }}')
                        .then(res => res.json())
                        .then(data => { this.count = data.count; this.items = data.items; this.loading = false; })
                        .catch(() => { this.loading = false; });
                }
             }"
             x-init="load()"
             @click.outside="open = false"
             class="relative">
            <button @click="open = !open" class="relative text-slate-500 hover:text-slate-700 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span x-show="count > 0" x-cloak class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
            </button>

            <div x-show="open" x-cloak x-transition
                 class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-lg border border-slate-100 z-30">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <span class="font-semibold text-slate-800 text-sm">Notifications</span>
                    <span class="text-xs text-slate-400" x-text="count + ' new'"></span>
                </div>
                <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                    <template x-if="loading">
                        <div class="px-4 py-6 text-center text-sm text-slate-400">Loading...</div>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <div class="px-4 py-6 text-center text-sm text-slate-400">You're all caught up.</div>
                    </template>
                    <template x-for="item in items" :key="item.title">
                        <a :href="item.link" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs" :class="colorClasses(item.color)">
                                <i :class="'fa-solid ' + item.icon"></i>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-slate-700 truncate" x-text="item.title"></span>
                                <span class="block text-xs text-slate-400 truncate" x-text="item.subtitle"></span>
                                <span class="block text-xs text-slate-300" x-text="item.time"></span>
                            </span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button @click="open = !open" class="text-slate-500 hover:text-slate-700 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </button>

            <div x-show="open" x-cloak x-transition
                 class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-30 text-sm">
                <div class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase">Quick Access</div>
                <a href="{{ route('support.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-headset w-4 text-slate-400"></i> Help &amp; Support
                </a>
                <a href="{{ route('reports.sales') }}" class="flex items-center gap-2 px-4 py-2 text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-chart-line w-4 text-slate-400"></i> View Reports
                </a>
                <a href="{{ route('customers.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-users w-4 text-slate-400"></i> Manage Customers
                </a>
                <hr class="my-2 border-slate-100">
                <a href="{{ route('exit.index') }}" class="flex items-center gap-2 px-4 py-2 text-rose-500 hover:bg-rose-50">
                    <i class="fa-solid fa-right-from-bracket w-4"></i> Exit
                </a>
            </div>
        </div>

        <!-- Profile -->
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button @click="open = !open" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-slate-300 flex items-center justify-center text-slate-600 font-semibold text-sm">A</div>
                <div class="hidden sm:block leading-tight text-left">
                    <div class="text-sm font-semibold text-slate-800">Admin User</div>
                    <div class="text-xs text-slate-400">Manager</div>
                </div>
            </button>

            <div x-show="open" x-cloak x-transition
                 class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-30 text-sm">
                <div class="px-4 py-3 border-b border-slate-100">
                    <div class="font-semibold text-slate-800">Admin User</div>
                    <div class="text-xs text-slate-400">Manager · Sales & Customers Management</div>
                </div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-slate-600 hover:bg-slate-50 mt-1">
                    <i class="fa-solid fa-gauge w-4 text-slate-400"></i> Dashboard
                </a>
                <a href="{{ route('sales-orders.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-file-invoice w-4 text-slate-400"></i> My Sales Orders
                </a>
            </div>
        </div>
    </div>
</header>
