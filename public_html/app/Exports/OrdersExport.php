<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'رقم الطلب',
            'العميل',
            'البريد الإلكتروني',
            'الهاتف',
            'الحالة',
            'حالة الدفع',
            'طريقة الدفع',
            'المجموع الفرعي',
            'الخصم',
            'الشحن',
            'الضريبة',
            'الإجمالي',
            'تاريخ الطلب',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->customer_name ?? $order->user?->name,
            $order->customer_email ?? $order->user?->email,
            $order->customer_phone ?? $order->user?->phone,
            $order->status,
            $order->payment_status,
            $order->payment_method,
            number_format($order->subtotal, 2),
            number_format($order->discount_amount, 2),
            number_format($order->shipping_cost, 2),
            number_format($order->tax_amount, 2),
            number_format($order->total_amount, 2),
            $order->created_at->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D97A8C']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
