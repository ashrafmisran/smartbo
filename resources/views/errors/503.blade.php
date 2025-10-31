<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Penyelenggaraan</title>
    <style>
        :root { --primary: #16a34a; --text: #111827; --muted: #6b7280; --bg: #f9fafb; }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body { display: flex; align-items: center; justify-content: center; background: var(--bg); color: var(--text); font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, Noto Sans, "Apple Color Emoji", "Segoe UI Emoji"; }
        .card { background: #fff; width: 100%; max-width: 720px; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 20px rgba(0,0,0,.06); text-align: center; }
        .brand { display: inline-flex; align-items: center; gap: .5rem; font-weight: 700; color: var(--primary); letter-spacing: .2px; }
        h1 { margin: 0.5rem 0 0.25rem; font-size: clamp(1.5rem, 1.1rem + 2vw, 2rem); }
        p { margin: .25rem 0 .75rem; color: var(--muted); }
        .actions { margin-top: 1rem; display: flex; gap: .5rem; justify-content: center; flex-wrap: wrap; }
        .btn { appearance: none; border: none; cursor: pointer; padding: .65rem 1rem; border-radius: .65rem; font-weight: 600; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-outline { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .small { font-size: .875rem; color: var(--muted); margin-top: 1rem; }
        footer { margin-top: 1.25rem; font-size: .8rem; color: var(--muted); }
        .divider { height: 1px; background: #eef2f7; margin: 1rem 0; border-radius: 9999px; }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="title">
        <div class="brand" aria-label="Jenama">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 1v4"/><path d="M12 19v4"/><path d="M4.22 4.22l2.83 2.83"/><path d="M16.95 16.95l2.83 2.83"/><path d="M1 12h4"/><path d="M19 12h4"/><path d="M4.22 19.78l2.83-2.83"/><path d="M16.95 7.05l2.83-2.83"/>
                <circle cx="12" cy="12" r="5"/></svg>
            <span>{{ config('app.name') }}</span>
        </div>

        <h1 id="title">Penyelenggaraan Sedang Dijalankan</h1>
        <p>Untuk meningkatkan prestasi dan kebolehpercayaan, sistem kami sedang melalui proses penyelenggaraan berjadual.</p>
        <p>Sila cuba semula dalam sedikit masa. Terima kasih atas kesabaran anda.</p>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.location.reload()">Muat Semula Halaman</button>
            <a class="btn btn-outline" href="mailto:postmaster@{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'domain' }}">Hubungi Pentadbir</a>
        </div>

        <div class="divider" aria-hidden="true"></div>
        <div class="small">
            Jika anda melihat mesej ini terlalu lama, kosongkan cache pelayar anda dan cuba semula.
        </div>

        <footer>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak cipta terpelihara.</footer>
    </main>
</body>
</html>
