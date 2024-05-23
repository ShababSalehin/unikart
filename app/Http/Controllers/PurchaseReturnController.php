<?php

namespace App\Http\Controllers;

use App\ProductStock;
use App\Product;
use App\Purchase_order;
use App\Purchase_order_item;
use App\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\PurchaseReturnDetail;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
   
    public function index(Request $request)
    {
        $date = $request->date;
        $sort_search = '';
        DB::enableQueryLog();

        $data = PurchaseReturn::orderBy('created_at', 'desc');

        if ($date != null) {
            $data = $data->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1])));
        }

        if ($request->has('search')) {
            $sort_search = $request->search;
            $data = $data->where('purchase_no', 'like', '%' . $sort_search . '%');
        }

        $data = $data->paginate(15);
        $title =  'Purchase Return';
        return view('backend.purchase_return.index', compact('data', 'title', 'date', 'sort_search'));
    }

    
    public function create()
    {
        $products = Product::all();
        $purchases = Purchase_order::where('status', 2)->get();
        $all_return = PurchaseReturn::all();
        $totall_return = count($all_return) + 1;

        if ($totall_return > 99999) {
            $return_no = "0" . $totall_return;
        } else if ($totall_return > 9999) {
            $return_no = "0" . $totall_return;
        } else if ($totall_return > 999) {
            $return_no = "00" . $totall_return;
        } else if ($totall_return > 99) {
            $return_no = "000" . $totall_return;
        } else if ($totall_return > 9) {
            $return_no = "0000" . $totall_return;
        } else {
            $return_no = "00000" . $totall_return;
        }

        $title =  'Purchase Return Add';

        return view('backend.purchase_return.add', compact('purchases', 'products', 'title', 'return_no'));
    }

   
    public function store(Request $request)
    {
        $purchase_order = Purchase_order::where('id', $request->purchase_id)->first();


        if (!empty($request->purchase_id)) {
            $purchase_return = new PurchaseReturn();
            $purchase_return->purchase_id = $purchase_order->purchase_no;
            $purchase_return->return_date =  $request->return_date;
            $purchase_return->return_number =  $request->return_no;
            $purchase_return->status =  0;
            $purchase_return->save();
            if (!empty($purchase_return->id)) {
                foreach ($request->product as $key => $prod) {
                    $item = new PurchaseReturnDetail();
                    $item->return_id = $purchase_return->id;
                    $item->product_id = $prod;
                    $item->product_verient = $request->desc[$key];
                    $item->return_qty = $request->qty[$key];
                    $item->unite_price = $request->price[$key];
                    $item->total_amount = $request->price[$key] * $request->qty[$key];
                    $item->save();
                }

                if (Auth::user()->user_type == 'admin') {
                    return redirect()->route('purchase_return.index');
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

   
    public function show(Request $request, $id)
    {
        //dd($id);
        $return = PurchaseReturn::where('id', $id)
            ->get();

        $data_item_rows = PurchaseReturnDetail::leftjoin('products', 'purchase_return_details.product_id', 'products.id')
            ->select('products.name', 'purchase_return_details.*')
            ->where('return_id', $id)
            ->get();


        return view('backend.purchase_return.view', compact('return', 'data_item_rows'));
    }


    public function return_approve(Request $request, $id)
    {
        $purchase_return_item = PurchaseReturnDetail::where('return_id', $id)->get();
        $return = PurchaseReturn::findOrFail($id);
        $return->status = 1;
        if ($return->save()) {
            if (!empty($purchase_return_item)) {
                foreach ($purchase_return_item as $key => $prod) {
                    if (($prod->desc == null)) {
                        $ps = ProductStock::where('product_id',$prod->product_id)->first();
                    } else {
                        $ps = ProductStock::where(['product_id'=>$prod->product_id,'variant'=>$prod->desc])->first();
                    }
                    if (!empty($ps)) {
                        $ps->decrement('qty', $prod->return_qty);
                    }
                }
            }
            flash(translate('Purchase return has been updated successfully!'))->success();
            return back();
        }
    }

    
    public function edit(Request $request, $id)
    {
        $purchase_return = PurchaseReturn::findOrFail($id);
        $purchase_item = PurchaseReturnDetail::where('return_id', $id)->get();
        $products = Product::all();
        $all_return = PurchaseReturn::where('status', 0)->get();
        $title =  'Return Edit';
        return view('backend.purchase_return.edit', compact('products', 'title', 'purchase_return', 'purchase_item', 'all_return'));
    }

   
    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        //
    }

    
    public function destroy(Request $request, $id)
    {
        $return = PurchaseReturn::findOrFail($id);
        if ($return != null) {
            PurchaseReturnDetail::where('return_id', $id)->delete();
            $return->delete();
            flash(translate('Return has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function get_purchase_order_item(Request $request)
    {
        $purchase_details = Purchase_order::leftjoin('purchase_order_item', 'purchase_order.id', 'purchase_order_item.po_id')
            ->leftjoin('products', 'purchase_order_item.product_id', 'products.id')
            ->select('purchase_order.purchase_no', 'products.name', 'purchase_order_item.*')
            ->where('po_id', $request->purchase_id)->get();
        return $purchase_details;
    }

    public function find_purchase_order_item(Request $request)
    {
        $product = Purchase_order_item::leftjoin('products', 'purchase_order_item.product_id', 'products.id')
            ->select('purchase_order_item.*', 'products.name')
            ->where('po_id', $request->purchase_id)->get();

        $html = '<option value="">' . translate("Select Product") . '</option>';

        foreach ($product as $row) {
            $html .= '<option value="' . $row->product_id . '">' . $row->name . '</option>';
        }
        echo json_encode($html);
    }

    public function get_puracher_details(Request $request)
    {
        $data = Purchase_order_item::where(['product_id'=>$request->product_id])
        ->where(['po_id'=>$request->purchase_id])->first();
        return $data;
    }
}
