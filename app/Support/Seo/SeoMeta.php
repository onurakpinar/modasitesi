<?php

namespace App\Support\Seo;

final class SeoMeta
{
    /**
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $canonical,
        public readonly string $ogTitle,
        public readonly string $ogDescription,
        public readonly ?string $ogImage,
        public readonly string $twitterCard = 'summary_large_image',
        public readonly string $robots = 'index, follow',
        public readonly array $jsonLd = [],
    ) {}
}
