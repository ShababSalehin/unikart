<?php

namespace App;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadPendingSalesProductStockReportModel implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {

        $start_date = null;
        $end_date = null;

        $products = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->select(
                'products.name as product_name','qty',
                'products.current_stock as current_stock',
                DB::raw('sum(quantity) AS quantity'))
            ->groupBy('products.id');



        $start_date = $this->start_date;
        $end_date = $this->end_date;

        if (!empty($start_date) && !empty($end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
            $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);
        }

        $products->where('orders.delivery_status', ['pending']);
        $products = $products->get();

        return collect($products);
    }

    public function headings(): array
    {
        return [

            'Product Name',
            'Order Qty',
            'Stock Qty'

        ];
    }

    public function map($orders): array
    {

        return [
            $orders->product_name,
            $orders->quantity,
            $orders->qty

        ];
    }
}
