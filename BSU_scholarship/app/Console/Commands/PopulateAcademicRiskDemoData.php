<?php

namespace App\Console\Commands;

use App\Models\GradeSubmission;
use App\Models\User;
use Illuminate\Console\Command;

class PopulateAcademicRiskDemoData extends Command
{
    protected $signature = 'academic-risk:demo {--clear : Remove records created by this command}';

    protected $description = 'Populate reversible approved semestral GWA data for academic-risk testing';

    private const REMARK = '[Academic risk demo data]';

    public function handle(): int
    {
        if ($this->option('clear')) {
            $deleted = GradeSubmission::where('remarks', self::REMARK)->delete();
            $this->info("Removed {$deleted} academic-risk demo submissions.");
            return self::SUCCESS;
        }

        $verifierId = User::whereIn('role', ['central', 'sfao'])->value('id');
        if (!$verifierId) {
            $this->error('No Central or SFAO user is available to verify demo records.');
            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        User::where('role', 'student')->orderBy('id')->chunkById(100, function ($students) use ($verifierId, &$created, &$updated) {
            foreach ($students as $student) {
                $pattern = $student->id % 4;
                preg_match('/([1-9])/', (string) $student->year_level, $yearMatch);
                $semesterCount = max(2, min(8, ((int) ($yearMatch[1] ?? 1)) * 2));
                if ($pattern === 2) {
                    $semesterCount = max(1, $semesterCount - 1);
                }
                $firstSchoolYear = 2025 - (int) ceil($semesterCount / 2);

                for ($periodIndex = 0; $periodIndex < $semesterCount; $periodIndex++) {
                    $isLatest = $periodIndex === $semesterCount - 1;
                    $gwa = match ($pattern) {
                        0 => $isLatest ? 1.55 : 1.80,
                        1 => 1.75,
                        2 => $isLatest ? 2.30 : 2.10,
                        default => $isLatest ? 2.80 : 2.35,
                    };
                    $schoolYearStart = $firstSchoolYear + (int) floor($periodIndex / 2);
                    $period = [
                        'school_year' => $schoolYearStart . '-' . ($schoolYearStart + 1),
                        'semester' => $periodIndex % 2 === 0 ? '1st Semester' : '2nd Semester',
                        'gwa' => $gwa,
                    ];

                    $submission = GradeSubmission::updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'school_year' => $period['school_year'],
                            'semester' => $period['semester'],
                            'remarks' => self::REMARK,
                        ],
                        [
                            'file_path' => null,
                            'status' => 'approved',
                            'verified_gwa' => $period['gwa'],
                            'gwa_verified_by' => $verifierId,
                            'gwa_verified_at' => now(),
                        ]
                    );

                    if ($submission->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    if ($submission->subjects()->doesntExist()) {
                        $submission->subjects()->create([
                            'subject_code' => 'DEMO-101',
                            'subject_name' => 'Demo Academic Record',
                            'units' => 3,
                            'grade' => 85,
                        ]);
                    }
                }
            }
        });

        $this->info("Academic-risk demo data ready: {$created} submissions created, {$updated} updated.");
        $this->line('Patterns: improving, stable, near-threshold, and critical.');
        $this->line('Clear later with: php artisan academic-risk:demo --clear');

        return self::SUCCESS;
    }
}
