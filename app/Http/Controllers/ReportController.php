<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\OrderDetail;
use App\CommissionHistory;
use App\OpeningStock;
use App\Order;
use App\Wallet;
use App\Seller;
use App\User;
use App\Search;
use App\Wishlist;
use Auth;
use DB;
use App\Product_stock_close;
use App\Purchase_order_item;

class ReportController extends Controller
{


    public function orderReport(Request $request)
    {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $date = $request->date;
        $sort_search = null;
        $sort_search2 = null;
        $from_order_no = null;
        $to_order_no = null;
        $seller = null;
        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->groupBy('orders.combined_order_id')

            ->where('orders.delivery_status', 'confirmed');
        // dd($orders);

        if ($request->has('from_order_no') && $request->has('to_order_no')) {
            $from_order_no = $request->from_order_no;
            $to_order_no = $request->to_order_no;
            if (!empty($from_order_no) && !empty($to_order_no)) {
                $orders = $orders->whereBetween('orders.id', [$from_order_no, $to_order_no]);
            }
        }



        if ($request->has('search2')) {
            $sort_search2 = $request->search2;
            $orders = $orders->where('shipping_address', 'like', '%' . $sort_search2 . '%');
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }

        $filter_seller = '';
        if (!empty($request->seller_id)) {
            $filter_seller = $request->seller_id;
            $orders  = $orders->where('orders.seller_id', $filter_seller);
        }

        $orders = $orders->get();
        //dd($orders);
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        return view('backend.reports.pending_order', compact('orders', 'from_order_no', 'to_order_no', 'date', 'start_date', 'end_date', 'filter_seller'));
    }


