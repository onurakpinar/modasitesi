<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $siteName }}</title>
        <link>{{ $siteUrl }}</link>
        <description>{{ $description }}</description>
        <language>tr</language>
        <atom:link href="{{ $feedUrl }}" rel="self" type="application/rss+xml" />

        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('posts.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('posts.show', $post->slug) }}</guid>
                <pubDate>{{ $post->published_at?->toRfc2822String() }}</pubDate>
                @if ($post->author)
                    @if ($post->author->email)
                        <author>{{ $post->author->email }} ({{ $post->author->name }})</author>
                    @else
                        <author>{{ $post->author->name }}</author>
                    @endif
                @endif
                @if ($post->excerpt)
                    <description><![CDATA[{{ $post->excerpt }}]]></description>
                @endif
            </item>
        @endforeach
    </channel>
</rss>
