<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\HealthTestType;
use App\Models\Mitra;
use App\Models\TestParameter;
use App\Models\Testimonial;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\User;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $features = [
            [
                'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4',
                'title' => 'Peminjaman Alat Laboratorium',
                'description' => 'Ajukan peminjaman alat, pantau status secara real-time, dan kelola pengembalian dengan biaya yang dihitung otomatis per hari pemakaian.',
            ],
            [
                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'title' => 'Permohonan Riset & Penelitian',
                'description' => 'Ajukan permohonan riset secara digital: pilih jenjang, instansi, dan kategori, lengkapi surat pendukung, lalu pantau progres penelitian dengan logbook harian.',
            ],
            [
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'title' => 'Estimasi & Tagihan Transparan',
                'description' => 'Bench fee, biaya sewa alat, biaya laboran, hingga denda — semua dihitung otomatis dan ditampilkan transparan dalam invoice resmi yang bisa diunduh.',
            ],
            [
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'title' => 'Penugasan Laboran & Laboratorium',
                'description' => 'Admin menugaskan laboran dan laboratorium terbaik untuk setiap penelitian, lengkap dengan biaya pendampingan yang tercatat rapi.',
            ],
            [
                'icon' => 'M3 10h18M7 15h2m4 0h4m-9 4h8a2 2 0 002-2V8a2 2 0 00-2-2H9a2 2 0 00-2 2v9z',
                'title' => 'Logbook & Monitoring Progres',
                'description' => 'Catat kegiatan dan kendala penelitian harian melalui logbook digital, dengan riwayat status lengkap beserta waktu di setiap tahapannya.',
            ],
            [
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'title' => 'Laporan & Export Data',
                'description' => 'Export data peminjaman, alat, dan permohonan riset ke Excel dengan filter status dan rentang tanggal untuk mendukung pengambilan keputusan.',
            ],
        ];

        // Alat unggulan untuk section katalog ala e-commerce di landing page.
        $featuredTools = Tool::active()
            ->available()
            ->with(['category', 'images'])
            ->latest()
            ->take(5)
            ->get();

        // Parameter pengujian unggulan untuk section pengujian di landing page.
        $featuredParameters = TestParameter::active()
            ->with('unit')
            ->latest()
            ->take(5)
            ->get();

        // Kategori alat beserta jumlah alat aktif di dalamnya.
        $categories = ToolCategory::withCount(['tools' => function ($query) {
            $query->where('is_active', true);
        }])
            ->get()
            ->filter(fn ($category) => $category->tools_count > 0)
            ->values();

        // Jenis pemeriksaan kesehatan yang aktif.
        $healthTypes = HealthTestType::active()->orderBy('name')->get();

        // Testimoni pengguna dari database (dikelola admin).
        $testimonials = Testimonial::active()->latest()->get();

        // FAQ dari database (dikelola admin).
        $faqs = Faq::active()->ordered()->get();

        // Mitra dari database (dikelola admin).
        $mitras = Mitra::active()->orderBy('sort_order')->get();

        // Gallery images untuk section galeri kegiatan.
        $galleryImages = GalleryImage::active()->orderBy('sort_order')->get();

        $steps = [
            [
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'title' => 'Daftar & Lengkapi Profil',
                'description' => 'Buat akun, lengkapi NIM/NIP dan instansi di halaman profil agar pengajuan lebih cepat.',
            ],
            [
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'title' => 'Ajukan Peminjaman atau Riset',
                'description' => 'Pilih alat atau isi formulir permohonan riset lengkap dengan surat pendukung dan estimasi biaya otomatis.',
            ],
            [
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'title' => 'Disetujui, Dipantau, Selesai',
                'description' => 'Admin menyetujui, menugaskan laboran & laboratorium, lalu pantau progres lewat logbook hingga selesai dan lunas.',
            ],
        ];

        // Statistik dinamis untuk hero section.
        $stats = [
            [
                'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4',
                'value' => Tool::active()->count(),
                'suffix' => '+',
                'label' => 'Alat Laboratorium',
            ],
            [
                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'value' => TestParameter::active()->count(),
                'suffix' => '+',
                'label' => 'Parameter Pengujian',
            ],
            [
                'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
                'value' => User::count(),
                'suffix' => '+',
                'label' => 'Pengguna Aktif',
            ],
            [
                'icon' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
                'value' => round(Testimonial::active()->avg('rating') / 5 * 100),
                'suffix' => '%',
                'label' => 'Tingkat Kepuasan',
            ],
        ];

        return view('landing.index', compact('features', 'steps', 'featuredTools', 'featuredParameters', 'categories', 'testimonials', 'healthTypes', 'faqs', 'stats', 'mitras', 'galleryImages'));
    }
}
