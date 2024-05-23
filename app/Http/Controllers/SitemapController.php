<?php
  
namespace App\Http\Controllers;

use App\Blog;
use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Brand;

use Illuminate\Support\Facades\Response;
  
class SitemapController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index($value='')
    {
        $products = Product::where('published',1)->get();
        $categoryes = Category::all();
        $brands = Brand::all();

        
        return response()->view('sitemap.index',[
            'products' => $products,
            'categoryes' => $categoryes,
            'brands' => $brands,

           
    ])->header('Content-Type', 'text/xml');
    }

    public function products()
    {
        $products = Product::Where('published',1)->get();

        return response()->view('sitemap.products', [
            'products' => $products,
        ])->header('Content-Type', 'text/xml');
    }

    public function categories()
    {
        $categories = Category::all();
        return response()->view('sitemap.categories', [
            'categories' => $categories,
        ])->header('Content-Type', 'text/xml');
    }

    public function brands()
    {
        $brands = Brand::all();
        return response()->view('sitemap.brands', [
            'brands' => $brands,
        ])->header('Content-Type', 'text/xml');
    }

    public function blogs()
    {
        $blogs  = Blog::all();
        return response()->view('sitemap.blogs', [
            'blogs' => $blogs,
        ])->header('Content-Type', 'text/xml');
    }

   
}