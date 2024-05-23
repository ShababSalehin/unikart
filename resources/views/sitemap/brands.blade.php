<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($brands as $brand)
        <url>
            <loc>{{ url('/') }}/brand/{{ $brand->slug }}</loc>
            <lastmod>{{ date_format($brand->created_at,"Y-m-d") }}</lastmod>
            
        </url>
    @endforeach
</urlset>