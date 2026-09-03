<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan - MarketLabs</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
        <div class="text-center">
            <p class="text-8xl font-extrabold text-emerald-600">404</p>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">Halaman Tidak Ditemukan</h1>
            <p class="mt-2 text-slate-600">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}"
                   class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Kembali ke Beranda
                </a>
                <button onclick="history.back()"
                        class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    Halaman Sebelumnya
                </button>
            </div>
        </div>
    </div>
</body>
</html>
