<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ImportReadFilter implements IReadFilter
{
    public function __construct(
        protected int $maxRows = 10000,
        protected int $maxCols = 50,
    ) {}

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        if ($row < 1 || $row > $this->maxRows) {
            return false;
        }

        $columnIndex = Coordinate::columnIndexFromString($columnAddress);

        return $columnIndex <= $this->maxCols;
    }
}
