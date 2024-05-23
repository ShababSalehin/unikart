<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\ClubPointController;
use App\Http\Controllers\AffiliateController;

use App\Product;
use App\ProductStock;
use App\Color;
use App\OrderDetail;
use App\CouponUsage;
use App\Coupon;
use App\OtpConfiguration;
use App\User;
use App\BusinessSetting;
use Auth;
use Session;
use DB;
use Mail;
use App\Mail\InvoiceEmailManager;
use CoreComponentRepository;
use App\Purchase_order;
use App\Purchase_order_item;
use App\Supplier;
use App\Shop;


class PurchaseController extends Controller
{
     

  // $purchase_order_item = Purchase_order_item::where('po_id',000)->get();
        // $purchase = Purchase_order::findOrFail(000);
        // $purchase->status = 1;    
        // if($purchase->save()){
        //     if(!empty($purchase_order_item)){
        //         foreach($purchase_order_item as $key => $prod){
        //                 if(empty($prod->desc)){
        //                     $ps = ProductStock::where(['product_id'=>$prod->product_id])->first();
        //                 }else{
        //                     $ps = ProductStock::where(['product_id'=>$prod->product_id,'variant'=>$prod->desc])->first();
        //                 }
                       
        //                 if(!empty($ps)){
        //                     $ps->decrement('qty', $prod->qty);
        //                     $ps->save();
        //                 }
        //         }
        //     }

        //     flash(translate('Approved purchase remove successfully!'))->success();
        //     return back();
        // }


