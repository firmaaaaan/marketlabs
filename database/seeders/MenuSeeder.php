<?php

namespace Database\Seeders;

use App\Models\LandingPageSection;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Sidebar Menu - Default items
        $sidebarItems = [
            ['label' => 'Dashboard', 'route_name' => 'admin.dashboard', 'sort_order' => 0, 'is_active' => true],
            ['label' => 'Notifikasi', 'route_name' => 'admin.notifications.all', 'sort_order' => 1, 'is_active' => true],
            ['label' => 'Kelola Alat', 'route_name' => 'admin.tools.index', 'sort_order' => 2, 'is_active' => true],
            ['label' => 'Kelola Kategori', 'route_name' => 'admin.categories.index', 'sort_order' => 3, 'is_active' => true],
            ['label' => 'Satuan & Parameter', 'route_name' => 'admin.sample-units.index', 'sort_order' => 4, 'is_active' => true],
            ['label' => 'Bentuk & Jenis Sampel', 'route_name' => 'admin.sample-attributes.index', 'sort_order' => 5, 'is_active' => true],
            ['label' => 'Kelola Laboratorium', 'route_name' => 'admin.laboratoriums.index', 'sort_order' => 6, 'is_active' => true],
            ['label' => 'Kelola User', 'route_name' => 'admin.users.index', 'sort_order' => 7, 'is_active' => true, 'min_role' => 'superadmin'],
            ['label' => 'Kelola Peminjaman', 'route_name' => 'admin.borrowings.index', 'sort_order' => 8, 'is_active' => true],
            ['label' => 'Permohonan Riset', 'route_name' => 'admin.research.index', 'sort_order' => 9, 'is_active' => true],
            ['label' => 'Pengujian Sampel', 'route_name' => 'admin.sample-tests.index', 'sort_order' => 10, 'is_active' => true],
            ['label' => 'Pemeriksaan Kesehatan', 'route_name' => 'admin.health-checkups.index', 'sort_order' => 11, 'is_active' => true],
            ['label' => 'Tarif Pemeriksaan', 'route_name' => 'admin.health-checkup-types.index', 'sort_order' => 12, 'is_active' => true],
            ['label' => 'Jadwal Layanan', 'route_name' => 'admin.schedule.index', 'sort_order' => 13, 'is_active' => true],
            ['label' => 'Kalender', 'route_name' => 'admin.calendar.index', 'sort_order' => 14, 'is_active' => true],
            ['label' => 'Kelola Event', 'route_name' => 'admin.events.index', 'sort_order' => 15, 'is_active' => true],
            ['label' => 'Tarif Bench Fee', 'route_name' => 'admin.bench-fee.index', 'sort_order' => 16, 'is_active' => true],
            ['label' => 'Pengaturan Invoice', 'route_name' => 'admin.invoice.index', 'sort_order' => 17, 'is_active' => true],
            ['label' => 'Pengaturan WhatsApp', 'route_name' => 'admin.whatsapp.index', 'sort_order' => 18, 'is_active' => true],
            ['label' => 'Pengaturan Footer', 'route_name' => 'admin.footer.index', 'sort_order' => 19, 'is_active' => true, 'min_role' => 'superadmin'],
            ['label' => 'Kelola Menu', 'route_name' => 'admin.menus.index', 'sort_order' => 20, 'is_active' => true, 'min_role' => 'superadmin'],
            ['label' => 'Kelola Testimoni', 'route_name' => 'admin.testimonials.index', 'sort_order' => 21, 'is_active' => true],
            ['label' => 'Kelola FAQ', 'route_name' => 'admin.faqs.index', 'sort_order' => 22, 'is_active' => true],
            ['label' => 'Kelola Mitra', 'route_name' => 'admin.mitras.index', 'sort_order' => 23, 'is_active' => true],
            ['label' => 'Log Aktivitas', 'route_name' => 'admin.activity-logs.index', 'sort_order' => 24, 'is_active' => true, 'min_role' => 'superadmin'],
            ['label' => 'Download Dokumen', 'route_name' => 'admin.document-downloads.index', 'sort_order' => 25, 'is_active' => true],
            ['label' => 'Backup & Restore', 'route_name' => 'admin.backup.index', 'sort_order' => 26, 'is_active' => true, 'min_role' => 'superadmin'],
        ];

        foreach ($sidebarItems as $item) {
            MenuItem::updateOrCreate(
                ['route_name' => $item['route_name'], 'group' => 'sidebar'],
                $item + ['group' => 'sidebar']
            );
        }

        // Topbar Menu - Default items
        $topbarItems = [
            ['label' => 'Beranda', 'route_name' => 'home', 'sort_order' => 0, 'is_active' => true],
            ['label' => 'Alat', 'route_name' => 'tools.index', 'sort_order' => 1, 'is_active' => true],
            ['label' => 'Pengujian', 'route_name' => 'sample-tests.catalog', 'sort_order' => 2, 'is_active' => true],
            ['label' => 'Pemeriksaan', 'route_name' => 'health-checkups.catalog', 'sort_order' => 3, 'is_active' => true],
            ['label' => 'Jadwal Lab', 'route_name' => 'lab-schedule', 'sort_order' => 4, 'is_active' => true],
            ['label' => 'Event', 'route_name' => 'events.index', 'sort_order' => 5, 'is_active' => true],
        ];

        foreach ($topbarItems as $item) {
            MenuItem::updateOrCreate(
                ['route_name' => $item['route_name'], 'group' => 'topbar'],
                $item + ['group' => 'topbar']
            );
        }

        // Landing Page Sections
        $sections = [
            ['key' => 'hero', 'title' => 'Hero Section', 'description' => 'Bagian utama landing page dengan judul dan CTA', 'sort_order' => 0, 'is_active' => true],
            ['key' => 'features', 'title' => 'Fitur Utama', 'description' => 'Daftar fitur dan kemampuan platform', 'sort_order' => 1, 'is_active' => true],
            ['key' => 'steps', 'title' => 'Cara Kerja', 'description' => 'Langkah-langkah menggunakan platform', 'sort_order' => 2, 'is_active' => true],
            ['key' => 'tools', 'title' => 'Alat Tersedia', 'description' => 'Katalog alat laboratorium yang bisa dipinjam', 'sort_order' => 3, 'is_active' => true],
            ['key' => 'parameters', 'title' => 'Layanan Pengujian', 'description' => 'Parameter pengujian sampel yang tersedia', 'sort_order' => 4, 'is_active' => true],
            ['key' => 'health', 'title' => 'Pemeriksaan Kesehatan', 'description' => 'Jenis pemeriksaan kesehatan yang tersedia', 'sort_order' => 5, 'is_active' => true],
            ['key' => 'testimonials', 'title' => 'Testimoni', 'description' => 'Kesan dan pesan pengguna platform', 'sort_order' => 6, 'is_active' => true],
            ['key' => 'faq', 'title' => 'FAQ', 'description' => 'Pertanyaan yang sering diajukan', 'sort_order' => 7, 'is_active' => true],
        ];

        foreach ($sections as $section) {
            LandingPageSection::updateOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}
