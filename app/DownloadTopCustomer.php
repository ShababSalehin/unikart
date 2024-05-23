<?php

namespace App;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadTopCustomer implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($order_by,$top,$phone,$start_date,$end_date)
    {
        
        $this->top = $top;
        $this->order_by = $order_by;
        $this->phone = $phone;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
     
    }



    public function collection()
    {
        
        $top=10;
        $order_by="amount";
        $phone="";

        // $start_date = date('Y-m-01');
        // $end_date = date('Y-m-t');

        $start_date ='';
        $end_date = '';

        if(!empty($this->top)){
            $top = $this->top;
        }

        if(!empty($this->order_by)){
            $order_by=$this->order_by;        
        }


        if(!empty($this->phone)){
            $phone=$this->phone;        
        }

           $qlog = DB::enableQueryLog();
            
           if($order_by=="amount"){
                if(!empty($this->phone)){
                    $topcustomers = User::
                    leftjoin('orders','users.id','orders.user_id')
                    ->leftjoin('order_details','orders.id','order_details.order_id')
                    ->select('users.id as userid','users.name as customer_name','users.phone as customer_phone',
                    DB::raw('sum(order_details.quantity) AS productquantity'),
                    DB::raw('sum(orders.grand_total) AS amount'),
                    DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('amount','desc')
                    ->where('orders.delivery_status','delivered')
                    ->where('users.phone',$this->phone)
                    ->limit($top);
                }else{
                    $topcustomers = User::
                    leftjoin('orders','users.id','orders.user_id')
                    ->leftjoin('order_details','orders.id','order_details.order_id')
                    ->select('users.id as userid','users.name as customer_name','users.phone as customer_phone',
                    DB::raw('sum(order_details.quantity) AS productquantity'),
                    DB::raw('sum(orders.grand_total) AS amount'),
                    DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('amount','desc')
                    ->where('orders.delivery_status','delivered')
                    ->limit($top);
                }    
           }else{
                if(!empty($this->phone)){
                    $topcustomers = User::
                    leftjoin('orders','users.id','orders.user_id')
                    ->leftjoin('order_details','orders.id','order_details.order_id')
                    ->select('users.id as userid','users.name as customer_name','users.phone as customer_phone',
                    DB::raw('sum(order_details.quantity) AS productquantity'),
                    DB::raw('sum(orders.grand_total) AS amount'),
                    DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('totalorder','desc')
                    ->where('orders.delivery_status','delivered')
                    ->where('users.phone',$this->phone)
                    ->limit($top);
                }else{
                    $topcustomers = User::
                    leftjoin('orders','users.id','orders.user_id')
                    ->leftjoin('order_details','orders.id','order_details.order_id')
                    ->select('users.id as userid','users.name as customer_name','users.phone as customer_phone',
                    DB::raw('sum(order_details.quantity) AS productquantity'),
                    DB::raw('sum(orders.grand_total) AS amount'),
                    DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('totalorder','desc')
                    ->where('orders.delivery_status','delivered')
                    ->limit($top)->get();
                }    
           }


           if (!empty($this->start_date) && !empty($this->end_date)) {
                $start_date = date('Y-m-d 00:00:00',strtotime($this->start_date));
                $end_date = date('Y-m-d 23:59:59',strtotime($this->end_date));
                $topcustomers =$topcustomers->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);
           }

           $topcustomers =$topcustomers->get();

        return collect($topcustomers);
    }

    public function headings(): array
    
    {
              
        return [        
            'Customer Name',
            'Customer Phone',
            'Total Order',
            'Date',
            'Product QTY',
            'Total Amount',
        ];
    }

    public function map($topcustomers): array
    {
        if(!empty($this->start_date)){
            return [
                $topcustomers->customer_name,
                $topcustomers->customer_phone,
                $topcustomers->totalorder,
                $this->start_date.' To '.$this->end_date,
                $topcustomers->productquantity,
                $topcustomers->amount

            ];
       }else{
            return [
                $topcustomers->customer_name,
                $topcustomers->customer_phone,
                $topcustomers->totalorder,
                '',
                $topcustomers->productquantity,
                $topcustomers->amount

            ];
       }

    }    
    
}