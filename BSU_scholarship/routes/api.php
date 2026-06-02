<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Scholarship API routes
Route::get('/scholarships/{id}', function ($id) {
    try {
        \Log::info('API call to scholarships/' . $id);
        $scholarship = \App\Models\Scholarship::find($id);
        if (!$scholarship) {
            \Log::info('Scholarship not found for ID: ' . $id);
            return response()->json(['error' => 'Scholarship not found'], 404);
        }
        \Log::info('Found scholarship: ' . $scholarship->scholarship_name);
        return response()->json($scholarship);
    } catch (\Exception $e) {
        \Log::error('API error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
});
