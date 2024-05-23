<?php

namespace App;

use App\Product;
use App\ProductStock; //added by alauddin
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        $products=ProductStock::
        leftJoin('products','product_stocks.product_id','=', 'products.id');
       
        $products = $products->select('products.name','product_stocks.*')->get();
        return $products;
    }

    public function headings(): array
    {
        return [
           'Id',
            'Product Id',
            'name',
            'Variant',
            'current_stock',
            'purchase_unit_price',
            'Amount',
        ];
    }

    /**
    * @var Product $product
    */
    public function map($product): array
    {
        $qty = 0;
        // foreach ($product->stocks as $key => $stock) {
        //     $qty += $stock->qty;
        // }
        return [
            $product->id,
            $product->product_id,
            $product->name,
            $product->variant,
            $qty,

        ];
    }
}
