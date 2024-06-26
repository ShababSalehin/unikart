<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Cart;
use Illuminate\Http\Request;
class ShippingController extends Controller
{
    public function shipping_cost(Request $request)
    {
        $carts = Cart::where('user_id', $request->user_id)
            ->get();

        foreach ($carts as $key => $cartItem) {
            $cartItem->address_id = $request->address_id;
            $cartItem->save();
        }
        foreach ($carts as $key => $cartItem) {
            $cartItem['shipping_cost'] = getShippingCost($carts, $key);
        	$cartItem['unicart_shipping_cost'] = getUnikartShippingCost($carts, $key);//added by alauddin
            $cartItem->save();
        }

        //Total shipping cost $calculate_shipping

        $total_shipping_cost = Cart::where('user_id', $request->user_id)->sum('shipping_cost');

        return response()->json(['result' => true, 'shipping_type' => get_setting('shipping_type'), 'value' => convert_price($total_shipping_cost), 'value_string' => format_price($total_shipping_cost)], 200);
    }
}
