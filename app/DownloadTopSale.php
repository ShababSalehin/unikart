<?php

namespace App;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DownloadTopSale implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($shop_or_product,$top,$order_by,$city_id,$shop_id,$start_date,$end_date)
    {
        $this->shop_or_product = $shop_or_product;
        $this->top = $top;
        $this->order_by = $order_by;
        $this->city_id = $city_id;
        $this->shop_id = $shop_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
     
    }
    public function collection()
    {
        $top=20;
        $order_by="quantity";
        $shop_or_product="Shop";
        $sort_by = null;
        $pro_sort_by = null;
        $city_id=null;
        $start_date = '';
        $end_date = '';
        DB::enableQueryLog();

        if (!empty($this->shop_or_product)) {
            $shop_or_product = $this->shop_or_product;
        }
        if (!empty($this->top)) {
            $top = $this->top;
        }
        if (!empty($this->order_by)) {
            $order_by = $this->order_by;
        }
        if($shop_or_product=="Shop"){
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops','products.user_id', '=','shops.user_id')
            
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')->select('products.name as product_name','categories.name as category_name','shops.contact_person','shops.contact_number','shops.name as shop_name',DB::raw('sum(order_details.price) AS price'),DB::raw('sum(quantity) AS quantity'))->groupBy('shops.id')->orderBy($order_by, 'desc')->limit($top);
        }else{
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops','products.user_id', '=','shops.user_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')->select('products.name as product_name','categories.name as category_name','shops.name as shop_name',DB::raw('sum(order_details.price) AS price'),DB::raw('sum(quantity) AS quantity'))->groupBy('products.id')->orderBy($order_by, 'desc')->limit($top); 
        }
        if (!empty($this->city_id)) {
            $city_id = $this->city_id;
            $products = $products->where('shops.citi_id', $city_id);
        }
        if (!empty($this->shop_id)) {
            $pro_sort_by = $this->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start_date = $this->start_date;
            $end_date = date('Y-m-d', strtotime($this->end_date . ' +1 day'));
            if($shop_or_product=="Shop"){
                $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])->select('products.name as product_name','shops.contact_person','shops.contact_number','shops.name as shop_name','categories.name as category_name',DB::raw('sum(order_details.price) AS price'),DB::raw('sum(quantity) AS quantity'))->groupBy('shops.id')->orderBy($order_by, 'desc')->limit($top);
            }else{
                $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])->select('products.name as product_name','shops.name as shop_name','categories.name as category_name',DB::raw('sum(order_details.price) AS price'),DB::raw('sum(quantity) AS quantity'))->groupBy('products.id')->orderBy($order_by, 'desc')->limit($top);
            }
        }
        $products->whereNotIn('order_details.delivery_status', ['cancel']);
        $products = $products->get();
        if(!empty($this->end_date))
        $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        

               return collect($products);
    }

    public function headings(): array
    
    {
        if ($this->shop_or_product == 'Shop') {
            $shop_or_product ="Shop";
            
        }else{
            $shop_or_product = "Product Name";
        }
       
        if (($this->order_by == "quantity")) {

            $order_byt ="QTY";
        }else{
            $order_byt ="Amount";
        }
      
        return [
            $shop_or_product,
           'Contact Person',
            'Contact Contact Number',
            $order_byt,
        ];
    }

    public function map($products): array
    {
        
        if($this->shop_or_product == "Shop"){
            $shop_or_product = $products->shop_name;
        }else{
            $shop_or_product = $products->product_name;
        }
          
        if($this->order_by == "quantity"){
            $order_by = $products->quantity;
        }else{
            $order_by = $products->price;
        }

            return [
                $shop_or_product,
                $products->contact_person,
                $products->contact_number,
                $order_by, 
            ];
    }
}