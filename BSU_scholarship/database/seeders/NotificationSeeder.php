<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all students
        $students = User::where('role', 'student')->get();
        
        if ($students->isEmpty()) {
            $this->command->info('No students found. Please run the UsersTableSeeder first.');
            return;
        }

        // Create sample notifications for each student
        foreach ($students as $student) {
            // Sample scholarship notification
            Notification::create([
                'user_id' => $student->id,
                'type' => 'scholarship_created',
                'title' => 'New Scholarship Available',
                'message' => 'A new scholarship "Academic Excellence Grant" has been posted. Check it out!',
                'data' => [
                    'scholarship_id' => 1,
                    'scholarship_name' => 'Academic Excellence Grant',
                    'deadline' => now()->addDays(30)->format('Y-m-d')
                ],
                'is_read' => false
            ]);

            // Sample application status notification
            Notification::create([
                'user_id' => $student->id,
                'type' => 'application_status',
                'title' => 'Application Status Update',
                'message' => 'Your application for "Merit Scholarship" has been approved. Congratulations!',
                'data' => [
                    'application_id' => 1,
                    'scholarship_id' => 2,
                    'status' => 'approved',
                    'scholarship_name' => 'Merit Scholarship'
                ],
                'is_read' => false
            ]);

            // Sample SFAO comment notification
            Notification::create([
                'user_id' => $student->id,
                'type' => 'sfao_comment',
                'title' => 'SFAO Comment on Your Application',
                'message' => 'Please submit your updated transcript. The current one is outdated.',
                'data' => [
                    'application_id' => 1,
                    'commenter_role' => 'sfao'
                ],
                'is_read' => true
            ]);

            // Sample old notification (read)
            Notification::create([
                'user_id' => $student->id,
                'type' => 'scholarship_created',
                'title' => 'Scholarship Deadline Reminder',
                'message' => 'The deadline for "Leadership Scholarship" is approaching. Apply now!',
                'data' => [
                    'scholarship_id' => 3,
                    'scholarship_name' => 'Leadership Scholarship',
                    'deadline' => now()->subDays(5)->format('Y-m-d')
                ],
                'is_read' => true,
                'read_at' => now()->subDays(2)
            ]);
        }

        $this->command->info('Sample notifications created for all students.');
    }
}