    public function pendingOrderProductStockReport(Request $request)
    {

        $start_date = null;
        $end_date = null;
        $date = $request->date;
        $sort_search = null;
        $sort_search2 = null;
        $from_order_no = null;
        $to_order_no = null;
        $seller = null;
        $products = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->select(
                'products.id as product_id',
                'products.name as product_name','qty',
                'products.current_stock as current_stock',
                DB::raw('sum(quantity) AS quantity'),
                
            )
            ->groupBy('products.id');



        if ($request->has('from_order_no') && $request->has('to_order_no')) {
            $from_order_no = $request->from_order_no;
            $to_order_no = $request->to_order_no;
            if (!empty($from_order_no) && !empty($to_order_no)) {
                $products = $products->whereBetween('orders.id', [$from_order_no, $to_order_no]);
            }
        }





        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
            $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);
        }

        $filter_seller = '';
        $products->where('orders.delivery_status', ['pending']);
        $products = $products->get();
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));
        }
        return view('backend.reports.pending_order_product_inventory', compact('products', 'from_order_no', 'to_order_no', 'date', 'start_date', 'end_date', 'filter_seller'));
    }



    public function salesReport(Request $request)
    {

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $date = $request->date;
        $sort_search = null;
        $sort_search2 = null;
        $seller = null;

        $orders = Order::orderBy('orders.created_at', 'ASC')
        ->whereNotIn('orders.delivery_status',['cancelled']);
        
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }

        if ($request->has('search2')) {
            $sort_search2 = $request->search2;
            $orders = $orders->where('shipping_address', 'like', '%' . $sort_search2 . '%');
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $filter_seller = '';
        if (!empty($request->seller_id)) {
            $filter_seller = $request->seller_id;
            $orders  = $orders->where('orders.seller_id', $filter_seller);
        }

        $orders = $orders->whereBetween('orders.created_at', [$start_date, $end_date]);
        $orders = $orders->get();
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        return view('backend.reports.sales', compact('orders', 'sort_search', 'sort_search2', 'date', 'start_date', 'end_date', 'filter_seller'));
    }



    public function salesCouponDiscountReport(Request $request)
    {

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $date = $request->date;

        $seller = null;
        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->where('orders.delivery_status', 'delivered');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }

        $filter_seller = '';
        if (!empty($request->seller_id)) {
            $filter_seller = $request->seller_id;
            $orders  = $orders->where('orders.seller_id', $filter_seller);
        }

        $orders = $orders->whereBetween('orders.created_at', [$start_date, $end_date]);
        $orders = $orders->get();

        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        return view('backend.reports.salesCouponDiscount', compact('orders',  'date', 'start_date', 'end_date', 'filter_seller'));
    }

    public function topSalesReport(Request $request)
    {
        $top = 20;
        $order_by = "quantity";
        $shop_or_product = "Shop";
        $sort_by = null;
        $pro_sort_by = null;
        $city_id = null;
        $start_date = '';
        $end_date = '';
        DB::enableQueryLog();

        if (!empty($request->shop_or_product)) {
            $shop_or_product = $request->shop_or_product;
        }

        if (!empty($request->top)) {
            $top = $request->top;
        }

        if (!empty($request->order_by)) {
            $order_by = $request->order_by;
        }

        if ($shop_or_product == "Shop") {
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->whereNotNull('shops.user_id')
                ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')

                ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                ->select(
                    'products.name as product_name',
                    'categories.name as category_name',
                    'shops.user_id',
                    'shops.name as shop_name',
                    'shops.contact_person',
                    'shops.contact_number',
                    DB::raw('sum(order_details.price) AS price'),
                    DB::raw('sum(order_details.due_to_seller) AS total_due_to_seller'),
                    DB::raw('sum(order_details.unikart_earning) AS total_unikart_earning'),
                    DB::raw('sum(order_details.shipping_cost) AS total_shipping_cost'),
                    DB::raw('sum(quantity) AS quantity')
                )
                ->groupBy('shops.id')->orderBy($order_by, 'desc')->limit($top);
        } else {
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
                ->whereNotNull('shops.user_id')
                ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                ->select(
                    'products.name as product_name',
                    'categories.name as category_name',
                    'shops.user_id',
                    'shops.name as shop_name',
                    DB::raw('sum(order_details.price) AS price'),
                    DB::raw('sum(order_details.due_to_seller) AS total_due_to_seller'),
                    DB::raw('sum(order_details.unikart_earning) AS total_unikart_earning'),
                    DB::raw('sum(order_details.shipping_cost) AS total_shipping_cost'),
                    DB::raw('sum(quantity) AS quantity')
                )
                ->groupBy('products.id')->orderBy($order_by, 'desc')->limit($top);
        }



        if (!empty($request->city_id)) {
            $city_id = $request->city_id;
            $products = $products->where('shops.citi_id', $city_id);
        }


        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = date('Y-m-d', strtotime($request->end_date . ' +1 day'));
            if ($shop_or_product == "Shop") {
                $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])

                    ->select('products.name as product_name', 'shops.user_id', 'shops.name as shop_name', 'categories.name as category_name', 'shops.contact_person', 'shops.contact_number', DB::raw('sum(order_details.price) AS price'), DB::raw('sum(quantity) AS quantity'))->groupBy('shops.id')->orderBy($order_by, 'desc')->limit($top);
            } else {
                $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])

                    ->select('products.name as product_name', 'shops.user_id', 'shops.name as shop_name', 'categories.name as category_name', DB::raw('sum(order_details.price) AS price'), DB::raw('sum(quantity) AS quantity'))->groupBy('products.id')->orderBy($order_by, 'desc')->limit($top);
            }
        }
        $products->where('order_details.delivery_status', ['delivered']);
        $products = $products->get();
        // dd($products);
        if (!empty($request->end_date))
            $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        return view('backend.reports.top_sales', compact('products', 'city_id', 'shop_or_product', 'top', 'order_by', 'sort_by', 'pro_sort_by', 'start_date', 'end_date'));
    }

    public function TopSalwDetailsShow($shopuserid, $profit)
    {

        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->leftjoin('order_details', 'order_details.order_id', '=', 'orders.id')
            ->leftjoin('products', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.delivery_status', 'delivered')
            ->where('order_details.seller_id', $shopuserid)
            ->select('products.name as productname', 'users.name as username', 'orders.id', 'orders.code', 'orders.created_at', 'users.phone', 'order_details.quantity', 'order_details.price');
        $orders = $orders->get();
        return view('backend.reports.top_sale_details', compact('orders', 'profit'));
    }

    public function productWiseSalesDetails(Request $request)
    {

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $pro_sort_by = null;
        DB::enableQueryLog();

        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->leftjoin('order_details', 'order_details.order_id', '=', 'orders.id')
            ->leftjoin('users', 'users.id', '=', 'orders.user_id')
            ->leftjoin('shops', 'shops.user_id', '=', 'orders.seller_id')
            ->where('orders.delivery_status', 'delivered')
            ->select('users.name as username', 'shops.name as seller_name', 'orders.*', 'users.phone');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
        } else {
            $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
        }

        $orders = $orders->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);

        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $orders = $orders->where('order_details.product_id', $pro_sort_by);
        }

        $orders = $orders->get();


        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));

        return view('backend.reports.product_wise_sales_details', compact('orders', 'start_date', 'end_date', 'pro_sort_by'));
    }



    public function coupon_report_details(Request $request)
    {

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $pro_sort_by = null;
        $shop_sort_by = null;
        DB::enableQueryLog();

        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->leftjoin('order_details', 'order_details.order_id', '=', 'orders.id')
            ->leftjoin('coupon_usages', 'coupon_usages.order_id', 'orders.id')
            ->leftjoin('coupons', 'coupons.id', 'coupon_usages.coupon_id')
            ->leftjoin('users', 'users.id', '=', 'orders.user_id')
            ->leftjoin('shops', 'shops.user_id', '=', 'orders.seller_id')
            ->where('orders.delivery_status', 'delivered')
            ->select(
                'users.name as username',
                'shops.name as seller_name',
                'orders.*',
                'users.phone',
                'coupons.code as coupon_code',
                'coupons.remarks'
            )
            ->where('orders.coupon_discount', '>', '0');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
        } else {
            $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
        }

        $orders = $orders->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);

        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $orders = $orders->where('order_details.product_id', $pro_sort_by);
        }

        if (!empty($request->shop_name)) {
            $shop_sort_by = $request->shop_name;
            $orders = $orders->where('shops.name', $shop_sort_by);
        }

        $orders = $orders->get();


        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));

        return view('backend.reports.coupon_wise_sales_details', compact('orders', 'start_date', 'end_date', 'pro_sort_by', 'shop_sort_by'));
    }


    public function topCustomersReport(Request $request)
    {
        $top = 10;
        $order_by = "amount";
        $phone = "";

        $start_date = '';
        $end_date = '';

        if (!empty($request->top)) {
            $top = $request->top;
        }

        if (!empty($request->order_by)) {
            $order_by = $request->order_by;
        }

        if (!empty($request->phone)) {
            $phone = $request->phone;
        }

        $qlog = DB::enableQueryLog();

        if ($order_by == "amount") {
            $topcustomers = User::leftJoinSub(
                function ($query) {
                    $query->select('user_id', DB::raw('sum(grand_total) as amount'))
                        ->from('orders')
                        ->where('delivery_status', '!=', 'cancelled') // Exclude cancelled orders from the sum
                        ->groupBy('user_id');
                },
                'order_amount',
                function ($join) {
                    $join->on('users.id', '=', 'order_amount.user_id');
                }
            )
                ->select(
                    'users.id as userid',
                    'users.name as customer_name',
                    'users.phone as customer_phone',
                    DB::raw('sum(CASE WHEN orders.delivery_status != "cancelled" THEN order_details.quantity ELSE 0 END) AS productquantity'), // Exclude cancelled orders from sum
                    DB::raw('IFNULL(order_amount.amount, 0) AS amount'),
                    //DB::raw('count(distinct orders.id) AS totalorder')
                    DB::raw('count(distinct CASE WHEN orders.delivery_status != "cancelled" THEN orders.id END) AS totalorder') // Exclude cancelled orders from count

                )
                ->leftJoin('orders', 'users.id', 'orders.user_id')
                ->leftJoin('order_details', 'orders.id', 'order_details.order_id')
                ->groupBy('users.id', 'users.name', 'users.phone', 'order_amount.amount')
                ->orderBy('amount', 'desc');
        } else {
            $topcustomers = User::leftJoinSub(function ($query) {
                $query->select('user_id', DB::raw('sum(grand_total) as amount'))
                    ->from('orders')
                    ->groupBy('user_id');
            }, 'order_amount', function ($join) {
                $join->on('users.id', '=', 'order_amount.user_id');
            })
                ->select(
                    'users.id as userid',
                    'users.name as customer_name',
                    'users.phone as customer_phone',
                    DB::raw('sum(CASE WHEN orders.delivery_status != "cancelled" THEN order_details.quantity ELSE 0 END) AS productquantity'), // Exclude cancelled orders from sum
                    DB::raw('IFNULL(order_amount.amount, 0) AS amount'),
                    //DB::raw('count(distinct orders.id) AS totalorder')
                    DB::raw('count(distinct CASE WHEN orders.delivery_status != "cancelled" THEN orders.id END) AS totalorder') // Exclude cancelled orders from count
                )
                ->leftJoin('orders', 'users.id', 'orders.user_id')
                ->leftJoin('order_details', 'orders.id', 'order_details.order_id')
                ->groupBy('users.id', 'users.name', 'users.phone', 'order_amount.amount')
                ->orderBy('totalorder', 'desc');
        }

        if (!empty($request->phone)) {
            $topcustomers->where('users.phone', $request->phone);
        }

        $topcustomers->limit($top);

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
            $topcustomers->whereBetween('orders.created_at', [strtotime($start_date), strtotime($end_date)]);
        }

        $topcustomers = $topcustomers->get();
        if (!empty($start_date) && !empty($end_date)) {
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));
        }

        return view('backend.reports.top_customers', compact('topcustomers', 'top', 'order_by', 'phone', 'start_date', 'end_date'));
    }



    public function topCustomersReport_old(Request $request)
    {
        $top = 10;
        $order_by = "amount";
        $phone = "";

        // $start_date = date('Y-m-01');
        // $end_date = date('Y-m-t');

        $start_date = '';
        $end_date = '';

        if (!empty($request->top)) {
            $top = $request->top;
        }

        if (!empty($request->order_by)) {
            $order_by = $request->order_by;
        }


        if (!empty($request->phone)) {
            $phone = $request->phone;
        }

        $qlog = DB::enableQueryLog();

        if ($order_by == "amount") {
            if (!empty($request->phone)) {
                $topcustomers = User::leftjoin('orders', 'users.id', 'orders.user_id')
                    ->leftjoin('order_details', 'orders.id', 'order_details.order_id')
                    ->select(
                        'users.id as userid',
                        'users.name as customer_name',
                        'users.phone as customer_phone',
                        DB::raw('sum(order_details.quantity) AS productquantity'),
                        DB::raw('sum(orders.grand_total) AS amount'),
                        DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('amount', 'desc')
                    ->where('orders.delivery_status', 'delivered')
                    ->where('users.phone', $request->phone)
                    ->limit($top);
            } else {
                $topcustomers = User::leftjoin('orders', 'users.id', 'orders.user_id')
                    ->leftjoin('order_details', 'orders.id', 'order_details.order_id')
                    ->select(
                        'users.id as userid',
                        'users.name as customer_name',
                        'users.phone as customer_phone',
                        DB::raw('sum(order_details.quantity) AS productquantity'),
                        DB::raw('sum(orders.grand_total) AS amount'),
                        DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('amount', 'desc')
                    ->where('orders.delivery_status', 'delivered')
                    ->limit($top);
            }
        } else {
            if (!empty($request->phone)) {
                $topcustomers = User::leftjoin('orders', 'users.id', 'orders.user_id')
                    ->leftjoin('order_details', 'orders.id', 'order_details.order_id')
                    ->select(
                        'users.id as userid',
                        'users.name as customer_name',
                        'users.phone as customer_phone',
                        DB::raw('sum(order_details.quantity) AS productquantity'),
                        DB::raw('sum(orders.grand_total) AS amount'),
                        DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('totalorder', 'desc')
                    ->where('orders.delivery_status', 'delivered')
                    ->where('users.phone', $request->phone)
                    ->limit($top);
            } else {
                $topcustomers = User::leftjoin('orders', 'users.id', 'orders.user_id')
                    ->leftjoin('order_details', 'orders.id', 'order_details.order_id')
                    ->select(
                        'users.id as userid',
                        'users.name as customer_name',
                        'users.phone as customer_phone',
                        DB::raw('sum(order_details.quantity) AS productquantity'),
                        DB::raw('sum(orders.grand_total) AS amount'),
                        DB::raw('count(orders.user_id) AS totalorder'),
                    )->groupBy('users.id')->orderBy('totalorder', 'desc')
                    ->where('orders.delivery_status', 'delivered')
                    ->limit($top);
            }
        }


        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
            $topcustomers = $topcustomers->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);
        }

        $topcustomers = $topcustomers->get();
        if (!empty($start_date) && !empty($end_date)) {
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));
        }


        return view('backend.reports.top_customers', compact('topcustomers', 'top', 'order_by', 'phone', 'start_date', 'end_date'));
    }


    public function topCustomerDetailsShow($customerid = false, $start_date = false, $end_date = false)
    {
        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->leftjoin('order_details', 'order_details.order_id', '=', 'orders.id')
            ->leftjoin('products', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('users', 'users.id', '=', 'orders.user_id')
            ->whereIn('orders.delivery_status', ['delivered', 'picked_up']) // Include both delivered and picked_up orders
            ->where('orders.user_id', $customerid);

        if ($start_date > 0 && $end_date > 0) {
            $orders = $orders->whereBetween('orders.created_at', [strtotime($start_date), strtotime($end_date)]);
        }

        $orders = $orders->select('products.name as productname', 'users.name as username', 'orders.id', 'orders.code', 'orders.created_at', 'users.phone', 'order_details.quantity', 'order_details.price')
            ->get();

        return view('backend.reports.top_customer_details', compact('orders', 'start_date', 'end_date'));
    }


    public function topCustomerDetailsShow_old($customerid = false, $start_date = false, $end_date = false)
    {

        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->leftjoin('order_details', 'order_details.order_id', '=', 'orders.id')
            ->leftjoin('products', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.delivery_status', 'delivered')
            ->where('orders.user_id', $customerid)
            ->select('products.name as productname', 'users.name as username', 'orders.id', 'orders.code', 'orders.created_at', 'users.phone', 'order_details.quantity', 'order_details.price');
        if ($start_date > 0 && $end_date > 0) {
            $orders = $orders->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)]);
        }

        $orders = $orders->get();
        return view('backend.reports.top_customer_details', compact('orders', 'start_date', 'end_date'));
    }


    public function topCustomersReport_(Request $request)
    {
        $top = 20;
        $order_by = "quantity";
        $shop_or_product = "Shop";
        $sort_by = null;
        $pro_sort_by = null;
        $city_id = null;
        $start_date = '';
        $end_date = '';
        DB::enableQueryLog();

        if (!empty($request->shop_or_product)) {
            $shop_or_product = $request->shop_or_product;
        }

        if (!empty($request->top)) {
            $top = $request->top;
        }

        if (!empty($request->order_by)) {
            $order_by = $request->order_by;
        }

        if ($shop_or_product == "Shop") {
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->whereNotNull('shops.user_id')
                ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')

                ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                ->select(
                    'products.name as product_name',
                    'categories.name as category_name',
                    'shops.user_id',
                    'shops.name as shop_name',
                    'shops.contact_person',
                    'shops.contact_number',
                    DB::raw('sum(order_details.price) AS price'),
                    DB::raw('sum(order_details.due_to_seller) AS total_due_to_seller'),
                    DB::raw('sum(order_details.unikart_earning) AS total_unikart_earning'),
                    DB::raw('sum(order_details.shipping_cost) AS total_shipping_cost'),
                    DB::raw('sum(quantity) AS quantity')
                )
                ->groupBy('shops.id')->orderBy($order_by, 'desc')->limit($top);
        } else {
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
                ->whereNotNull('shops.user_id')
                ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                ->select(
                    'products.name as product_name',
                    'categories.name as category_name',
                    'shops.user_id',
                    'shops.name as shop_name',
                    DB::raw('sum(order_details.price) AS price'),
                    DB::raw('sum(order_details.due_to_seller) AS total_due_to_seller'),
                    DB::raw('sum(order_details.unikart_earning) AS total_unikart_earning'),
                    DB::raw('sum(order_details.shipping_cost) AS total_shipping_cost'),
                    DB::raw('sum(quantity) AS quantity')
                )
                ->groupBy('products.id')->orderBy($order_by, 'desc')->limit($top);
        }



        if (!empty($request->city_id)) {
            $city_id = $request->city_id;
            $products = $products->where('shops.citi_id', $city_id);
        }


        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = date('Y-m-d', strtotime($request->end_date . ' +1 day'));
            //$products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])->select('products.name as product_name','categories.name as category_name',DB::raw('sum(order_details.price) AS price'),DB::raw('sum(quantity) AS quantity'),DB::raw('count(product_id) AS num_of_sale'))->groupBy('products.id')->orderBy('num_of_sale', 'desc');
            if ($shop_or_product == "Shop") {
                $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])

                    ->select('products.name as product_name', 'shops.user_id', 'shops.name as shop_name', 'categories.name as category_name', 'shops.contact_person', 'shops.contact_number', DB::raw('sum(order_details.price) AS price'), DB::raw('sum(quantity) AS quantity'))->groupBy('shops.id')->orderBy($order_by, 'desc')->limit($top);
            } else {
                $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])

                    ->select('products.name as product_name', 'shops.user_id', 'shops.name as shop_name', 'categories.name as category_name', DB::raw('sum(order_details.price) AS price'), DB::raw('sum(quantity) AS quantity'))->groupBy('products.id')->orderBy($order_by, 'desc')->limit($top);
            }
        }
        $products->where('order_details.delivery_status', ['delivered']);
        $products = $products->get();
        // dd($products);
        if (!empty($request->end_date))
            $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        return view('backend.reports.top_customers', compact('products', 'city_id', 'shop_or_product', 'top', 'order_by', 'sort_by', 'pro_sort_by', 'start_date', 'end_date'));
    }

    public function stock_report_old(Request $request)
    {

        $sort_by = null;
        $pro_sort_by = null;
        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->orderBy('products.current_stock', 'desc');

        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }

        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }

        if (!empty($request->brand_id)) {
            $pro_sort_by = $request->brand_id;
            $products = $products->where('brands.id', $pro_sort_by);
        }

        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }

        $products = $products->select('products.*', 'categories.name as category_name', 'shops.name as shopsname')->get();
        // dd($products);
        return view('backend.reports.stock_report', compact('products', 'sort_by', 'pro_sort_by'));
    }


    public function stock_report(Request $request)
    {
        $sort_by = null;
        $pro_sort_by = null;

        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->orderBy('products.current_stock', 'desc');


        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }

        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }

        if (!empty($request->brand_id)) {
            $pro_sort_by = $request->brand_id;
            $products = $products->where('brands.id', $pro_sort_by);
        }

        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }

        $products = $products->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->whereIn('order_details.delivery_status', ['Pending', 'Confirmed', 'Picked Up', 'On the Way', 'Delivered'])
            ->select(
                'products.*',
                'categories.name as category_name',
                'shops.name as shopsname',
                DB::raw('sum(order_details.quantity) AS sales_quantity')
            )->groupBy('products.id')->get();

        return view('backend.reports.stock_report', compact('products', 'sort_by', 'pro_sort_by'));
    }




    public function stock_ledger_report(Request $request)
    {

        $sort_by = null;
        $pro_sort_by = null;


        if (!empty($request->from_date) && !empty($request->to_date)) {
            $from_date = date('Y-m-d', strtotime($request->from_date));
            $to_date = date('Y-m-d', strtotime($request->to_date));
            $from_string_time = strtotime($request->from_date);
            $to_string_time = strtotime($request->to_date);
        } else {
            $from_date = date('Y-m-01');
            $to_date = date('Y-m-t');
            $from_string_time = strtotime($from_date);
            $to_string_time = strtotime($to_date);
        }



        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->orderBy('products.current_stock', 'desc')
            ->limit(5);


        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }



        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }



        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
        }




        $products = $products->select('products.*', 'categories.name as category_name', 'shops.name as shopsname')->get();

        foreach ($products as $key => $value) {

            $o_purchase_info = array();
            $o_sale_info = array();


            $purchase_info = array();
            $sale_info = array();


            $opening_qty = 0;
            $opening_amount = 0;
            $purchase_qty = 0;
            $purchase_amount = 0;
            $sale_qty = 0;
            $sale_amount = 0;


            if (!empty($sort_by)) {
                $o_p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where po.date<'$from_date' and po.wearhouse_id=$sort_by and poi.product_id=" . $value['id'];
            } else {
                $o_p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where po.date<'$from_date' and poi.product_id=" . $value['id'];
            }
            $o_purchase_info = DB::select($o_p_sql);

            if (!empty($o_purchase_info)) {
                $o_purchase_qty = $o_purchase_info[0]->total_purchase_qty;
                $o_purchase_amount = $o_purchase_info[0]->total_purchase_amount;
            } else {
                $o_purchase_qty = 0;
                $o_purchase_amount = 0;
            }


            if (!empty($sort_by)) {
                $o_s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where o.date<$from_string_time and o.warehouse=$sort_by and od.delivery_status='delivered' and product_id=" . $value['id'];
            } else {
                $o_s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where o.date<$from_string_time and od.delivery_status='delivered' and product_id=" . $value['id'];
            }
            $o_sale_info = DB::select($o_s_sql);

            if (!empty($o_sale_info)) {
                $o_sale_qty = $o_sale_info[0]->total_sale_qty;
                $o_sale_amount = $o_sale_info[0]->total_sale_amount;
            } else {
                $o_sale_qty = 0;
                $o_sale_amount = 0;
            }





            $products[$key]->opening_stock_qty = $opening_qty = $o_purchase_qty - ($o_sale_qty);
            $products[$key]->opening_stock_amount = $opening_amount = $o_purchase_amount - ($o_sale_amount);




            if (!empty($sort_by)) {
                $p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where (po.date>='$from_date' and po.date<='$to_date') and po.wearhouse_id=$sort_by and poi.product_id=" . $value['id'];
            } else {
                $p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where (po.date>='$from_date' and po.date<='$to_date') and poi.product_id=" . $value['id'];
            }
            $purchase_info = DB::select($p_sql);

            if (!empty($sort_by)) {
                $s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where (o.date>=$from_string_time and o.date<=$to_string_time) and o.warehouse=$sort_by and od.delivery_status='delivered' and product_id=" . $value['id'];
            } else {
                $s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where (o.date>=$from_string_time and o.date<=$to_string_time) and od.delivery_status='delivered' and product_id=" . $value['id'];
            }
            $sale_info = DB::select($s_sql);


            if (!empty($purchase_info)) {
                $products[$key]->purchase_qty = $purchase_qty = $purchase_info[0]->total_purchase_qty;
                $products[$key]->purchase_amount = $purchase_amount = $purchase_info[0]->total_purchase_amount;
            } else {
                $products[$key]->purchase_qty = $purchase_qty = 0;
                $products[$key]->purchase_amount = $purchase_amount = 0;
            }

            if (!empty($sale_info)) {
                $products[$key]->sale_qty = $sale_qty = $sale_info[0]->total_sale_qty;
                $products[$key]->sale_amount = $sale_amount = $sale_info[0]->total_sale_amount;
            } else {
                $products[$key]->sale_qty = 0;
                $products[$key]->sale_amount = 0;
            }


            $products[$key]->closing_qty = ($opening_qty + $purchase_qty) - ($sale_qty);
            $products[$key]->closing_amount = ($opening_amount + $purchase_amount) - ($sale_amount);
        }

        return view('backend.reports.stock_ledger_report', compact('products', 'sort_by', 'pro_sort_by', 'from_date', 'to_date'));
    }



    public function in_house_sale_report(Request $request)
    {
        $sort_by = null;
        $pro_sort_by = null;

        $start_date = date('Y-m-01 00:00:00');
        $end_date = date('Y-m-t 23:59:59');

        $start_date = '';
        $end_date = '';

        DB::enableQueryLog();
        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')

            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('num_of_sale', '>', '0')
            ->select(
                'products.name as product_name',
                'products.id as product_id',
                'brands.name as brand_name',
                'shops.name as shopname',
                'categories.name as category_name',
                DB::raw('sum(order_details.price) AS price'),
                DB::raw('sum(quantity) AS quantity'),
                DB::raw('count(product_id) AS num_of_sale')
            )->groupBy('products.id')->orderBy('num_of_sale', 'desc');

        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }

        if (!empty($request->brand_id)) {
            $pro_sort_by = $request->brand_id;
            $products = $products->where('brand_id', $pro_sort_by);
        }

        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }

        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }


        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = date('Y-m-d', strtotime($request->end_date . ' +1 day'));
            $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])
                ->select(
                    'products.name as product_name',
                    'categories.name as category_name',
                    'products.id as product_id',
                    'brands.name as brand_name',
                    'shops.name as shopname',
                    DB::raw('sum(order_details.price) AS price'),
                    DB::raw('sum(quantity) AS quantity'),
                    DB::raw('count(product_id) AS num_of_sale')
                )
                ->groupBy('products.id')->orderBy('num_of_sale', 'desc');
        }

        $products->whereIn('order_details.delivery_status', ['pending', 'confirmed', 'picked_up', 'on_the_way', 'delivered']);
        $products = $products->get();
        if (!empty($request->end_date))
            $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));


        return view('backend.reports.in_house_sale_report', compact('products', 'sort_by', 'pro_sort_by', 'start_date', 'end_date'));
    }

    public function picked_up_report(Request $request)
    {
        $sort_by = null;
        $pro_sort_by = null;
        $start_date = "";
        $end_date = "";
        DB::enableQueryLog();
        $products = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('num_of_sale', '>', '0')
            ->select(
                'products.name as product_name',
                'orders.code',
                'products.thumbnail_img',
                'shops.name',
                'shops.address',
                'shops.phone',
                DB::raw('sum(quantity) AS quantity'),
                DB::raw('count(product_id) AS num_of_sale')
            )->groupBy('products.id')->orderBy('num_of_sale', 'desc');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = date('Y-m-d', strtotime($request->end_date . ' +1 day'));
            $products = $products->whereBetween('orders.date', [strtotime($start_date), strtotime($end_date)])
                ->select(
                    'products.name as product_name',
                    'orders.code',
                    'shops.name',
                    'shops.address',
                    'shops.phone',
                    DB::raw('sum(quantity) AS quantity'),
                    DB::raw('count(product_id) AS num_of_sale')
                )->groupBy('products.id')->orderBy('num_of_sale', 'desc');
        }
        $products->where('order_details.delivery_status', ['confirmed']);
        $products = $products->get();
        //dd($products);
        if (!empty($request->end_date))
            $end_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        return view('backend.reports.picked_up_report', compact('products', 'sort_by', 'pro_sort_by', 'start_date', 'end_date'));
    }

    public function seller_sale_report(Request $request)
    {
        $sort_by = null;
        $sellers = Seller::orderBy('created_at', 'desc');
        if ($request->has('verification_status')) {
            $sort_by = $request->verification_status;
            $sellers = $sellers->where('verification_status', $sort_by);
        }
        $sellers = $sellers->paginate(10);
        return view('backend.reports.seller_sale_report', compact('sellers', 'sort_by'));
    }

    public function wish_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id')) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(10);
        return view('backend.reports.wish_report', compact('products', 'sort_by'));
    }

    public function customer_wishlist(Request $request)
    {

        $sort_by = null;
        $wishreports = Wishlist::join('products', 'products.id', '=', 'wishlists.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('users', 'users.id', '=', 'wishlists.user_id')
            ->select('users.id', 'users.name', 'users.phone', 'products.name as proname', 'categories.name as category_name');
        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
            $wishreports = $wishreports->where('category_id', $sort_by);
        }

        $wishreports = $wishreports->get();

        return view('backend.reports.customer_wishproduct', compact('wishreports', 'sort_by'));
    }

    public function user_search_report(Request $request)
    {
        $searches = Search::orderBy('count', 'desc')->paginate(10);
        return view('backend.reports.user_search_report', compact('searches'));
    }

    public function commission_history(Request $request)
    {
        $seller_id = null;
        $date_range = null;

        if (Auth::user()->user_type == 'seller') {
            $seller_id = Auth::user()->id;
        }
        if ($request->seller_id) {
            $seller_id = $request->seller_id;
        }

        $commission_history = CommissionHistory::orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $commission_history = $commission_history->where('created_at', '>=', $date_range1[0]);
            $commission_history = $commission_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($seller_id) {

            $commission_history = $commission_history->where('seller_id', '=', $seller_id);
        }

        $commission_history = $commission_history->paginate(10);
        if (Auth::user()->user_type == 'seller') {
            return view('frontend.user.seller.reports.commission_history_report', compact('commission_history', 'seller_id', 'date_range'));
        }
        return view('backend.reports.commission_history_report', compact('commission_history', 'seller_id', 'date_range'));
    }

    public function wallet_transaction_history(Request $request)
    {
        $user_id = null;
        $date_range = null;

        if ($request->user_id) {
            $user_id = $request->user_id;
        }

        $users_with_wallet = User::whereIn('id', function ($query) {
            $query->select('user_id')->from(with(new Wallet)->getTable());
        })->get();

        $wallet_history = Wallet::orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $wallet_history = $wallet_history->where('created_at', '>=', $date_range1[0]);
            $wallet_history = $wallet_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($user_id) {
            $wallet_history = $wallet_history->where('user_id', '=', $user_id);
        }

        $wallets = $wallet_history->paginate(10);

        return view('backend.reports.wallet_history_report', compact('wallets', 'users_with_wallet', 'user_id', 'date_range'));
    }


    public function income_report(Request $request)
    {
        $seller_id = null;
        $date_range = null;

        if (Auth::user()->user_type == 'seller') {
            $seller_id = Auth::user()->id;
        }
        if ($request->seller_id) {
            $seller_id = $request->seller_id;
        }
        $sellers = Seller::orderBy('created_at', 'desc');
        if ($request->seller_id) {
            $sellers = $sellers->where('user_id', $seller_id);
        }
        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            // $sellers = $sellers->where('created_at', '>=', $date_range1[0]);
            // $sellers = $sellers->where('created_at', '<=', $date_range1[1]);
        }


        $sellers = $sellers->paginate(10);

        //$result = DB::select("SELECT sl.id,sl.user_id,u.name,(SELECT SUM(price) FROM order_details WHERE seller_id=sl.user_id AND payment_status='paid' and refund_status=0) as price,(SELECT SUM(shipping_cost) FROM order_details  WHERE seller_id=sl.user_id AND payment_status='paid' and refund_status=0) as shipping_cost FROM sellers sl left join order_details od on od.seller_id=sl.user_id join users u on u.id=sl.user_id GROUP BY sl.user_id ORDER BY sl.created_at DESC");

        return view('backend.reports.seller_income_report', compact('sellers', 'seller_id', 'date_range'));
    }
    public function income_details_report(Request $request, $id)
    {
        $date_range = null;
        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            // $sellers = $sellers->where('created_at', '>=', $date_range1[0]);
            // $sellers = $sellers->where('created_at', '<=', $date_range1[1]);
        }

        $sellers = Order::where(['seller_id' => $id, 'delivery_status' => 'delivered'])
            ->orderBy('created_at', 'desc');
        $sellers = $sellers->paginate(10);
        //dd( $sellers);
        $seller_id = $id;
        return view('backend.reports.seller_income_details_report', compact('sellers', 'date_range', 'seller_id'));
    }

    public function income_order_details_report(Request $request, $id)
    {
        $date_range = null;
        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            // $sellers = $sellers->where('created_at', '>=', $date_range1[0]);
            // $sellers = $sellers->where('created_at', '<=', $date_range1[1]);
        }

        $sellers = Order::where(['seller_id' => $id, 'delivery_status' => 'delivered'])

            ->orderBy('created_at', 'desc');
        $sellers = $sellers->paginate(10);

        $orders = Order::orderBy('orders.created_at', 'ASC')
            ->leftjoin('order_details', 'order_details.order_id', '=', 'orders.id')
            ->leftjoin('products', 'products.id', '=', 'order_details.product_id')
            ->leftjoin('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.id', $id)
            ->select('products.name as productname', 'users.name as username', 'orders.code', 'orders.created_at', 'users.phone', 'order_details.quantity', 'order_details.price', 'order_details.shipping_type');

        $orders = $orders->get();
        $order_id = $id;
        return view('backend.reports.income_order_details_report', compact('orders', 'date_range', 'order_id'));
    }


    public function top_selling_area_report()
    {

        $states = DB::select("SELECT * FROM states");
        foreach ($states as $key => $state) {
            $stateId = $state->id;
            $orders = DB::select("SELECT COUNT(grand_total) AS total_orders,
            SUM(grand_total) AS total_amount FROM orders WHERE JSON_VALUE(shipping_address,
            '$.state_id') = '$stateId' GROUP BY JSON_VALUE(shipping_address,
            '$.state_id') ORDER BY total_amount DESC");
            if (isset($orders[0]->total_orders)) {
                $states[$key]->total_orders = $orders[0]->total_orders;
                $states[$key]->total_amount = $orders[0]->total_amount;
            } else {
                $states[$key]->total_orders = 0;
                $states[$key]->total_amount = 0;
            }
        }

        array_multisort(
            array_map(
                static function ($element) {
                    return $element->total_amount;
                },
                $states
            ),
            SORT_DESC,
            $states
        );
        return view('backend.reports.top_selling_area_report', compact('states'));
    }


    public function firstorder_customer_report()
    {

        $total_user = User::orderBy('id', 'desc')->count();

        $Total_First_Orders = Order::where('delivery_status', 'delivered')
            ->groupBy('user_id')->get()->count();

        $first_order2 = Order::where('is_first_order', 'Yes')
            ->where('delivery_status', 'delivered')->get()->count();

        $first_order_half_price = OrderDetail::whereRaw('(product_unit_price / 2) = unikart_discount')
            ->where('delivery_status', 'delivered')->get()->count();
         
            $first_order_half_price_d = OrderDetail::whereRaw('(product_unit_price / 2) = unikart_discount')
            ->where('delivery_status', 'delivered')->get()->pluck('order_id');
            
            $half_price_duplicate = Order::select('user_id')->whereIn('id',$first_order_half_price_d)->get()->pluck('user_id');

            $order2 = Order::select('user_id')->whereIn('user_id',$half_price_duplicate)
            ->where('delivery_status', 'delivered')->get();

            $duplicates = $order2->countBy('user_id')
            ->filter(function ($count) {
                return $count > 1;
            });

    $duplicates_first_order = $duplicates->count();

        $first_order_c20 = Order::leftjoin('coupon_usages', 'coupon_usages.user_id', 'orders.user_id')
            ->where('coupon_usages.coupon_id', 30)
            ->where('is_first_order', 'Yes')
            ->where('delivery_status', 'delivered')->get()->count();

        $sql = "SELECT user_id, COUNT(user_id) FROM orders GROUP BY user_id HAVING COUNT(user_id) > 1";
        $duplicate_order = count(DB::select($sql));

        return view('backend.reports.first_order_customer_report', compact('total_user', 'Total_First_Orders', 'first_order_c20', 'first_order_half_price', 'duplicate_order','duplicates_first_order'));
    }

    public function monthly_stock_ledger_report(Request $request) // Added by Tanem
    {


        ini_set('max_execution_time', 0);

        $category_id = '';
        $product_id = '';

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
            $startDate = date('Y-m-d', strtotime($request->from_date));
            $endDate = date('Y-m-d', strtotime($request->to_date));
        } else {
            $from_date = date('Y-m-01 00:00:00');
            $to_date = date('Y-m-t 23:59:59');
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        $products = Product::select(
            'products.*',
            'categories.id as cat_id',
            'categories.name as cat_name'
        )
            ->leftjoin('categories', 'products.category_id', 'categories.id');

        if (!empty($request->category_id) && !empty($request->product_id)) {
            $category_id = $request->category_id;
            $product_id = $request->product_id;

            $products = $products->where('categories.id', $request->category_id)
                ->where('products.id', $request->product_id)->get();
        } elseif (!empty($request->category_id)) {
            $category_id = $request->category_id;
            $products = $products->where('categories.id', $request->category_id)->get();
        } else {
            $products = $products->get();
        }

        foreach ($products as $key => $value) {
            // Added Stock Part
            $opening_stocks = OpeningStock::where('product_id', $value->id)
                ->whereBetween('created_at', array($from_date, $to_date))->get();

            foreach ($opening_stocks as $openStock) {
                $products[$key]->opening_stock_qty += $openStock->qty;
                $products[$key]->opening_stock_amount += $openStock->qty * $openStock->price;
            }

            $purchases = Purchase_order_item::select(
                'purchase_order_item.id',
                'purchase_order_item.po_id',
                'purchase_order_item.product_id',
                'purchase_order_item.qty',
                'purchase_order_item.price',
                'purchase_order_item.amount',
                'purchase_order.status',
                'purchase_order.date'
            )
                ->leftjoin('purchase_order', 'purchase_order.id', '=', 'purchase_order_item.po_id')
                ->where('purchase_order.status', 2)->where('purchase_order_item.product_id', $value->id)
                ->whereBetween('purchase_order.date', array($startDate, $endDate))->get();

            foreach ($purchases as $purchase) {
                $products[$key]->purchase_qty += $purchase->qty;
                $products[$key]->purchase_amount += $purchase->qty * $purchase->price;
            }

            $orders = OrderDetail::select(
                'order_details.id',
                'order_details.order_id',
                'order_details.product_id',
                'order_details.price',
                'order_details.quantity as qty',
                'order_details.delivery_status',
                'order_details.created_at',
                'order_details.updated_at'
            )
                ->leftjoin('orders', 'orders.id', '=', 'order_details.order_id')
                ->whereNotIn('order_details.delivery_status', array('cancelled'))
                ->where('order_details.product_id', $value->id)
                ->whereBetween('order_details.created_at', array($from_date, $to_date))->get();

            foreach ($orders as $o_key => $o_value) {
                $products[$key]['sales_qty'] += $o_value->qty;
                $products[$key]['sales_amount'] += $o_value->price;
            }

            $purchase_item = $opening_stocks->merge($purchases);

            // Marge Minus Stock Product
            $sales = $orders;

            // FIFO Calculation
            foreach ($sales->toArray() as $sale_key => $sale) {
                $purAmount = 0;
                $balance = $sale['qty'];

                $detail = [];
                foreach ($purchase_item->toArray() as $pur_key => $pur) {
                    if ($balance != 0) {
                        if ($pur['qty'] <= $balance) {
                            $purchase_item[$pur_key]['qty'] = 0;
                            $temPur[] = $pur;
                            unset($purchase_item[$pur_key]);
                            $purAmount += $pur['qty'] * $pur['price'];
                            $detail[] = $pur['qty'] . "*" . $pur['price'] . "=" . $pur['qty'] * $pur['price'];
                            $balance -= $pur['qty'];
                        } else {
                            if ($pur['qty'] > $balance) {
                                $balance -= $pur['qty'];
                                $saleQty = $pur['qty'] - abs($balance);
                                $purchase_item[$pur_key]['qty'] = abs($balance);
                                if ($balance != 0) {
                                    $purAmount += $saleQty * $pur['price'];
                                    $detail[] = $saleQty . "*" . $pur['price'] . "=" . $saleQty * $pur['price'];
                                }
                                $balance = max(0, $balance);
                                break;
                            }
                        }
                    }
                }

                $sales[$sale_key]['details'] = $detail;
                $sales[$sale_key]['purAmt'] = $purAmount;
                $sales[$sale_key]['amount'] =  $sales[$sale_key]['qty'] * $sales[$sale_key]['price'];
                $sales[$sale_key]['gainLoss'] =  $sales[$sale_key]['amount'] - $sales[$sale_key]['purAmt'];
            }

            foreach ($purchase_item as $new_pur_value) {
                $products[$key]->closing_stock_qty += $new_pur_value->qty;
                $products[$key]->closing_stock_amount += $new_pur_value->qty * $new_pur_value->price;
            }
        }

        return view('backend.reports.monthly_stock_ledger_report', compact('products', 'category_id', 'product_id', 'from_date', 'to_date'));
    }
    public function getProducts(Request $request)
    {
        $products = Product::where('products.parent_id', '=', null)->where('category_id', $request->value)->get();
        return $products;
    }



    public function save_stock_closing(Request $request)
    {
        $sort_by = null;
        $pro_sort_by = null;

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $from_date = date('Y-m-d', strtotime($request->from_date));
            $to_date = date('Y-m-d', strtotime($request->to_date));
            $from_string_time = strtotime($request->from_date);
            $to_string_time = strtotime($request->to_date);
            $month = date('Y-m', strtotime($request->from_date));
        } else {
            $from_date = date('Y-m-01');
            $to_date = date('Y-m-t');
            $from_string_time = strtotime($from_date);
            $to_string_time = strtotime($to_date);
            $month = date('Y-m', strtotime($from_date));
        }



        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('shops', 'products.user_id', '=', 'shops.user_id')
            ->where('is_delete', null)
            ->orderBy('products.current_stock', 'desc');



        if (!empty($request->product_id)) {
            $pro_sort_by = $request->product_id;
            $products = $products->where('products.id', $pro_sort_by);
        }



        if (!empty($request->shop_id)) {
            $pro_sort_by = $request->shop_id;
            $products = $products->where('shops.id', $pro_sort_by);
        }



        if ($request->has('category_id') && !empty($request->category_id)) {
            $sort_by = $request->category_id;
        }




        $products = $products->select('products.*', 'categories.name as category_name', 'shops.name as shopsname')->get();

        // print_r($products);
        // exit;
        foreach ($products as $key => $value) {

            $o_purchase_info = array();
            $o_sale_info = array();


            $purchase_info = array();
            $sale_info = array();


            $opening_qty = 0;
            $opening_amount = 0;
            $purchase_qty = 0;
            $purchase_amount = 0;
            $sale_qty = 0;
            $sale_amount = 0;


            if (!empty($sort_by)) {
                // $o_p_sql="select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where po.date<'$from_date' and po.wearhouse_id=$sort_by and poi.product_id=".$value['id'];
                $o_p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where po.date<'$from_date' and poi.product_id=" . $value['id'];
            } else {
                $o_p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where po.date<'$from_date' and poi.product_id=" . $value['id'];
            }
            $o_purchase_info = DB::select($o_p_sql);

            if (!empty($o_purchase_info)) {
                $o_purchase_qty = $o_purchase_info[0]->total_purchase_qty;
                $o_purchase_amount = $o_purchase_info[0]->total_purchase_amount;
            } else {
                $o_purchase_qty = 0;
                $o_purchase_amount = 0;
            }


            if (!empty($sort_by)) {
                //  $o_s_sql="select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where o.date<$from_string_time and o.warehouse=$sort_by and od.delivery_status='delivered' and product_id=".$value['id'];
                $o_s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where o.date<$from_string_time and od.delivery_status='delivered' and product_id=" . $value['id'];
            } else {
                $o_s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where o.date<$from_string_time and od.delivery_status='delivered' and product_id=" . $value['id'];
            }
            $o_sale_info = DB::select($o_s_sql);

            if (!empty($o_sale_info)) {
                $o_sale_qty = $o_sale_info[0]->total_sale_qty;
                $o_sale_amount = $o_sale_info[0]->total_sale_amount;
            } else {
                $o_sale_qty = 0;
                $o_sale_amount = 0;
            }

            $products[$key]->opening_stock_qty = $opening_qty = $o_purchase_qty - ($o_sale_qty);
            $products[$key]->opening_stock_amount = $opening_amount = $o_purchase_amount - ($o_sale_amount);

            if (!empty($sort_by)) {
                //  $p_sql="select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where (po.date>='$from_date' and po.date<='$to_date') and po.wearhouse_id=$sort_by and poi.product_id=".$value['id'];
                $p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where (po.date>='$from_date' and po.date<='$to_date') and poi.product_id=" . $value['id'];
            } else {
                $p_sql = "select sum(poi.qty) as total_purchase_qty,sum(poi.amount) as total_purchase_amount from purchase_order_item poi left join purchase_order po on poi.po_id=po.id where (po.date>='$from_date' and po.date<='$to_date') and poi.product_id=" . $value['id'];
            }
            $purchase_info = DB::select($p_sql);

            if (!empty($sort_by)) {
                // $s_sql="select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where (o.date>=$from_string_time and o.date<=$to_string_time) and o.warehouse=$sort_by and od.delivery_status='delivered' and product_id=".$value['id'];
                $s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where (o.date>=$from_string_time and o.date<=$to_string_time) and od.delivery_status='delivered' and product_id=" . $value['id'];
            } else {
                $s_sql = "select sum(od.quantity) as total_sale_qty,sum(od.price) as total_sale_amount from order_details od left join orders o on od.order_id=o.id where (o.date>=$from_string_time and o.date<=$to_string_time) and od.delivery_status='delivered' and product_id=" . $value['id'];
            }

            $sale_info = DB::select($s_sql);


            if (!empty($purchase_info)) {
                $products[$key]->purchase_qty = $purchase_qty = $purchase_info[0]->total_purchase_qty;
                $products[$key]->purchase_amount = $purchase_amount = $purchase_info[0]->total_purchase_amount;
            } else {
                $products[$key]->purchase_qty = $purchase_qty = 0;
                $products[$key]->purchase_amount = $purchase_amount = 0;
            }

            if (!empty($sale_info)) {
                $products[$key]->sale_qty = $sale_qty = $sale_info[0]->total_sale_qty;
                $products[$key]->sale_amount = $sale_amount = $sale_info[0]->total_sale_amount;
            } else {
                $products[$key]->sale_qty = 0;
                $products[$key]->sale_amount = 0;
            }


            $products[$key]->closing_qty = $closing_stock_qty = ($opening_qty + $purchase_qty) - ($sale_qty);
            $products[$key]->closing_amount = $closing_stock_amount = ($opening_amount + $purchase_amount) - ($sale_amount);



            $pre_close_stock = Product_stock_close::where('product_id', $value['id'])
                ->where('month', $month)
                ->get();

            // print_r($pre_close_stock);
            // exit;


            if (!isset($pre_close_stock[0]->id)) {
                $item = new Product_stock_close();
                $item->product_id = $value['id'];
                // $item->variant = '';
                $item->opening_stock_qty = $opening_qty;
                $item->opening_stock_amount = $opening_amount;
                $item->purchase_qty = $purchase_qty;
                $item->purchase_amount = $purchase_amount;
                $item->sale_qty = $sale_qty;
                $item->sale_amount = $sale_amount;
                $item->damage_qty = 0;
                $item->damage_amount = 0;
                $item->closing_stock_qty = $closing_stock_qty;
                $item->closing_stock_amount = $closing_stock_amount;
                $item->month = $month;

                $item->save();
            } else {


                // $item = new Product_stock_close();
                $item = Product_stock_close::findOrFail($pre_close_stock[0]->id);
                $item->product_id = $value['id'];
                // $item->variant = '';
                $item->opening_stock_qty = $opening_qty;
                $item->opening_stock_amount = $opening_amount;
                $item->purchase_qty = $purchase_qty;
                $item->purchase_amount = $purchase_amount;
                $item->sale_qty = $sale_qty;
                $item->sale_amount = $sale_amount;
                $item->damage_qty = 0;
                $item->damage_amount = 0;
                $item->closing_stock_qty = $closing_stock_qty;
                $item->closing_stock_amount = $closing_stock_amount;
                $item->month = $month;

                $item->save();
            }
        }
        flash(translate('Stock closed successfully'))->success();
        return redirect()->route('stock_closing');
        //return view('backend.reports.stock_closing', compact('products', 'sort_by', 'pro_sort_by','from_date','to_date'));
    }
}
