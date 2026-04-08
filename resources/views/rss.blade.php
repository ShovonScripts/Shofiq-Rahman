{{-- IMPORTANT: save this file as UTF-8 **without BOM** --}}

<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>

{{-- Optional: pretty rendering in browsers (remove if you don’t have rss.xsl in /public) --}}
<?php echo '<?xml-stylesheet type="text/xsl" href="'.asset('rss.xsl')."\"?>\n"; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{{ config('app.name') }} — RSS</title>
    <link>{{ rtrim($siteUrl, '/') }}/</link>
    <description>{{ config('app.name') }} ফিড</description>
    <language>bn</language>

    {{-- Self link for validators/readers --}}
    <atom:link href="{{ rtrim($siteUrl, '/') }}/feed" rel="self" type="application/rss+xml" />

    <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
    <generator>Laravel + Inertia</generator>

    @foreach ($items as $item)
      <item>
        <title><![CDATA[{{ $item->title }}]]></title>
        <link>{{ rtrim($siteUrl, '/') }}/lekha/{{ $item->slug }}</link>
        <guid isPermaLink="true">{{ rtrim($siteUrl, '/') }}/lekha/{{ $item->slug }}</guid>
        <pubDate>{{ optional($item->published_at)->toRfc2822String() }}</pubDate>

        {{-- Use CDATA to avoid escaping issues; leave excerpt as plain text/HTML-lite --}}
        <description><![CDATA[{!! $item->excerpt !!}]]></description>
      </item>
    @endforeach
  </channel>
</rss>
