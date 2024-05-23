<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\AllBrandCampaignCollection;
use App\Http\Resources\V2\BrandFlashDealWithProducts;
use App\Http\Resources\V2\FlashDealCollection;
use App\Http\Resources\V2\FlashDealCollectionWithProducts;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use Illuminate\Http\Request;
class FlashDealController extends Controller
{
    public function index()
    {
        $flash_deals = FlashDeal::where('status', 1)->where('start_date',
         '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        return new FlashDealCollection($flash_deals);
    }

    public function products($id, Request $request){
        $flash_deal = FlashDeal::find($id);
        $products = FlashDealProduct::leftjoin('products','flash_deal_products.product_id','products.id')
        ->select('products.*')->where('flash_deal_products.flash_deal_id',$flash_deal->id)
        ->orderBy('flash_deal_products.id','desc');
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        return new ProductMiniCollection($products->paginate(36));
    }

    public function flash_deal_with_products(){
        $flash_deals_ids = FlashDeal::where('status', 1)->where('start_date', '<=',
         strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))
         ->whereNull('brand_id')->get();
        return new FlashDealCollectionWithProducts($flash_deals_ids);
    }

    public function brand_campaign(){
        $brandcampaigns = FlashDeal::where('status', 1)->where('campaign_type','Brand')
        ->where('end_date', '>=', strtotime(date('d-m-Y')))->orderBy('created_at','desc')->get();
        return new AllBrandCampaignCollection($brandcampaigns);
    }

    public function brand_campaign_products(Request $request){
        $flash_deal = FlashDeal::where('id',$request->id)->get();
       return new BrandFlashDealWithProducts($flash_deal);
    }

}
