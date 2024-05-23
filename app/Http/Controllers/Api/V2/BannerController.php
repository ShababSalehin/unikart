<?php

namespace App\Http\Controllers\Api\V2;

use App\Blog;
use App\Http\Resources\V2\BannerCollection;
use App\Http\Resources\V2\BlogCollection;
use App\Http\Resources\V2\HomeBannerThreeCollection;
use App\Http\Resources\V2\HomeBannerTwoCollection;

class BannerController extends Controller
{

    public function index()
    {
        return new BannerCollection(json_decode(get_setting('home_banner1_images'), true));
    }

    public function home_banner_two()
    {

        return new HomeBannerTwoCollection(json_decode(get_setting('home_banner2_images'), true));
    }

    public function home_banner_three()
    {

        return new HomeBannerThreeCollection(json_decode(get_setting('home_banner3_images'), true));
    }

    public function blog()
    {
        $blogs = Blog::latest()->take(4)->get();
        return new BlogCollection($blogs);
    }
}
