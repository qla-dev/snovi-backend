<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$story = App\Models\Story::where('slug', 'crvenkapica')->first();
$res = App\Http\Resources\StoryResource::make($story)->toArray(request());
echo json_encode($res, JSON_PRETTY_PRINT);
