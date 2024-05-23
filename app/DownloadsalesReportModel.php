<?php

namespace App;

use App\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadsalesReportModel implements FromCollection, WithMapping, WithHeadings
{
  public function __construct($start_date,$end_date,$search,$date){
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->date = $date;
    $this->search = $search;
    }

    public function collection()
    {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $date = $this->date;
        $sort_search =  $this->search;

        $orders = Order::orderBy('orders.created_at', 'ASC')
        ->whereNotIn('orders.delivery_status',['cancelled']);
        if(!empty($sort_search)){
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start_date = date('Y-m-d 00:00:00',strtotime($this->start_date));
            $end_date = date('Y-m-d 23:59:59',strtotime($this->end_date));
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
            'Order ID',
            'Customer ID',
            'Customer Name',
            'Customer Phone',
            'Customer Address',
            'Amount',
        ];
    }

    public function map($orders): array
    {
        $delivery_status = $orders->orderDetails->first();
        $error = 0;
        if(empty($delivery_status)){
        $error = 1;
        return [];
        }else{
        if($delivery_status->delivery_status=='cancel' || $delivery_status->delivery_status=='pending'){
        $error = 1;
        return [];
        }
        }

        $total = 0;
        if(!empty(\App\Customer::where('user_id', $orders->user_id)->first())){
        $customer_id = \App\Customer::where('user_id', $orders->user_id)->first()->customer_id;
       $payment_details = json_decode($orders->payment_details);
       }else{
        $customer_id = '';
        $payment_details = json_decode($orders->payment_details);
        
      }
        $total+=$orders->grand_total;

      if ($orders->user != null){
            return [
                 date('d-m-Y',$orders->date),
                $orders->code,
                $orders->user->id,
                $orders->user->name,
                $orders->user->phone,
                $orders->user->address,
                $orders->grand_total,
            ];

        }else{
            return [
                date('d-m-Y',$orders->date),
                $orders->code,
                $orders->guest_id,
                json_decode($orders->shipping_address)->name,
                json_decode($orders->shipping_address)->phone,  
                json_decode($orders->shipping_address)->address,
                $orders->grand_total,
            ];
        }
    }
}