<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Category;
use App\Product;
use Cache;
use CoreComponentRepository;
use App\Utility\CategoryUtility;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
   
    public function admin_dashboard(Request $request)
    {   
        CoreComponentRepository::initializeCache();
        $root_categories = Category::where('level', 0)->get();

        $cached_graph_data = Cache::remember('cached_graph_data', 86400, function() use ($root_categories){
            $num_of_sale_data = null;
            $qty_data = null;
            foreach ($root_categories as $key => $category){
                $category_ids = \App\Utility\CategoryUtility::children_ids($category->id);
                $category_ids[] = $category->id;

                $products = Product::with('stocks')->whereIn('category_id', $category_ids)->get();
                $qty = 0;
                $sale = 0;
                foreach ($products as $key => $product) {
                    $sale += $product->num_of_sale;
                    foreach ($product->stocks as $key => $stock) {
                        $qty += $stock->qty;
                    }
                }
                $qty_data .= $qty.',';
                $num_of_sale_data .= $sale.',';
            }
            $item['num_of_sale_data'] = $num_of_sale_data;
            $item['qty_data'] = $qty_data;

            return $item;
        });

        return view('backend.dashboard', compact('root_categories', 'cached_graph_data'));
    }
    public function menu_update(Request $request)
    {
        $str1 = '';
        $str = "";
        $str1.='<div class="aiz-user-sidenav-wrap position-relative z-1 shadow-sm">';
        $str1.='<div class="aiz-user-sidenav rounded overflow-auto c-scrollbar-light pb-5 pb-xl-0">';
        $str1.='<div class="aiz-side-nav-wrap">';
        $str1.='<h4 class="h5 fs-16 mb-1 fw-600 ml-3">All Category</h4>';
        $str1.='<ul class="aiz-side-nav-list" id="main-menu" data-toggle="aiz-side-menu">';
        $str .= '<ul class="list-unstyled categories no-scrollbar py-2 mb-0 text-left">';
        foreach (Category::where('level', 0)->orderBy('order_level', 'desc')->get()->take(11) as $key => $category) {
            $str .= '<li class="category-nav-element" data-id="' . $category->id . '">';
            $str .= '<a href="' . route('products.category', $category->slug) . '" class="text-truncate text-reset py-2 px-3 d-block">';

            $str .= '<span class="cat-name">' . $category->getTranslation('name') . '</span>';
            $str .= '</a>';
            $str1.='<li class="aiz-side-nav-item">';
            $str1.='<a href="#" class="aiz-side-nav-link">';
            $str1.='<span class="aiz-side-nav-text">' . $category->getTranslation('name') . '</span>';
            $str1.='<span class="aiz-side-nav-arrow"></span>';
            $str1.='</a>';
            if (count(CategoryUtility::get_immediate_children_ids($category->id)) > 0) {

                $str .= ' <div class="sub-cat-menu c-scrollbar-light">';
                $str .= ' <ul class=" first-sub-ul list-unstyled categories no-scrollbar py-2 mb-0 text-left">';
                $str1.='<ul class="aiz-side-nav-list level-2">';
                foreach (CategoryUtility::get_immediate_children_ids($category->id) as $key => $first_level_id) {
                    if (count(CategoryUtility::get_immediate_children_ids($first_level_id)) > 0) {
                        $str .= '<li class="category-nav-element-sub" data-id="' . $category->id . '">';
                    }else{
                        $str .= '<li class="category-nav-element-last" data-id="' . $category->id . '">';
                    }
                    $str .= '<a href="' . route('products.category', Category::find($first_level_id)->slug) . '" class="text-truncate text-reset py-1 px-3 d-block">';

                    $str .= '<span class="cat-name">' . Category::find($first_level_id)->getTranslation('name') . '</span>';
                    $str .= '</a>';
                    $str1.='<li class="aiz-side-nav-item">';
                    $str1.='<a href="Javascript:" class="aiz-side-nav-link">';
                    $str1.='<span class="aiz-side-nav-text">' . Category::find($first_level_id)->getTranslation('name') . '</span>';
                    $str1.='<span class="aiz-side-nav-arrow"></span>';
                    $str1.='</a>';
                    if (count(CategoryUtility::get_immediate_children_ids($first_level_id)) > 0) {

                        $str .= '<ul class=" sub-cat-menu-grand list-unstyled categories no-scrollbar py-2 mb-0 text-left">';
                        $str1.='<ul class="aiz-side-nav-list level-3">';
                        foreach (CategoryUtility::get_immediate_children_ids($first_level_id) as $key => $second_level_id) {
                            $str .= '<li class="category-nav-element-last" data-id="' . $category->id . '">';
                            $str .= '<a href="' . route('products.category', Category::find($second_level_id)->slug) . '" class="text-truncate text-reset py-1 px-3 d-block">';

                            $str .= '<span class="cat-name">' . Category::find($second_level_id)->getTranslation('name') . '</span>';
                            $str .= '</a>';
                            $str .= '</li>';
                            $str1.='<li class="aiz-side-nav-item">';
                            $str1.='<a href="' . route('products.category', Category::find($second_level_id)->slug) . '" class="aiz-side-nav-link">';
                            $str1.=' <span class="aiz-side-nav-text" style="margin-left: 10px;">' . Category::find($second_level_id)->getTranslation('name') . '</span>';
                            $str1.='</a>';
                            $str1.='</li>';
                        }
                        $str .= '</ul>';
                        $str1 .= '</ul>';
                    }
                    $str .= '</li>';
                    $str1 .= '</li>';
                }
                $str .= '</ul>';
                $str1 .= '</ul>';

                $str .= ' </div>';
            }
            $str .= ' </li>';
            $str1 .= ' </li>';
        }
        $str .= ' </ul>';
        $str1 .= ' </ul>';
        $str1.='</div>';
        $str1.='</div>';
        $str1.='<div class="fixed-bottom d-xl-none bg-white border-top d-flex justify-content-between px-2" style="box-shadow: 0 -5px 10px rgb(0 0 0 / 10%);">';
        $str1.='<a class="btn btn-sm p-2 d-flex align-items-center" href="javascript:void(0)"><i class="las la-sign-out-alt fs-18 mr-2"></i><span></span>';
        $str1.='</a>';
        $str1.='<button class="btn btn-sm p-2 " data-toggle="class-toggle" data-backdrop="static"data-target=".aiz-mobile-cat-side-nav" data-same=".mobile-side-nav-thumb"><i class="las la-times la-2x"></i>';
        $str1.='</button>';
        $str1.='</div>';
        $str1.='</div>';
        file_put_contents('category_menu_static.php', $str.PHP_EOL);
        file_put_contents('category_mobile_menu_static.php', $str1.PHP_EOL);
        flash(translate('Menu Updated successfully'))->success();
        return back();
    }

    function clearCache(Request $request)
    {
    Artisan::call('optimize:clear');
    flash(translate('Cache cleared successfully'))->success();
    return back();
    }
}
