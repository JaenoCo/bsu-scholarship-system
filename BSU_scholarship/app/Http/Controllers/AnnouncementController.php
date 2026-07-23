<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->resolveUser();

        $query = Scholarship::query()
            ->where('is_active', true)
            ->whereNotNull('announcement_title')
            ->where('announcement_title', '<>', '')
            ->whereNotNull('announcement_message')
            ->where('announcement_message', '<>', '');

        if ($user && $user->role === 'student' && $user->campus_id) {
            $this->scopeScholarshipCampusAvailability($query, [$user->campus_id]);
        }

        $announcements = $query->orderBy('updated_at', 'desc')->get();

        return view('announcements.index', compact('announcements'));
    }

    private function resolveUser(): ?User
    {
        $userId = session('user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function scopeScholarshipCampusAvailability($query, array $campusIds)
    {
        return $query->where(function ($scope) use ($campusIds) {
            if (Schema::hasColumn('scholarships', 'campus_id')) {
                $scope->whereIn('campus_id', $campusIds);
            }

            if (Schema::hasTable('campus_scholarship')) {
                $scope->orWhereHas('campuses', function ($campusQuery) use ($campusIds) {
                    $campusQuery->whereIn('campuses.id', $campusIds);
                })->orDoesntHave('campuses');
            }
        });
    }
}
