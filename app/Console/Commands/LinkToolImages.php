<?php

namespace App\Console\Commands;

use App\Models\Tool;
use App\Models\ToolImage;
use Illuminate\Console\Command;

class LinkToolImages extends Command
{
    protected $signature = 'tools:link-images';

    protected $description = 'Link orphaned tool images in storage to the first 4 tools';

    public function handle(): int
    {
        $mapping = [
            'Mikroskop Binokuler'      => 'AaEh5yuDrU7FMXEGBb51gArCa0x7hPnIg02SuyR0.png',
            'Autoclave Sterilisasi'    => 'CUVWwKYikaNmzbfrIa8E6mdEdL7q01h1PMDeDT3l.png',
            'Sentrifuge Laboratorium'  => 'kHat63Va4dg4F9uppiMXYCUHQHhmslPXaiuuabO1.jpg',
            'Spektrofotometer UV-Vis'  => 'lOkpzxBKQJEBFytKCJmOI9ZB8fTuD2RyJ9uZlWre.png',
        ];

        foreach ($mapping as $name => $filename) {
            $tool = Tool::where('name', $name)->first();

            if (! $tool) {
                $this->warn("Alat '{$name}' tidak ditemukan, dilewati.");
                continue;
            }

            if ($tool->images()->count() > 0) {
                $this->info("Alat '{$name}' sudah punya gambar, dilewati.");
                continue;
            }

            $path = "tools/{$filename}";

            ToolImage::create([
                'tool_id'    => $tool->id,
                'path'       => $path,
                'sort_order' => 0,
            ]);

            $this->info("Berhasil link gambar untuk '{$name}' → {$path}");
        }

        return self::SUCCESS;
    }
}
