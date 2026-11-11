<div id="cookie-consent" class="fixed inset-x-4 bottom-4 z-[200] hidden max-w-xl rounded-2xl border border-line bg-surface p-4 shadow-2xl sm:left-auto sm:right-6" role="dialog" aria-live="polite" aria-label="Informasi cookie">
    <p class="text-sm font-semibold text-ink">Cookie esensial</p>
    <p class="mt-1 text-xs leading-5 text-muted">Aplikasi menggunakan cookie sesi dan keamanan yang diperlukan untuk login dan perlindungan permintaan. Tidak ada cookie analitik yang diaktifkan oleh paket ini.</p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <button id="cookie-consent-accept" type="button" class="rounded-lg bg-accent px-3 py-2 text-xs font-semibold text-[#07140c]">Mengerti</button>
        <a href="{{ route('privacy') }}" class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-muted">Kebijakan privasi</a>
    </div>
</div>
<script @cspNonce>
(() => {
    const key = 'badig_cookie_notice_ack_v1';
    const banner = document.getElementById('cookie-consent');
    const accept = document.getElementById('cookie-consent-accept');
    if (!banner) return;
    try {
        if (localStorage.getItem(key) !== '1') banner.classList.remove('hidden');
        accept?.addEventListener('click', () => {
            localStorage.setItem(key, '1');
            banner.remove();
        });
    } catch (_) {
        banner.classList.remove('hidden');
        accept?.addEventListener('click', () => banner.remove());
    }
})();
</script>
