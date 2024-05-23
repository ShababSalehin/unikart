<?php

namespace App\Http\Controllers;
use Excel;
use Illuminate\Http\Request;
use App\DownloadPendingSalesReportModel; //added by alauddin
use App\DownloadPendingSalesProductStockReportModel; //added by alauddin
use App\DownloadSalesCouponDiscountReportModel; //added by alauddin
use App\DownloadProductWiseSalesReportModel; //added by alauddin
use App\DownloadsalesReportModel;
use App\DownloadSellersSalesReports;
use App\DownloadProductsStock;
use App\DownloadProdutsWiseSale;
use App\DownloadToBePickUp;
use App\DownloadWishReport;
use App\DownloadSearch;
use App\DownloadCommissionHistory;
use App\DownloadTopSale;
use App\DownloadTopCustomer; //added by alauddin

class DownloadReportController extends Controller
{

	public function sales_pending_order_ledger_export(Request $request){       
        return Excel::download(new DownloadPendingSalesReportModel($request->from_order_no,$request->to_order_no), 'confirmsalesledger.xlsx');
    }


	public function sales_pending_order_product_stock_ledger_export(Request $request){       
        return Excel::download(new DownloadPendingSalesProductStockReportModel($request->start_date,$request->end_date), 'pendingsalesproductstockledger.xlsx');
    }

	public function sales_coupon_discount_ledger_export(Request $request){       
        return Excel::download(new DownloadSalesCouponDiscountReportModel($request->seller_id,$request->start_date,$request->end_date), 'salescoupondiscountledger.xlsx');
    }


    public function top_sale_download(Request $request){
       
        return Excel::download(new DownloadTopSale($request->shop_or_product,$request->top,$request->order_by,$request->city_id,$request->shop_id,$request->start_date,$request->end_date), 'topsale.xlsx');
    }

	public function top_customer_download(Request $request){       
        return Excel::download(new DownloadTopCustomer($request->order_by,$request->top,$request->phone,$request->start_date,$request->end_date), 'topcustomer.xlsx');
    }


	public function productWiseSalesReport(Request $request){   
        return Excel::download(new DownloadProductWiseSalesReportModel($request->start_date,$request->end_date,$request->search,$request->date), 'ProductWiseSalesDetails.xlsx');
    }
	
    public function sales_ledger_export(Request $request){
       
        return Excel::download(new DownloadsalesReportModel($request->start_date,$request->end_date,$request->search,$request->date), 'salesledger.xlsx');
    }
    public function seller_sales_export(Request $request){
       
        return Excel::download(new DownloadSellersSalesReports($request->verification_status), 'sellersalesreport.xlsx');
    }
    public function products_stock_export(Request $request){
       
        return Excel::download(new DownloadProductsStock($request->category_id,$request->product_id,$request->brand_id,$request->shop_id), 'productstocks.xlsx');
    }
    public function productswise_sale_export(Request $request){
       
        return Excel::download(new DownloadProdutsWiseSale($request->category_id,$request->product_id,$request->brand_id,$request->shop_id,$request->start_date,$request->end_date), 'ProductWizeSale.xlsx');
    }
    public function tobepickup_report(Request $request){
       
        return Excel::download(new DownloadToBePickUp($request->start_date,$request->end_date), 'tobepickup.xlsx');
    }
    public function wishlist_report_download(Request $request){
       
        return Excel::download(new DownloadWishReport($request->category_id), 'WishlistReport.xlsx');
    }
    public function user_search_download(){
       
        return Excel::download(new DownloadSearch(), 'UserSearch.xlsx');
    }
    public function commission_history_download(Request $request){

        return Excel::download(new DownloadCommissionHistory($request->seller_id,$request->date_range), 'CommissionHistory.xlsx');
    }
}
