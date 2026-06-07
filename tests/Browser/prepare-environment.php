<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
Illuminate\Support\Facades\Artisan::call('db:seed', [
    '--class' => 'Database\\Seeders\\CategorySeeder',
    '--force' => true,
]);
Illuminate\Support\Facades\Artisan::call('db:seed', [
    '--class' => 'Database\\Seeders\\PageSeeder',
    '--force' => true,
]);

foreach (Category::query()->get() as $category) {
    Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => "Test yazısı — {$category->name}",
    ]);
}

echo "Browser test ortamı hazır.\n";
