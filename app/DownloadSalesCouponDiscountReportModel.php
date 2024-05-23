<?php

namespace App;
use DB;
use App\Order;

use App\User;
use App\Search;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadSalesCouponDiscountReportModel implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($seller_id,$start_date,$end_date){
        $this->start_date = $start_date;    
        $this->end_date = $end_date;        
        $this->seller_id = $seller_id;        
    }

    public function collection()
    {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $seller_id =$this->seller_id;
        
        $orders = Order::orderBy('orders.created_at', 'ASC')
        ->where('orders.delivery_status', 'delivered');
       
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start_date = date('Y-m-d 00:00:00',strtotime($this->start_date));
            $end_date = date('Y-m-d 23:59:59',strtotime($this->end_date));
        }

        if(!empty( $seller_id)){        
            $orders  = $orders ->where('orders.seller_id', $seller_id);
        }

        $orders = $orders->whereBetween('created_at', [$start_date, $end_date]);
     
        
        $start_date = date('Y-m-d',strtotime($start_date));
        $end_date = date('Y-m-d',strtotime($end_date));
        $orders = $orders->get();
      
       
            return collect($orders);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Order Code',
            'Seller Discount',
            'Unikart Discount'
        
        ];
    }

    public function map($orders): array
    {
        
        $error = 0;
        if($order->seller_coupon_discount<=0 && $order->unikart_coupon_discount<=0){
            $error = 1;
            return [];
        }

       // $total_seller_coupon_discount+=$orders->seller_coupon_discount;
       // $total_unikart_coupon_discount+=$orders->unikart_coupon_discount;
        return [
            date('d-m-Y',$orders->date),
            $orders->code,           
            $orders->seller_coupon_discount,
            $orders->unikart_coupon_discount
            
        ];

       
       
    }
}