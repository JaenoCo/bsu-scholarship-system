<?php

namespace App\Services;

use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;

class ScholarshipAudienceService
{
    public function applyToStudentQuery(Builder $query, Scholarship $scholarship): Builder
    {
        $scholarship->loadMissing(['campuses', 'targetColleges', 'targetPrograms', 'targetTracks']);

        if ($scholarship->campuses->isNotEmpty()) {
            $query->whereIn('campus_id', $scholarship->campuses->modelKeys());
        } elseif ($scholarship->campus_id) {
            $query->where('campus_id', $scholarship->campus_id);
        }

        if ($scholarship->targetColleges->isNotEmpty()) {
            $query->whereIn('college', $scholarship->targetColleges->pluck('short_name')->all());
        }

        if ($scholarship->targetPrograms->isNotEmpty()) {
            $query->whereIn('program', $scholarship->targetPrograms->pluck('name')->all());
        }

        if ($scholarship->targetTracks->isNotEmpty()) {
            $query->whereIn('track', $scholarship->targetTracks->pluck('name')->all());
        }

        return $query;
    }
}
