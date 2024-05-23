<?php

namespace App;
use Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadCommissionHistory implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($seller_id,$date_range)
    {
        $this->seller_id = $seller_id;
        $this->date_range = $date_range;
    }

    public function collection()
    {
        $seller_id = null;
        $date_range = null;
        
        if(Auth::user()->user_type == 'seller') {
            $seller_id = Auth::user()->id;
        } if($this->seller_id) {
            $seller_id = $this->seller_id;
        }
        
        $commission_history = CommissionHistory::orderBy('created_at', 'desc');
        
        if ($this->date_range) {
            $date_range = $this->date_range;
            $date_range1 = explode(" / ", $this->date_range);
            $commission_history = $commission_history->where('created_at', '>=', $date_range1[0]);
            $commission_history = $commission_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($seller_id){
            
            $commission_history = $commission_history->where('seller_id', '=', $seller_id);
        }
        
        $commission_history = $commission_history->get();

        return collect($commission_history );
    }

    public function headings(): array
    {
        return [
            'Order Date:',
            'Order ID:',
            'Product Price:',
            'Admin Commission',
            'Seller Earning',
            'Created At',
        ];
    }

    public function map($commission_history): array
    {
        if(isset($commission_history->order))
        $ordercode = $commission_history->order->code;
        else
        $ordercode = "This Order Deleted";
        if(isset($commission_history->order))
        $orderdate = $commission_history->order->created_at;
        else
        $orderdate = "Null";

        if(isset($commission_history->order))
        $grand_total = $commission_history->order->grand_total;
        else
      $grand_total = "Null";
            return [
                $orderdate,
                $ordercode,
                $grand_total,
                $commission_history ->admin_commission,
                $commission_history ->seller_earning,
                $commission_history ->created_at,
            ];
    }
}

