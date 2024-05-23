<?php

namespace App;
use DB;
use App\Order;
//use App\OrderDetail;

use App\User;
use App\Search;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadPendingSalesReportModel implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($from_order_no,$to_order_no){
        $this->from_order_no = $from_order_no;
         //dd($this->start_date);
        $this->to_order_no =$to_order_no;
        //dd($this->end_date);
       
        //dd($this->search);
    }

    public function collection()
    {
        
        $from_order_no = null;
        $to_order_no = null;
        
        $orders = Order::orderBy('orders.created_at', 'ASC')
                        ->groupBy('orders.combined_order_id')   

        ->where('orders.delivery_status', 'confirmed');
	// dd($orders);
           
        $from_order_no = $this->from_order_no;
        $to_order_no = $this->to_order_no;
        if(!empty($from_order_no) && !empty($to_order_no)){
            $orders = $orders->whereBetween('orders.id', [$from_order_no,$to_order_no]);
        }
                   
        $orders = $orders->get();   
        return collect($orders);

    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Customer Name',
            'Contact No.',
            'Customer Address',
            'District',
            'Area',
            'Price',
            'Product Selling Price',
            'Weight(g)',
            'Instruction',
        ];
    }

    public function map($orders): array
    {
       

        

      if ($orders->user != null){

        $shipping = json_decode($orders->shipping_address);
        $state_name = \App\State::where('id', $shipping->state_id)->first()->name;                            
        $city_name = \App\City::where('id', $shipping->city_id)->first()->name;
      
      //  $all_order=\App\Order::where('combined_order_id',$orders->combined_order_id)->pluck('id')->toArray();
      //  $subtotal = $orders->orderDetails->whereIn('order_id',$all_order)->sum('price')+$orders->orderDetails->whereIn('order_id',$all_order)->sum('discount');
     //   $discount = $orders->orderDetails->whereIn('order_id',$all_order)->sum('discount');
      //  $shipping_cost = $orders->orderDetails->whereIn('order_id',$all_order)->sum('shipping_cost');
      //  $cdiscount = $orders->coupon_discount;
      
      $all_order=\App\Order::where('combined_order_id',$orders->combined_order_id)->get();
                    
        $subtotal = 0;
        $discount = 0;
        $cdiscount = 0;
        $shipping_cost = 0;

       foreach($all_order as $key1=>$value){
                   
            $subtotal += $value->orderDetails->sum('price')+$value->orderDetails->sum('discount');
            $discount += $value->orderDetails->sum('discount');
            $shipping_cost += $value->orderDetails->sum('shipping_cost');
            $cdiscount += $value->coupon_discount;                  
       }

       if($orders->payment_status=='unpaid'){
            $net_total=$subtotal+$shipping_cost-$discount-$cdiscount;
        }else{
            $net_total=0;
        }
       
            return [
                
                $orders->code,               
                $orders->user->name,
                $shipping->phone,
                $shipping->address . '-'. $city_name . '-'. $state_name,
                $state_name,
                $city_name,
                $net_total,
                $subtotal,
                '500',
                '',
            ];
            

        }else{

            return [                
                $orders->code,               
                $orders->user->name,
                $shipping->phone,
                $shipping->address . '-'. $city_name . '-'. $state_name,
                $state_name,
                $city_name,
                $net_total,
                $subtotal,
                '500',
                '',
            ];

        }
    }
}