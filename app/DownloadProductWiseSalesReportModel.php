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

class DownloadProductWiseSalesReportModel implements FromCollection, WithMapping, WithHeadings
{
  public function __construct($start_date,$end_date){
    $this->start_date = $start_date;
    $this->end_date = $end_date;
        
       
    }

    public function collection()
    {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        
        $orders = Order::orderBy('orders.created_at', 'ASC')
        ->leftjoin('users','users.id','=','orders.user_id')
        ->leftjoin('shops','shops.user_id','=','orders.seller_id')
        ->where('orders.delivery_status', 'delivered')
        ->select('users.name as username','shops.name as seller_name','orders.*','users.phone');
        

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start_date = date('Y-m-d 00:00:00',strtotime($this->start_date));
            $end_date = date('Y-m-d 23:59:59',strtotime($this->end_date));
        }else{
            $start_date = date('Y-m-d 00:00:00',strtotime($start_date));
            $end_date = date('Y-m-d 23:59:59',strtotime($end_date));
        }

        $orders = $orders->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);
     
        
        $start_date = date('Y-m-d',strtotime($start_date));
        $end_date = date('Y-m-d',strtotime($end_date));
        $orders = $orders->get();
       
        return collect($orders);
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Order Code', 
            'Order Date', 
            'Product Name',          
            'Customer Name',
            'Customer Phone',
            'Qty',
            'Seller',
            'Invoice',
            'SSL',
            'App Coupon',
            'Unikart Coupon',
            'Seller Coupon'
        ];
    }

    public function map($orders): array
    {
        // $total_invoice=$total_invoice+$orders->grand_total;
         $app_coupon=$orders->orderDetails->sum('app_discount');
        // $total_app_coupon=$total_app_coupon+$orders->orderDetails->sum('app_discount');
        // $total_unikart_coupon=$total_unikart_coupon+$orders->unikart_coupon_discount;
        // $total_seller_coupon=$total_seller_coupon+$orders->seller_coupon_discount;
        $m=0;
        foreach($orders->orderDetails as $key1=>$value){
            $m++;
            //$totalqty=$totalqty+$value->quantity; 
            if($m==1){
                return [                
                    $orders->id,
                    $orders->code,
                    date('d-m-Y',$orders->date),
                    json_decode($value->product)->name,
                    $orders->username,
                    $orders->phone,
                    $value->quantity,
                    $orders->seller_name,
                    $orders->grand_total,
                    $app_coupon,
                    $orders->unikart_coupon_discount,
                    $orders->seller_coupon_discount
                ];
            }else{
                return [                
                    $orders->id,
                    $orders->code,
                    date('d-m-Y',$orders->date),
                    json_decode($value->product)->name,
                    $orders->username,
                    $orders->phone,
                    $value->quantity,
                    $orders->seller_name,
                    '',
                    '',
                    '',
                    ''
                ];
            }
        }

        
       
    }



}