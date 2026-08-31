<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$camp = App\Models\Campus::find(4);
if ($camp) {
    $data = $camp->colleges->map(function ($c) {
        return ['id' => $c->id, 'name' => $c->name, 'short_name' => $c->short_name];
    })->values()->all();
    echo json_encode($data, JSON_PRETTY_PRINT), PHP_EOL;
} else {
    echo '[]';
}
