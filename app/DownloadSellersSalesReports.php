<?php

namespace App;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadSellersSalesReports implements FromCollection, WithMapping, WithHeadings
{
 
    public function __construct($verification_status)
    {
        $this->verification_status = $verification_status;
        
    }

    public function collection()
    {
        
        $sellers = Seller::orderBy('created_at', 'desc');
        if (!empty($this->verification_status)) {
            $sort_by =  $this->verification_status;
            $sellers = $sellers->where('verification_status', $sort_by)->get();
        }

               return collect($sellers);
    }

    public function headings(): array
    {
        return [
            'Seller Name',
            'Shop Name',
            'Number of Product Sale',
            'Order Amount',
        ];
    }

    public function map($sellers): array
    {
        $prices = \App\OrderDetail::where('seller_id', $sellers->user->id)->sum('price');
        $num_of_sale = 0;
        foreach ($sellers->user->products as $key => $product) {
            $num_of_sale += $product->num_of_sale;
        }
            return [
                $sellers->user->name,
                $sellers->user->shop->name,
                $num_of_sale,
                $prices,
            ];
    }
}