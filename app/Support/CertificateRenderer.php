<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Storage;

class CertificateRenderer
{
    /**
     * Font bawaan (tanpa perlu install/upload) — key → [regular, bold].
     * Font yang hanya punya satu berat akan memakai file yang sama untuk bold.
     */
    public const FONTS = [
        'lato' => ['regular' => 'Lato-Regular.ttf', 'bold' => 'Lato-Bold.ttf'],
        'poppins' => ['regular' => 'Poppins-Regular.ttf', 'bold' => 'Poppins-SemiBold.ttf'],
        'tinos' => ['regular' => 'Tinos-Regular.ttf', 'bold' => 'Tinos-Bold.ttf'],
        'crimson' => ['regular' => 'CrimsonText-Regular.ttf', 'bold' => 'CrimsonText-Bold.ttf'],
        'arvo' => ['regular' => 'Arvo-Regular.ttf', 'bold' => 'Arvo-Bold.ttf'],
        'marcellus' => ['regular' => 'Marcellus-Regular.ttf', 'bold' => 'Marcellus-Regular.ttf'],
        'ibmplexserif' => ['regular' => 'IBMPlexSerif-Regular.ttf', 'bold' => 'IBMPlexSerif-Bold.ttf'],
        'cardo' => ['regular' => 'Cardo-Regular.ttf', 'bold' => 'Cardo-Bold.ttf'],
        'sortsmillgoudy' => ['regular' => 'SortsMillGoudy-Regular.ttf', 'bold' => 'SortsMillGoudy-Regular.ttf'],
        'great_vibes' => ['regular' => 'GreatVibes-Regular.ttf', 'bold' => 'GreatVibes-Regular.ttf'],
        'amatic' => ['regular' => 'AmaticSC-Regular.ttf', 'bold' => 'AmaticSC-Bold.ttf'],
        'pacifico' => ['regular' => 'Pacifico-Regular.ttf', 'bold' => 'Pacifico-Regular.ttf'],
        'lobster' => ['regular' => 'Lobster-Regular.ttf', 'bold' => 'Lobster-Regular.ttf'],
        'pinyon_script' => ['regular' => 'PinyonScript-Regular.ttf', 'bold' => 'PinyonScript-Regular.ttf'],
    ];

    /**
     * Label untuk dropdown font di editor.
     *
     * @return array<string, string>
     */
    public static function fontFamilies(): array
    {
        return [
            'lato' => 'Lato (Sans)',
            'poppins' => 'Poppins (Sans)',
            'tinos' => 'Tinos (Serif Klasik)',
            'crimson' => 'Crimson Text (Serif)',
            'arvo' => 'Arvo (Serif Tebal)',
            'marcellus' => 'Marcellus (Serif Elegan)',
            'ibmplexserif' => 'IBM Plex Serif',
            'cardo' => 'Cardo (Serif)',
            'sortsmillgoudy' => 'Sorts Mill Goudy',
            'great_vibes' => 'Great Vibes (Skrip)',
            'amatic' => 'Amatic SC (Skrip)',
            'pacifico' => 'Pacifico (Skrip)',
            'lobster' => 'Lobster (Skrip)',
            'pinyon_script' => 'Pinyon Script (Kaligrafi)',
        ];
    }

    /**
     * Render sertifikat (depan + belakang) untuk satu pendaftaran.
     *
     * Layout nama: [{ type: 'name', x, y, size, color, align, font, weight, enabled }]
     * x/y dalam persen dari ukuran gambar; size dalam px acuan lebar 1240px.
     * Hanya nama yang bisa disesuaikan; nomor & tanggal digambar otomatis (depan).
     *
     * @return array{front: ?string, back: ?string} path relatif (disk public) PNG per sisi.
     */
    public static function render(Event $event, EventRegistration $registration): array
    {
        $name = $registration->user->name;
        $number = $registration->certificate_number ?? '-';
        $date = $registration->certificate_generated_at?->translatedFormat('d F Y') ?? date('d F Y');

        return [
            'front' => self::renderSide(
                $event,
                false,
                $name,
                $number,
                $date,
                'events/'.$event->id.'/certificate-'.$registration->id.'.png'
            ),
            'back' => self::renderSide(
                $event,
                true,
                $name,
                $number,
                $date,
                'events/'.$event->id.'/certificate-'.$registration->id.'-back.png'
            ),
        ];
    }

