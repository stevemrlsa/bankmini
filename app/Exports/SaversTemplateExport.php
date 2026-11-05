<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaversTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * Header Kolom Template Excel
     */
    public function headings(): array
    {
        return [
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Jenis Kelamin (L/P)',
        ];
    }

    /**
     * Contoh Data Pengisian Template
     */
    public function array(): array
    {
        return [
            [
                'Ahmad Fauzi',
                '20261001',
                'X RPL 1',
                'L',
            ],
            [
                'Siti Nurhaliza',
                '20261002',
                'X AKL 2',
                'P',
            ],
            [
                'Budi Santoso',
                '20261003',
                'XI DKV 1',
                'L',
            ],
        ];
    }

    /**
     * Styling baris header
     */
    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16233F']
                ]
            ],
        ];
    }
}