     public function purchase_orders(Request $request)
     {
         $date = $request->date;
         $sort_search = '';
         DB::enableQueryLog();
        // $data = Purchase_order::select('purchase_order.*','suppliers.supplier_id','suppliers.name')->leftjoin('suppliers', 'suppliers.supplier_id', '=', 'purchase_order.supplier_id')->orderBy('purchase_order.created_at', 'desc');
        
        $data = Purchase_order::select('purchase_order.*','shops.name')->leftjoin('shops', 'shops.id', '=', 'purchase_order.supplier_id')->orderBy('purchase_order.created_at', 'desc');

         if ($date != null) {
             $data = $data->where('purchase_order.created_at', '>=', date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0])))->where('purchase_order.created_at', '<=', date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1])));
         }

         if ($request->has('search')) {
             $sort_search = $request->search;
             $data = $data->where('purchase_no', 'like', '%' . $sort_search . '%');
         }
         //  dd(DB::getQueryLog());
         $data = $data->paginate(15);
         $title =  'Purchase Order';
         return view('backend.purchase_order.index', compact('data', 'title', 'date', 'sort_search'));
     }
     
     function updatePurchasePrice(){
         $data = DB::select("SELECT p.product_id,(select p1.price from purchase_order_item as p1 where p.product_id=p1.product_id order by p1.created_at desc limit 1) as price FROM `purchase_order_item` as p group by product_id");
         
         foreach($data as $pp){
 try{
         $p = Product::find($pp->product_id);
         if($p!=null){
                         $p->purchase_price = $pp->price;
                         $p->save();
         }
 }catch (\Exception $e) {
                     dd($e);
                 }					
         }
     }

     public function add_purchase(Request $request)
     {
         $products = Product::all();
         $supplier = Shop::all();
         $all_purchase = Purchase_order::orderBy('created_at', 'desc')->first();
         $totall_purchase=($all_purchase->purchase_no)+1;
         if($totall_purchase>99999){
            $purchase_no="0".$totall_purchase;
         }else if($totall_purchase>9999){
            $purchase_no="0".$totall_purchase;
         }else if($totall_purchase>999){
            $purchase_no="00".$totall_purchase;
         }else if($totall_purchase>99){
            $purchase_no="000".$totall_purchase;
         }else if($totall_purchase>9){
            $purchase_no="0000".$totall_purchase;
         }else{
            $purchase_no="00000".$totall_purchase; 
         }

         $title =  'Purchase Add';
         
         return view('backend.purchase_order.add', compact('products', 'title', 'supplier','purchase_no'));
     }


     public function store_purchase(Request $request)
     {
         if (!empty($request->purchase_no)) {
             $purchase = new Purchase_order();
               
             $purchase->purchase_no = $request->purchase_no;
             $purchase->batch_no = $request->batch_no;
             $purchase->supplier_id = $request->supplier_id;
             $purchase->date =  $request->purchase_date;
             $purchase->total_value =  $request->total;
             $purchase->created_by = Auth::user()->id;
             $purchase->remarks = $request->remarks;
             
             
             $purchase->save();
             if (!empty($purchase->id)) {
 
                 foreach ($request->product as $key => $prod) {
                     $item = new Purchase_order_item();
                     $item->po_id = $purchase->id;
                     $item->product_id = $prod;
                     $item->qty = $request->qty[$key];
                     //$item->expiry_date = $request->exp[$key];
                     $item->desc = $request->desc[$key];
                     $item->price = $request->price[$key];
                     $item->amount = $request->price[$key] * $request->qty[$key];
                    
                     $item->save();
 
                     
                 }
                 
                if(Auth::user()->user_type == 'admin'){
                 return redirect()->route('purchase_orders.index');
                }
                 
             } else {
                 flash(translate('Something went wrong'))->error();
                 return back();
             }
         } else {
             flash(translate('Something went wrong'))->error();
             return back();
         }
     }
 
  public function puracher_edit($id)
     {
         $purchase = Purchase_order::findOrFail($id);
         $purchase_item = Purchase_order_item::where('po_id', $id)->get();

         $products = Product::all();
        // $supplier = Supplier::all();
         $supplier = Shop::all();
         $title =  'Purchase Edit';    

         
        return view('backend.purchase_order.edit', compact('products', 'title', 'supplier', 'purchase','purchase_item'));
         
         
     }
 
     public function puracher_edit_store(Request $request)
     {
         if (!empty($request->purchase_no)) {
             
             $purchase = Purchase_order::findOrFail($request->po_id);
 
             $purchase->purchase_no = $request->purchase_no;
             $purchase->batch_no = $request->batch_no;
             $purchase->supplier_id = $request->supplier_id;
             $purchase->date =  $request->purchase_date;
             $purchase->total_value =  $request->total;
             $purchase->created_by = Auth::user()->id;
             $purchase->remarks = $request->remarks;
            
             if ($purchase->save()) {
                Purchase_order_item::where('po_id', $request->po_id)->delete();                  
                 foreach ($request->product as $key => $prod) {
                     $item = new Purchase_order_item();
                     $item->po_id = $purchase->id;
                     $item->product_id = $prod;
                     $item->qty = $request->qty[$key];
                     $item->desc = $request->desc[$key];
                     $item->price = $request->price[$key];
                     $item->amount = $request->price[$key] * $request->qty[$key];
                     $item->save();                         
                 }
                 flash(translate('Purchase Edited Successfully'))->success();                 
                 return redirect()->route('purchase_orders.index');
             } else {
                 flash(translate('Something went wrong'))->error();
                 return back();
             }
         } else {
             flash(translate('Something went wrong'))->error();
             return back();
         }
     }
 
     public function purchase_orders_view($id)
     {

        $purchase = Purchase_order::where('id',$id)
                     ->leftjoin('suppliers','suppliers.supplier_id','=','purchase_order.supplier_id')
                     ->get();
        $data_item_rows = Purchase_order_item::where('po_id', $id)
                         ->leftjoin('products','products.id','=','purchase_order_item.product_id')
                         ->get();        
        return view('backend.purchase_order.view', compact('purchase','data_item_rows'));
         
     }


     public function destroy_po($id)
    {
        $order = Purchase_order::findOrFail($id);
        if ($order != null) {
            $orderDetails = Purchase_order_item::where('po_id',$id)->get();           
            Purchase_order_item::where('po_id', $id)->delete();
            $order->delete();
            flash(translate('Purchase Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }


    public function get_puracher_product(Request $request){
        $product_id = $request->product_id;
       // $wearhouse_id = $request->wearhouse_id;
        $data['product'] = ProductStock::where(['product_id'=>$product_id])->first();
        $data['product_variant'] = ProductStock::where(['product_id'=>$product_id])->get();
       // return $product;
       return $data;
    }

    public function get_supplier_product(Request $request){
        $supplier_id = $request->supplier_id;        
        $product = Product::leftjoin('shops','shops.user_id','=','products.user_id')
        ->where(['shops.id'=>$supplier_id])
        ->select('products.*')
        ->get();
       // dd($product);


       $html = '<option value="">'.translate("Select Product").'</option>';
        
       foreach ($product as $row) {
           $html .= '<option value="' . $row->id . '">' . $row->getTranslation('name') . '</option>';
       }
       
       echo json_encode($html);
       // return $product;
        
    }


    public function purchase_approve($id){
    
       $purchase_order_item = Purchase_order_item::where('po_id',$id)->get();
        
        $purchase = Purchase_order::findOrFail($id);
        $purchase->status = 2;    
        
        if($purchase->save()){
        	if(!empty($purchase_order_item)){
                foreach($purchase_order_item as $key => $prod){
    
                        
                		if(empty($prod->desc)){
                            $ps = ProductStock::where(['product_id'=>$prod->product_id])->first();
                        }else{
                            $ps = ProductStock::where(['product_id'=>$prod->product_id,'variant'=>$prod->desc])->first();
                        }
                       
                        if(!empty($ps)){
                            $ps->increment('qty', $prod->qty);
                            $ps->save();
                        }else{
                           // ProductStock::insert(['product_id'=>$prod->product_id,'qty'=>$prod->qty]);
                        }
                }
            }
            flash(translate('Purchase status has been updated successfully!'))->success();
            return back();
        }
        	       
    }
    
    public function purchase_update_payment_status(Request $request){
        $purchase_id = $request->purchase_id;
        $purchase = Purchase_order::findOrFail($purchase_id);
        $purchase->payment_amount = $request->payment_amount;
        $purchase->payment_status = $request->status;
        $purchase->save();            
        return 1;
    }



	
}
