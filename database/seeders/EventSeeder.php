<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class EventSeeder extends Seeder
{
    /**
     * Seed contoh event dengan kategori (online/offline/hybrid), gambar,
     * serta form registrasi & presensi, siap diisi.
     */
    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $this->seedEvent([
            'slug' => 'workshop-pengujian-kimia-2026',
            'code' => 'EVT-2026-001',
            'title' => 'Workshop Pengujian Kimia 2026',
            'description' => "Workshop praktik pengujian sampel kimia untuk peneliti dan mahasiswa.\n\nMateri meliputi preparasi sampel, penggunaan instrumen, interpretasi hasil, hingga penulisan laporan pengujian. Peserta yang hadir akan mendapatkan sertifikat digital resmi dari MarketLabs.",
            'location' => 'Gedung Laboratorium Terpadu, Ruang Seminar',
            'mode' => Event::MODE_OFFLINE,
            'starts_at' => now()->addDays(14)->setTime(9, 0),
            'ends_at' => now()->addDays(14)->setTime(15, 0),
            'quota' => 50,
            'fee' => 150000,
            'discount' => 50000,
            'registration_deadline' => now()->addDays(10),
            'form_fields' => [
                ['key' => 'nim', 'label' => 'NIM / NIP', 'type' => 'text', 'required' => true],
                ['key' => 'institution', 'label' => 'Instansi', 'type' => 'text', 'required' => true],
                ['key' => 'tshirt', 'label' => 'Ukuran Kaos', 'type' => 'select', 'options' => ['S', 'M', 'L', 'XL'], 'required' => false],
                ['key' => 'notes', 'label' => 'Kebutuhan Khusus', 'type' => 'textarea', 'required' => false],
            ],
            'attendance_fields' => [
                ['key' => 'signature_ready', 'label' => 'Bersedia Tanda Tangan Presensi', 'type' => 'checkbox', 'required' => true],
                ['key' => 'feedback', 'label' => 'Kesan Singkat', 'type' => 'textarea', 'required' => false],
            ],
        ], '#047857', '#065f46');

        $this->seedEvent([
            'slug' => 'seminar-digitalisasi-laboratorium-2026',
            'code' => 'EVT-2026-002',
            'title' => 'Seminar Digitalisasi Laboratorium 2026',
            'description' => "Seminar online tentang transformasi digital laboratorium: sistem informasi manajemen lab, otomasi pencatatan, dan layanan daring.\n\nDiselenggarakan melalui platform Zoom. Link akses akan dikirimkan ke email peserta setelah registrasi.",
            'location' => 'Zoom Meeting (online)',
            'mode' => Event::MODE_ONLINE,
            'starts_at' => now()->addDays(21)->setTime(10, 0),
            'ends_at' => now()->addDays(21)->setTime(12, 30),
            'quota' => 500,
            'fee' => 75000,
            'discount' => 25000,
            'registration_deadline' => now()->addDays(18),
            'form_fields' => [
                ['key' => 'institution', 'label' => 'Instansi', 'type' => 'text', 'required' => true],
                ['key' => 'sector', 'label' => 'Bidang', 'type' => 'select', 'options' => ['Pendidikan', 'Industri', 'Pemerintahan', 'Lainnya'], 'required' => false],
                ['key' => 'expectation', 'label' => 'Ekspektasi Materi', 'type' => 'textarea', 'required' => false],
            ],
            'attendance_fields' => [
                ['key' => 'joined_online', 'label' => 'Saya telah bergabung secara daring', 'type' => 'checkbox', 'required' => true],
                ['key' => 'feedback', 'label' => 'Kesan Singkat', 'type' => 'textarea', 'required' => false],
            ],
        ], '#0369a1', '#075985');

        $this->seedEvent([
            'slug' => 'pelatihan-analisis-data-penelitian-hybrid-2026',
            'code' => 'EVT-2026-003',
            'title' => 'Pelatihan Analisis Data Penelitian (Hybrid) 2026',
            'description' => "Pelatihan analisis data statistik untuk penelitian menggunakan Python & SPSS.\n\nFormat hybrid: peserta dapat mengikuti secara langsung di laboratorium komputer atau daring melalui Zoom. Sesi praktik disertai pendampingan langsung oleh fasilitator.",
            'location' => 'Lab Komputer 2 & Zoom Meeting',
            'mode' => Event::MODE_HYBRID,
            'starts_at' => now()->addDays(30)->setTime(8, 30),
            'ends_at' => now()->addDays(30)->setTime(16, 0),
            'quota' => 80,
            'fee' => 200000,
            'registration_deadline' => now()->addDays(26),
            'form_fields' => [
                ['key' => 'nim', 'label' => 'NIM / NIP', 'type' => 'text', 'required' => true],
                ['key' => 'institution', 'label' => 'Instansi', 'type' => 'text', 'required' => true],
                ['key' => 'join_mode', 'label' => 'Cara Mengikuti', 'type' => 'radio', 'options' => ['Offline', 'Online'], 'required' => true],
                ['key' => 'software', 'label' => 'Software Pilihan', 'type' => 'select', 'options' => ['Python', 'SPSS', 'Keduanya'], 'required' => false],
                ['key' => 'skill_level', 'label' => 'Tingkat Kemampuan', 'type' => 'select', 'options' => ['Pemula', 'Menengah', 'Mahir'], 'required' => false],
            ],
            'attendance_fields' => [
                ['key' => 'confirmation', 'label' => 'Konfirmasi Kehadiran', 'type' => 'radio', 'options' => ['Hadir Langsung', 'Hadir Online'], 'required' => true],
                ['key' => 'feedback', 'label' => 'Kesan Singkat', 'type' => 'textarea', 'required' => false],
            ],
        ], '#7c3aed', '#5b21b6');
    }

    protected function seedEvent(array $data, string $color1, string $color2): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $image = $this->generatePlaceholder($data['slug'], $data['title'], $color1, $color2, 1280, 720, 'thumb');
        $poster = $this->generatePlaceholder($data['slug'], $data['title'], $color1, $color2, 800, 1131, 'poster');

        Event::updateOrCreate(
            ['slug' => $data['slug']],
            array_merge($data, ['image' => $image, 'poster' => $poster, 'status' => Event::STATUS_ACTIVE, 'created_by' => $admin?->id])
        );
    }

    /**
     * Buat gambar placeholder PNG dengan gradasi dua warna dan judul event,
     * disimpan di disk public (storage/app/public/events).
     */
    protected function generatePlaceholder(string $slug, string $title, string $color1, string $color2, int $width, int $height, string $suffix): string
    {
        $image = imagecreatetruecolor($width, $height);

        [$r1, $g1, $b1] = $this->hexToRgb($color1);
        [$r2, $g2, $b2] = $this->hexToRgb($color2);

        for ($y = 0; $y < $height; $y++) {
            $t = $y / $height;
            $color = imagecolorallocate(
                $image,
                (int) round($r1 + ($r2 - $r1) * $t),
                (int) round($g1 + ($g2 - $g1) * $t),
                (int) round($b1 + ($b2 - $b1) * $t)
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        $font = public_path('fonts/Lato-Bold.ttf');
        $textColor = imagecolorallocate($image, 255, 255, 255);

        $title = mb_strtoupper($title);
        $size = max(28, min(72, (int) round($width * 56 / 1280)));
        $bbox = imagettfbbox($size, 0, $font, $title);
        $tw = abs($bbox[2] - $bbox[0]);
        $th = abs($bbox[5] - $bbox[1]);

        // Jika judul lebih lebar dari gambar, turunkan ukuran font.
        if ($tw > $width - 80) {
            $size = (int) floor($size * ($width - 80) / $tw);
            $bbox = imagettfbbox($size, 0, $font, $title);
            $tw = abs($bbox[2] - $bbox[0]);
            $th = abs($bbox[5] - $bbox[1]);
        }

        $titleX = (int) (($width - $tw) / 2);
        $titleY = (int) (($height - $th) / 2 + $th - 30);

        imagettftext($image, $size, 0, $titleX, $titleY, $textColor, $font, $title);

        $sub = 'EVENT MARKETLABS';
        $subSize = max(16, (int) round($width * 26 / 1280));
        $subBox = imagettfbbox($subSize, 0, $font, $sub);
        $subW = abs($subBox[2] - $subBox[0]);
        imagettftext($image, $subSize, 0, (int) (($width - $subW) / 2), $titleY + 70, $textColor, $font, $sub);

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        $relative = 'events/'.$slug.'-'.$suffix.'.png';
        Storage::disk('public')->put($relative, $png);

        return $relative;
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return array_map('hexdec', str_split($hex, 2));
    }
}
