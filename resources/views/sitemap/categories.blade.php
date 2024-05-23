<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($categories as $category)
        <url>
            <loc>{{ url('/') }}/category/{{ $category->slug }}</loc>
            <lastmod>{{ date_format($category->created_at,"Y-m-d") }}</lastmod>
            
        </url>
    @endforeach
</urlset>