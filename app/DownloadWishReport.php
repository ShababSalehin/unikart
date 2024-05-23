<?php

namespace App;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadWishReport implements FromCollection, WithMapping, WithHeadings
{
 
    public function __construct($category_id)
    {
        $this->category_id = $category_id;
        
    }

    public function collection()
    {
        $sort_by =null;
        $products = Product::orderBy('created_at', 'desc')->get();
        if (!empty($this->category_id->category_id)){
            $sort_by = $this->category_id;
            $products = $products->where('category_id', $sort_by)->get();
        }

        //dd( $products );
               return collect($products);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Number of Wish',
        ];
    }

    public function map($products): array
    {
        
            return [
                $products->name,
                $products->wishlists->count(),
            ];

        

    }
}