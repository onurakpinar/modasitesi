<?php

namespace App\Support\Seo;

use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Support\MediaUrl;
use App\Support\PublicContent;

class SeoBuilder
{
    public static function forHome(): SeoMeta
    {
        $siteName = SeoSettings::siteName();
        $description = SeoSettings::defaultDescription();
        $canonical = SeoSettings::absoluteUrl('/');

        $jsonLd = array_values(array_filter([
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $canonical,
                'description' => $description,
                'inLanguage' => 'tr-TR',
            ],
            self::organizationSchema(),
        ]));

        return new SeoMeta(
            title: SeoSettings::defaultTitle(),
            description: $description,
            canonical: $canonical,
            ogTitle: $siteName,
            ogDescription: $description,
            ogImage: SeoSettings::ogImageUrl(),
            jsonLd: $jsonLd,
        );
    }

    public static function forPostsIndex(?Category $category = null): SeoMeta
    {
        $title = $category
            ? $category->name.' Yazıları — '.SeoSettings::siteName()
            : 'Yazılar — '.SeoSettings::siteName();

        $description = $category?->description
            ?: 'Yayınlanmış moda yazılarını keşfedin.';

        $canonical = $category
            ? SeoSettings::absoluteUrl('/yazilar?kategori='.$category->slug)
            : route('posts.index');

        return self::baseMeta($title, $description, $canonical);
    }

    public static function forPost(Post $post): SeoMeta
    {
        $title = ($post->meta_title ?: $post->title).' — '.SeoSettings::siteName();
        $description = $post->meta_description ?: ($post->excerpt ?: SeoSettings::defaultDescription());
        $canonical = $post->canonical_url ?: route('posts.show', $post->slug);
        $image = MediaUrl::public($post->cover_image, $post->cover_image_fallback);

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
                self::breadcrumbItem(1, 'Ana Sayfa', route('home')),
                $post->category ? self::breadcrumbItem(2, $post->category->name, route('categories.show', $post->category->slug)) : null,
                self::breadcrumbItem($post->category ? 3 : 2, $post->title, $canonical),
            ])),
        ];

        $article = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $description,
            'image' => $image ? [$image] : null,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => $post->author?->name ? [
                '@type' => 'Person',
                'name' => $post->author->name,
            ] : null,
            'publisher' => self::publisherSchema(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical,
            ],
            'inLanguage' => 'tr-TR',
        ], fn ($value) => $value !== null && $value !== []);

        return new SeoMeta(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: $post->title,
            ogDescription: $description,
            ogImage: $image ?: SeoSettings::ogImageUrl(),
            jsonLd: [$article, $breadcrumb],
        );
    }

    public static function forCategory(Category $category): SeoMeta
    {
        $title = $category->name.' — '.SeoSettings::siteName();
        $description = $category->description ?: ($category->name.' kategorisindeki moda yazıları.');
        $canonical = route('categories.show', $category->slug);

        return self::baseMeta($title, $description, $canonical, breadcrumb: [
            self::breadcrumbItem(1, 'Ana Sayfa', route('home')),
            self::breadcrumbItem(2, $category->name, $canonical),
        ]);
    }

    public static function forTag(Tag $tag, int $publishedCount): SeoMeta
    {
        $title = $tag->name.' — '.SeoSettings::siteName();
        $description = $tag->name.' etiketli moda yazıları.';
        $canonical = route('tags.show', $tag->slug);
        $robots = $publishedCount >= 3 ? 'index, follow' : 'noindex, follow';

        return self::baseMeta($title, $description, $canonical, robots: $robots, breadcrumb: [
            self::breadcrumbItem(1, 'Ana Sayfa', route('home')),
            self::breadcrumbItem(2, 'Etiket', route('home')),
            self::breadcrumbItem(3, $tag->name, $canonical),
        ]);
    }

    public static function forAuthor(Author $author, int $publishedCount): SeoMeta
    {
        $title = $author->name.' — '.SeoSettings::siteName();
        $description = $author->short_bio ?: ($author->name.' tarafından yazılan moda içerikleri.');
        $canonical = route('authors.show', $author->slug);
        $robots = $publishedCount > 0 ? 'index, follow' : 'noindex, follow';

        return self::baseMeta($title, $description, $canonical, robots: $robots, breadcrumb: [
            self::breadcrumbItem(1, 'Ana Sayfa', route('home')),
            self::breadcrumbItem(2, 'Yazar', route('home')),
            self::breadcrumbItem(3, $author->name, $canonical),
        ]);
    }

    public static function forSearch(?string $query): SeoMeta
    {
        $title = filled($query)
            ? '“'.$query.'” araması — '.SeoSettings::siteName()
            : 'Arama — '.SeoSettings::siteName();

        return self::baseMeta(
            $title,
            'Moda yazılarında arama yapın.',
            route('search', filled($query) ? ['q' => $query] : []),
            robots: 'noindex, follow',
        );
    }

    public static function forPage(Page $page): SeoMeta
    {
        $title = ($page->meta_title ?: $page->title).' — '.SeoSettings::siteName();
        $description = $page->meta_description ?: SeoSettings::defaultDescription();
        $canonical = PublicContent::staticPageRouteName($page->slug)
            ? route(PublicContent::staticPageRouteName($page->slug))
            : SeoSettings::absoluteUrl('/');

        return self::baseMeta($title, $description, $canonical, breadcrumb: [
            self::breadcrumbItem(1, 'Ana Sayfa', route('home')),
            self::breadcrumbItem(2, $page->title, $canonical),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $breadcrumb
     */
    private static function baseMeta(
        string $title,
        string $description,
        string $canonical,
        string $robots = 'index, follow',
        ?array $breadcrumb = null,
    ): SeoMeta {
        $jsonLd = $breadcrumb ? [[
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumb,
        ]] : [];

        return new SeoMeta(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: $title,
            ogDescription: $description,
            ogImage: SeoSettings::ogImageUrl(),
            robots: $robots,
            jsonLd: $jsonLd,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function organizationSchema(): ?array
    {
        $siteName = SeoSettings::siteName();
        $logo = SeoSettings::publisherLogoUrl();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => SeoSettings::absoluteUrl('/'),
        ];

        if ($logo) {
            $schema['logo'] = $logo;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private static function publisherSchema(): array
    {
        $schema = [
            '@type' => 'Organization',
            'name' => SeoSettings::siteName(),
        ];

        if ($logo = SeoSettings::publisherLogoUrl()) {
            $schema['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private static function breadcrumbItem(int $position, string $name, string $url): array
    {
        return [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $url,
        ];
    }
}
