<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Product_stock_close; //added by alauddin
use App\ProductStock; //added by alauddin
use App\OrderDetail; //added by alauddin
use DB; //added by alauddin
use App\Category;
use App\SubCategory;
use App\SubSubCategory;
use App\Brand;
use App\User;
use Auth;
use App\ProductsImport;
use App\ProductsExport;
use PDF;
use Excel;
use Illuminate\Support\Str;

class ProductBulkUploadController extends Controller
{
    public function index()
    {
        if (Auth::user()->user_type == 'seller') {
            return view('frontend.user.seller.product_bulk_upload.index');
        }
        elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('backend.product.bulk_upload.index');
        }
    }

    public function export(){
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function pdf_download_category()
    {
        $categories = Category::all();

        return PDF::loadView('backend.downloads.category',[
            'categories' => $categories,
        ], [], [])->download('category.pdf');
    }

    public function pdf_download_brand()
    {
        $brands = Brand::all();

        return PDF::loadView('backend.downloads.brand',[
            'brands' => $brands,
        ], [], [])->download('brands.pdf');
    }

    public function pdf_download_seller()
    {
        $users = User::where('user_type','seller')->get();

        return PDF::loadView('backend.downloads.user',[
            'users' => $users,
        ], [], [])->download('user.pdf');

    }

    public function bulk_upload(Request $request)
    {
        if($request->hasFile('bulk_file')){
            $import = new ProductsImport;
            Excel::import($import, request()->file('bulk_file'));
            
            if(\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && 
                    \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated){
                $seller = Auth::user()->seller;
                $seller->remaining_uploads -= $import->getRowCount();
                $seller->save();
            }
//            dd('Row count: ' . $import->getRowCount());
        }
        
        
        return back();
    }


	function stock_upload(){
        return view('backend.product.bulk_upload.opening_stock_upload');
    }


	function stock_upload_action(Request $request){

        $upload=$request->file('bulk_file');
        $filePath=$upload->getRealPath();

        //open and read
        $file=fopen($filePath,'r');
        $header=fgetcsv($file);
        
        $escapedHeader=[];

        //validation
        foreach($header as $key=>$value){
            $l_header=strtolower($value);
            $escapedItem=preg_replace('/[^a-z]/','',$l_header);
            array_push($escapedHeader,$escapedItem);
           
        }

        //Looping through other column
        
        while($columns=fgetcsv($file)){
           
            if($columns[0]==''){
                continue;
            }
            //trim data
            foreach($columns as $key=>&$value){
                //$value=preg_replace('/\D/','',$value);
            }

            $data=array_combine($escapedHeader,$columns);

            //setting type

            
            foreach($data as $key=>&$value){
                $value=($key=="stock")?(float)$value:$value;   
            }

            //Table Update

           


            $stock=$data['stock'];

            //$stock_amount=(float)$data['amount'];

            
            $id=(int)$data['id'];
            $product_id=(int)$data['productid'];
        
        	// if($product_id != 431){
        	// 	continue;
        	// }
			
        	$variant=$data['variant'];
				
            $product_info=array();        
            //$product_info= ProductStock::firstOrNew(['id'=>$id]);
        
        	if(empty($variant)){
                $product_info= ProductStock::firstOrNew(['product_id'=> $product_id]);
            }else{
                //continue;
                $product_info= ProductStock::firstOrNew(['product_id'=> $product_id,'variant'=>$variant]);
            }

        
        	 

            $order_info=array();
            // $order_info=OrderDetail::
            // where('product_id','=',$product_id)
            // ->where('delivery_status','=','pending')
            // ->select(DB::raw('sum(quantity) as total_qty'))->get();        
        
        
        	$order_info=OrderDetail::
            leftjoin('orders','order_details.order_id','=', 'orders.id')
            ->where('product_id','=',$product_id)
            ->where('orders.delivery_status','=','pending')
            ->where('order_details.delivery_status','=','pending')
            ->select(DB::raw('sum(quantity) as total_qty'))->get();  
            
        	// $c_stock=$stock-$order_info[0]->total_qty;
        	// echo $order_info[0]->total_qty;
        	// exit;
           
            if(!empty($stock)){
                $product_info->qty=$stock-$order_info[0]->total_qty;
            
        	 	// $c_stock=$stock-$order_info[0]->total_qty;
        	 	// echo $c_stock;
        	 	// exit;
            }else{
                $product_info->qty=0;
            }
           
        	// echo $product_info->qty;
        	// exit;
        	
            $product_info->save();
			
        	
           

        }
        flash(translate('Products stock imported successfully'))->success();
        return back();
    }

}
