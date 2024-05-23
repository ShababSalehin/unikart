<?php

namespace App;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class DownloadProdutsWiseSale implements FromCollection, WithMapping, WithHeadings
{

    public function __construct($category_id, $product_id, $brand_id, $shop_id, $start_date, $end_date)
    {
        $this->category_id = $category_id;
        $this->product_id = $product_id;
        $this->brand_id = $brand_id;
        $this->shop_id = $shop_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $start_date = '';
        $end_date = '';
        if (empty($this->start_date))
            $this->start_date = $start_date;

        if (empty($this->end_date))
            $this->end_date = $end_date;

        DB::enableQueryLog();
        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')->where('num_of_sale', '>', '0')
            ->select(
                'products.name as product_name',
                'shops.name as shopname',
                'categories.name as category_name',
                DB::raw('sum(order_details.price) AS price'),
                DB::raw('sum(quantity) AS quantity'),
                DB::raw('count(product_id) AS num_of_sale')
            )->groupBy('products.id')->orderBy('num_of_sale', 'desc');

        if (!empty($this->category_id)) {
            $sort_by = $this->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        if (!empty($this->brand_id)) {
            $pro_sort_by = $this->brand_id;
            $products = $products->where('brand_id', $pro_sort_by);
        }
        if (!empty($this->shop_id)) {
            $pro_sort_by = $this->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }
        if (!empty($this->product_id)) {
            $pro_sort_by = $this->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }


        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start_date = $this->start_date;
            $end_date = date('Y-m-d', strtotime($this->end_date . ' +1 day'));
            $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])->select('products.name as product_name', 'categories.name as category_name', DB::raw('sum(order_details.price) AS price'), DB::raw('sum(quantity) AS quantity'), DB::raw('count(product_id) AS num_of_sale'))->groupBy('products.id')->orderBy('num_of_sale', 'desc');
        }

        $products->whereIn('order_details.delivery_status', ['Pending', 'Confirmed', 'Picked Up', 'On the Way', 'Delivered']);
        $products = $products->get();
        if (!empty($this->end_date))
            $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        return collect($products);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Category',
            'Shop Name',
            'Sales Qty',
            'Unit price',
            'Amount',
            'Total Order',
        ];
    }

    public function map($products): array
    {
        if (!empty($products->quantity)) {
            $qty = $products->quantity;
        } else {
            $qty = 1;
        }

        return [
            $products->product_name,
            $products->category_name,
            $products->shopname,
            $products->quantity,
            (single_price($products->price / $qty)),
            $products->price,
            $products->num_of_sale,
        ];
    }
}
