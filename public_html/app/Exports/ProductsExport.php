<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'اسم المنتج',
            'الرمز (SKU)',
            'الكمية المباعة',
            'عدد الطلبات',
            'إجمالي الإيرادات',
        ];
    }

    public function map($product): array
    {
        return [
            $product->product_name,
            $product->product_sku,
            $product->total_qty,
            $product->order_count,
            number_format($product->total_revenue, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D97A8C']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
