@props(['posts', 'columns' => 'sm:grid-cols-2 lg:grid-cols-3'])

<div {{ $attributes->merge(['class' => "grid gap-10 $columns"]) }}>
    @foreach ($posts as $post)
        <x-post-card :post="$post" />
    @endforeach
</div>
