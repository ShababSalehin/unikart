<?php

namespace App;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadToBePickUp implements FromCollection, WithMapping, WithHeadings
{
 
    public function __construct($start_date,$end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $start_date = "";
        $end_date = "";
        
        DB::enableQueryLog();
        $products = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('shops','products.user_id', '=','shops.user_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('num_of_sale', '>', '0')
            ->select('products.name as product_name','orders.code','products.thumbnail_img','shops.name','shops.address','shops.phone',
            DB::raw('sum(quantity) AS quantity'),
            DB::raw('count(product_id) AS num_of_sale'))->groupBy('products.id')->orderBy('num_of_sale', 'desc');
   
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start_date = $this->start_date;
            $end_date = date('Y-m-d', strtotime($this->end_date . ' +1 day'));
            $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])
            ->select('products.name as product_name','orders.code','shops.name','shops.address','shops.phone',
            DB::raw('sum(quantity) AS quantity'),
            DB::raw('count(product_id) AS num_of_sale'))->groupBy('products.id')->orderBy('num_of_sale', 'desc');
        }
        $products->where('order_details.delivery_status', ['confirmed']);
        $products = $products->get();
        //dd($products);
        if(!empty($this->end_date))
        $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));

               return collect($products);
    }

    public function headings(): array
    {
        return [
            'Order Id',
            'Product Name',
            'QTY',
            'Shop Name',
            'Shop Address',
            'Shop contact No',
        ];
    }

    public function map($products): array
    {
        
            return [
                $products->code,
                $products->product_name,
                $products->quantity,
                $products->name,
                $products->address,
                $products->phone,
            ];
    }
}