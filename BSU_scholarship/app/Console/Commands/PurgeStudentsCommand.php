<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Notification;
use App\Models\Scholar;
use App\Models\StudentProfile;
use App\Models\StudentSubmittedDocument;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeStudentsCommand extends Command
{
    protected $signature = 'students:purge {--force : Skip confirmation prompt}';

    protected $description = 'Delete all student users and their related records';

    public function handle(): int
    {
        $students = User::where('role', 'student')->get();

        if ($students->isEmpty()) {
            $this->info('No student users found.');
            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $confirmed = $this->confirm('This will delete all student users and related records. Continue?');

            if (! $confirmed) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        DB::transaction(function () use ($students) {
            foreach ($students as $student) {
                StudentSubmittedDocument::where('user_id', $student->id)->delete();
                StudentProfile::where('user_id', $student->id)->delete();
                Notification::where('user_id', $student->id)->delete();
                Application::where('user_id', $student->id)->delete();
                Scholar::where('user_id', $student->id)->delete();
                $student->delete();
            }
        });

        $this->info("Deleted {$students->count()} student users and related records.");

        return self::SUCCESS;
    }
}
