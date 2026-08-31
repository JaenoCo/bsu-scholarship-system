<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::where('email', 'qa.manual.1788148666143@g.batstate-u.edu.ph')->first();
$apps = $user ? App\Models\Application::where('user_id', $user->id)->get(['id','scholarship_id','status','created_at']) : collect();
$arasof = App\Models\Campus::where('name', 'ARASOF')->first();
$sfao = App\Models\User::where('role', 'sfao')->where('campus_id', $arasof?->id ?? 0)->get(['id','email','campus_id','name','role']);
echo json_encode([
  'manual_student' => $user ? ['id' => $user->id, 'email' => $user->email, 'campus_id' => $user->campus_id, 'role' => $user->role] : null,
  'manual_app_count' => $apps->count(),
  'manual_apps' => $apps->map(fn($a) => ['id' => $a->id, 'scholarship_id' => $a->scholarship_id, 'status' => $a->status, 'created_at' => $a->created_at])->values()->all(),
  'arasof_campus' => $arasof ? ['id' => $arasof->id, 'name' => $arasof->name, 'slug' => $arasof->slug, 'type' => $arasof->type] : null,
  'arasof_sfao' => $sfao->map(fn($u) => ['id' => $u->id, 'email' => $u->email, 'campus_id' => $u->campus_id, 'name' => $u->name, 'role' => $u->role])->values()->all(),
], JSON_PRETTY_PRINT);
