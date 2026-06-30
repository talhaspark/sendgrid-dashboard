<?php

namespace App\Exports;

use App\Models\SentEmail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SentEmailReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function __construct(
        protected ?string $dateFrom = null,
        protected ?string $dateTo   = null,
    ) {}

    public function query()
    {
        $query = SentEmail::query()->orderBy('sent_at', 'desc');

        if ($this->dateFrom) {
            $query->where('sent_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
        }

        if ($this->dateTo) {
            $query->where('sent_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
        }

        return $query;
    }

    public function title(): string
    {
        return 'Sent Emails';
    }

    public function headings(): array
    {
        return [
            '#',
            'To Email',
            'From Email',
            'Subject',
            'Status',
            'Opens',
            'Clicks',
            'Bounces',
            'Spam Reports',
            'Sent At',
            'Message ID',
        ];
    }

    public function map($email): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $email->to_email        ?? '—',
            $email->from_email      ?? '—',
            $email->subject         ?? '(No Subject)',
            strtoupper($email->status ?? ''),
            $email->opens           ?? 0,
            $email->clicks          ?? 0,
            $email->bounces         ?? 0,
            $email->spam_reports    ?? 0,
            $email->sent_at?->format('Y-m-d H:i:s') ?? '—',
            $email->sg_message_id   ?? '—',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 30,
            'D' => 45,
            'E' => 14,
            'F' => 8,
            'G' => 8,
            'H' => 10,
            'I' => 13,
            'J' => 22,
            'K' => 40,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Zebra stripes on data rows
        for ($r = 2; $r <= $lastRow; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:L{$r}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8F9FF');
            }
        }

        // Status colours
        $statusColors = [
            'DELIVERED'  => 'FFD1FAE5',
            'OPEN'       => 'FFDBEAFE',
            'CLICK'      => 'FFE0E7FF',
            'BOUNCE'     => 'FFFEE2E2',
            'DEFERRED'   => 'FFFEF3C7',
            'SPAMREPORT' => 'FFFCE7F3',
            'DROPPED'    => 'FFFFF7ED',
        ];

        for ($r = 2; $r <= $lastRow; $r++) {
            $status = $sheet->getCell("E{$r}")->getValue();
            if (isset($statusColors[$status])) {
                $sheet->getStyle("E{$r}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($statusColors[$status]);
            }
        }

        // Table border
        $sheet->getStyle("A1:L{$lastRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB('FFE5E7EB');

        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                    'name'  => 'Arial',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF6366F1'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
            "A2:A{$lastRow}" => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font'      => ['color' => ['argb' => 'FF9CA3AF']],
            ],
            "E2:E{$lastRow}" => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font'      => ['bold' => true, 'size' => 9],
            ],
            "F2:I{$lastRow}" => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "L2:L{$lastRow}" => [
                'font' => ['color' => ['argb' => 'FF9CA3AF'], 'size' => 8],
            ],
        ];
    }
}