<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExport
{
    /**
     * Buat file .xlsx dari array baris (baris pertama = header) dan kirim sebagai unduhan.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, array $rows, array $columnWidths = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $columnWidths) {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            foreach ($rows as $r => $row) {
                foreach (array_values($row) as $c => $value) {
                    $cell = $sheet->getCell([$c + 1, $r + 1]);

                    if ($value === null || $value === '') {
                        continue;
                    }

                    // Semua string ditulis sebagai teks agar nilai yang diawali
                    // =, +, -, @ tidak dieksekusi sebagai formula (formula injection),
                    // dan kode seperti AL-001 / INV-0001 tetap utuh di Excel.
                    if (is_string($value)) {
                        $cell->setValueExplicit($value, DataType::TYPE_STRING2);
                    } else {
                        $cell->setValue($value);
                    }
                }
            }

            // Gaya header.
            $headerRange = 'A1:'.$sheet->getHighestColumn().'1';
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Tinggi & pembungkus header.
            $sheet->getRowDimension(1)->setRowHeight(22);

            // Lebar kolom (auto jika tidak diberikan).
            if ($columnWidths) {
                foreach ($columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
            } else {
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }

            $sheet->freezePane('A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
