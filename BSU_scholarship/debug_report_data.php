<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Campus;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- DEBUG REPORT DATA ---\n";

// 1. Check Campuses
$campuses = Campus::all();
echo "Campuses Count: " . $campuses->count() . "\n";
foreach ($campuses as $campus) {
    echo "Campus: [{$campus->id}] {$campus->name}\n";
    
    // 2. Check Departments for this campus
    $departments = $campus->departments;
    echo "  Departments Count: " . $departments->count() . "\n";
    
    foreach ($departments as $dept) {
        echo "  - Dept: {$dept->name} ({$dept->short_name})\n";
        
        // 3. Check Users matching this
        $usersCount = User::where('campus_id', $campus->id)
            ->where(function($q) use ($dept) {
                $q->where('college', $dept->name)
                  ->orWhere('college', $dept->short_name);
            })
            ->where('role', 'student')
            ->count();
            
        echo "    -> Matching Students (Name OR ShortName): {$usersCount}\n";
        
        if ($usersCount == 0) {
            // Check if there are ANY students in this campus with this college, maybe case mismatch?
            $similarUsers = User::where('campus_id', $campus->id)
                ->where('role', 'student')
                ->where('college', 'LIKE', "%{$dept->name}%")
                ->count();
            echo "    -> Similar Matching Students (LIKE): {$similarUsers}\n";
        }
    }
}

// 4. Check a sample student to see what their 'college' field looks like
$sampleStudent = User::where('role', 'student')->first();
if ($sampleStudent) {
    echo "\nSample Student:\n";
    echo "  Name: {$sampleStudent->name}\n";
    echo "  Campus ID: {$sampleStudent->campus_id}\n";
    echo "  College: '{$sampleStudent->college}'\n";
} else {
    echo "\nNo students found.\n";
}

echo "\n--- END DEBUG ---\n";
