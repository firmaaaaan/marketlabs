<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\HealthTestType;
use App\Models\TestParameter;
use App\Models\Testimonial;
use App\Models\Tool;
use App\Models\ToolCategory;
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
            ->with('category')
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

        return view('landing.index', compact('features', 'steps', 'featuredTools', 'featuredParameters', 'categories', 'testimonials', 'healthTypes', 'faqs'));
    }
}
