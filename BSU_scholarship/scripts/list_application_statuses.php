<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$statuses = DB::table('applications')->select('status', DB::raw('COUNT(*) as cnt'))->groupBy('status')->orderBy('cnt', 'desc')->get();
echo "Application statuses (status => count):\n";
foreach ($statuses as $s) {
    echo " - {$s->status} => {$s->cnt}\n";
}

echo "\nSample problematic records (status not in approved,in_progress,pending,rejected):\n";
$bad = DB::table('applications')->whereNotIn('status', ['approved','in_progress','pending','rejected'])->limit(20)->get();
foreach ($bad as $b) {
    echo " - id: {$b->id} user_id: {$b->user_id} scholarship_id: {$b->scholarship_id} status: {$b->status}\n";
}

echo "\nDone.\n";
