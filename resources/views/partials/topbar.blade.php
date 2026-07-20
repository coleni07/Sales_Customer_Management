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
        <button class="relative text-slate-500 hover:text-slate-700 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500"></span>
        </button>
        <button class="text-slate-500 hover:text-slate-700 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </button>
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-full bg-slate-300 flex items-center justify-center text-slate-600 font-semibold text-sm">A</div>
            <div class="hidden sm:block leading-tight">
                <div class="text-sm font-semibold text-slate-800">Admin User</div>
                <div class="text-xs text-slate-400">Manager</div>
            </div>
        </div>
    </div>
</header>
