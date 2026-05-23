<?php

namespace App\Support;

class CacheKeys
{
    public static function homeBanners(): string
    {
        return 'public.home.banners.v2';
    }

    public static function latestPosts(int $limit = 5): string
    {
        return "public.home.latest_posts.{$limit}";
    }

    public static function latestBooks(int $limit = 5): string
    {
        return "public.home.latest_books.{$limit}";
    }

    public static function allGenres(): string
    {
        return 'public.taxonomy.genres';
    }

    public static function allCategories(): string
    {
        return 'public.taxonomy.categories';
    }

    public static function categoriesWithPostCount(): string
    {
        return 'public.taxonomy.categories_with_count';
    }

    public static function aboutProfile(): string
    {
        return 'public.about.profile';
    }

    public static function allPublic(): array
    {
        return [
            self::homeBanners(),
            self::latestPosts(),
            self::latestBooks(),
            self::allGenres(),
            self::allCategories(),
            self::categoriesWithPostCount(),
            self::aboutProfile(),
        ];
    }

    public static function allSitemapRelated(): array
    {
        return array_merge(
            self::allPublic(),
            ['rss.items', 'sitemap.xml']
        );
    }
}
