<aside class="flex h-full w-72 flex-col border-r border-slate-200 bg-slate-950 text-slate-200 shadow-sm">
    <div class="border-b border-white/10 px-5 py-5">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-sm font-semibold text-white">
                BADIG
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Sistem Informasi</p>
                <p class="text-xs text-slate-400">Sat Intelkam</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4">
        <a href="/dashboard-custom" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
            <span class="material-symbols-outlined text-base">dashboard</span>
            Dashboard
        </a>

        <a href="/archives" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
            <span class="material-symbols-outlined text-base">folder_open</span>
            Arsip
        </a>

        <a href="/file-manager" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
            <span class="material-symbols-outlined text-base">description</span>
            File Manager
        </a>

        @if(auth()->user()->role_id == 1)
            <div class="mt-4 px-3 pt-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
                Admin
            </div>

            <a href="/super-admin/master-file-manager" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                <span class="material-symbols-outlined text-base">folder_special</span>
                Master File Manager
            </a>

            <a href="/super-admin/audit-log" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                <span class="material-symbols-outlined text-base">receipt_long</span>
                Audit Log
            </a>
        @endif
    </nav>
</aside>