    /**
     * Render pratinjau sampel (depan + belakang) dengan nama contoh,
     * agar admin bisa melihat hasil sebelum generate untuk semua peserta.
     *
     * @return array{front: ?string, back: ?string} path relatif (disk public) PNG per sisi.
     */
    public static function preview(Event $event): array
    {
        $name = 'Nama Peserta Contoh';
        $date = now()->translatedFormat('d F Y');

        return [
            'front' => self::renderSide(
                $event,
                false,
                $name,
                '-',
                $date,
                'events/'.$event->id.'/preview-front.png'
            ),
            'back' => self::renderSide(
                $event,
                true,
                $name,
                '-',
                $date,
                'events/'.$event->id.'/preview-back.png'
            ),
        ];
    }

    private static function renderSide(Event $event, bool $back, string $name, string $number, string $date, string $outputPath): ?string
    {
        $template = $back ? $event->certificate_template_back : $event->certificate_template;

        if (! $template) {
            return null;
        }

        $layout = ($back ? $event->certificate_layout_back : $event->certificate_layout) ?? [];

        // @ : tekan warning gd/libpng (mis. iCCP sRGB profile) agar tidak menjadi exception.
        $image = @imagecreatefromstring((string) file_get_contents(Storage::disk('public')->path($template)));

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = $width / 1240;

        // Nama hanya untuk sisi depan
        if (! $back) {
            $nameLine = self::nameLine($layout);
            if ($nameLine) {
                self::drawText($image, $width, $height, $scale, $nameLine, $name);
            }
        }



        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($outputPath, $png);

        return $outputPath;
    }

    private static function nameLine(array $layout): ?array
    {
        foreach ($layout as $line) {
            if (($line['type'] ?? null) === 'name' && ($line['enabled'] ?? true)) {
                return $line;
            }
        }

        return null;
    }

    private static function drawText($image, int $width, int $height, float $scale, array $line, string $text): void
    {
        if ($text === '' || $text === null) {
            return;
        }

        $fontPath = self::fontPath($line['font'] ?? 'lato', $line['weight'] ?? 'bold');

        if (! $fontPath || ! is_file($fontPath)) {
            return;
        }

        $size = (float) ($line['size'] ?? 44) * $scale;
        $color = self::allocateColor($image, $line['color'] ?? '#1e293b');

        $bbox = imagettfbbox($size, 0, $fontPath, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]);

        $anchorX = ($line['x'] ?? 50) / 100 * $width;
        $anchorY = ($line['y'] ?? 50) / 100 * $height;

        $x = match ($line['align'] ?? 'center') {
            'left' => $anchorX,
            'right' => $anchorX - $textWidth,
            default => $anchorX - $textWidth / 2,
        };

        // imagettftext: titik y adalah baseline (dasar teks).
        $y = $anchorY + $textHeight / 2;

        imagettftext($image, $size, 0, (int) round($x), (int) round($y), $color, $fontPath, $text);
    }

    private static function fontPath(string $family, string $weight): ?string
    {
        $file = self::FONTS[$family][$weight] ?? self::FONTS[$family]['regular'] ?? null;

        return $file ? public_path('fonts/'.$file) : null;
    }

    private static function allocateColor($image, string $hex): int
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/./', '$0$0', $hex);
        }

        [$r, $g, $b] = array_map(fn ($h) => hexdec($h), str_split($hex, 2));

        return imagecolorallocate($image, $r, $g, $b);
    }
}
