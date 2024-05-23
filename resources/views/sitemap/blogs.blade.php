<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($blogs as $blog)
        <url>
            <loc>{{ url('/') }}/blog/{{ $blog->slug }}</loc>
            <lastmod>{{ date_format($blog->created_at,"Y-m-d") }}</lastmod>
        </url>
    @endforeach
</urlset>