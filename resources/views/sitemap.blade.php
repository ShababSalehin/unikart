<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    @foreach ($products as $product)
        <sitemap>
            <loc>{{ url('/') }}/products/{{ $product->slug }}</loc>
            <lastmod>{{ date_format($product->created_at,"Y-m-d") }}</lastmod>
        </sitemap>
    @endforeach

    @foreach ($categoryes as $category)
        <sitemap>
            <loc>{{ url('/') }}/categories/{{ $category->slug }}</loc>
            <lastmod>{{ date_format($category->created_at,"Y-m-d") }}</lastmod>
        </sitemap>
    @endforeach

    @foreach ($brands as $brand)
        <sitemap>
            <loc>{{ url('/') }}/brands/{{ $category->slug }}</loc>
            <lastmod>{{ date_format($brand->created_at,"Y-m-d") }}</lastmod>
        </sitemap>
    @endforeach

</sitemapindex>