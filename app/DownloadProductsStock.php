<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadProductsStock implements FromCollection, WithMapping, WithHeadings
{

    public function __construct($category_id, $product_id, $brand_id, $shop_id)
    {
        $this->category_id = $category_id;
        $this->product_id = $product_id;
        $this->brand_id = $brand_id;
        $this->shop_id = $shop_id;
    }

    public function collection()
    {

        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->orderBy('products.current_stock', 'desc');

        if (!empty($this->category_id->category_id)) {
            $sort_by = $this->category_id->category_id;
            $products = $products->where('category_id', $sort_by);
        }

        if (!empty($this->product_id->product_id)) {
            $pro_sort_by = $this->product_id->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }

        if (!empty($this->category_id->brand_id)) {
            $pro_sort_by = $this->category_id->brand_id;
            $products = $products->where('brands.id', $pro_sort_by);
        }

        if (!empty($this->shop_id->shop_id)) {
            $pro_sort_by = $this->shop_id->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }

        //$products = $products->select('products.*', 'categories.name as category_name','shops.name as shopsname')->get();

        $products = $products->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->whereIn('order_details.delivery_status', ['Pending', 'Confirmed', 'Picked Up', 'On the Way', 'Delivered'])
            ->select(
                'products.*',
                'categories.name as category_name',
                'shops.name as shopsname',
                DB::raw('sum(order_details.quantity) AS sales_quantity')
            )->groupBy('products.id')->get();

        return collect($products);
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Status',
            'Shop Name',
            'Total Sales Qty',
            'Unit price',
            'Stock',
            'Amount',
        ];
    }

    public function map($products): array
    {
        $qty = 0;
        foreach ($products->stocks as $stock) {
            $qty += $stock->qty;
        }

        if ($products->published == 1) {
            $pstatus = "Published";
        } else {
            $pstatus = "Un-Published";
        }
        return [
            $products->id,
            $products->name,
            $pstatus,
            $products->shopsname,
            $products->sales_quantity,
            $products->unit_price * .7,
            $qty,
            ($qty * $products->unit_price * .7),
        ];
    }
}
