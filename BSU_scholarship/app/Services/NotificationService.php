<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewScholarshipAnnouncement;
use Illuminate\Support\Facades\Log;
use App\Mail\ApplicationCommentMail;
use App\Mail\ApplicationStatusUpdateMail;

class NotificationService
{
    /**
     * Create notification for new scholarship
     */
    public static function notifyScholarshipCreated(Scholarship $scholarship)
    {
        // Start query for students
        $query = User::where('role', 'student');

        // 1. Filter by Campus
        // 1. Filter by Campus (Support Multiple Campuses via Relation)
        $scholarship->load('campuses');
        if ($scholarship->campuses->isNotEmpty()) {
            $query->whereIn('campus_id', $scholarship->campuses->pluck('id'));
        } elseif ($scholarship->campus_id) {
            // Fallback for legacy single-campus structure
            $query->where('campus_id', $scholarship->campus_id);
        }

        // 2. Filter by Eligibility Conditions
        // Reload conditions to ensure we have the latest created ones
        $scholarship->load('conditions');

        foreach ($scholarship->conditions as $cond) {
            $value = $cond->value;
            if (!$value) continue;

            switch ($cond->name) {
                case 'year_level':
                    $query->where('year_level', $value);
                    break;
                    
                case 'department':
                    // Map department to college column
                    $query->where('college', $value);
                    break;
                    
                case 'gwa':
                    // GWA: lower value is better grade (e.g. 1.0 > 2.0). 
                    // "Minimum GWA of 2.0" means student must have <= 2.0
                    $query->whereHas('form', function($q) use ($value) {
                        $q->where('previous_gwa', '<=', $value)
                          ->where('previous_gwa', '>', 0);
                    });
                    break;
                    
                case 'income':
                    // Income: "Maximum Income" means student income <= value
                    $query->whereHas('form', function($q) use ($value) {
                        $q->where('estimated_gross_annual_income', '<=', $value);
                    });
                    break;
                    
                case 'disability':
                    if (strtolower($value) === 'yes') {
                        $query->whereHas('form', function($q) {
                            $q->whereNotNull('disability')
                              ->where('disability', '!=', 'None')
                              ->where('disability', '!=', '');
                        });
                    }
                    break;
            }
        }

        $students = $query->get();
        $emailCount = 0;
        
        foreach ($students as $student) {
            // Create Database Notification
            Notification::create([
                'user_id' => $student->id,
                'type' => 'scholarship_created',
                'title' => 'New Scholarship Available',
                'message' => "A new scholarship '{$scholarship->scholarship_name}' has been posted. Check it out!",
                'data' => [
                    'scholarship_id' => $scholarship->id,
                    'scholarship_name' => $scholarship->scholarship_name,
                    'deadline' => $scholarship->submission_deadline
                ]
            ]);

            // Send Email Notification
            if ($student->email) {
                try {
                    Log::info("Sending email to: " . $student->email . " [Campus: " . $student->campus_id . "]");
                    Mail::to($student->email)->send(new NewScholarshipAnnouncement($scholarship));
                    $emailCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send email: ' . $e->getMessage());
                }
            }
        }
        
        return $emailCount;
    }

    /**
     * Create notification for SFAO comment
     */
    public static function notifySfaoComment(User $student, string $comment, $applicationId = null)
    {
        Notification::create([
            'user_id' => $student->id,
            'type' => 'sfao_comment',
            'title' => 'SFAO Comment on Your Application',
            'message' => $comment,
            'data' => [
                'application_id' => $applicationId,
                'commenter_role' => 'sfao'
            ]
        ]);

        // Send Email Notification
        if ($student->email) {
            try {
                $application = Application::find($applicationId);
                if ($application) {
                    Mail::to($student->email)->send(new ApplicationCommentMail($application, $comment));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send application comment email to ' . $student->email . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Create notification for application status change
     */
    public static function notifyApplicationStatusChange(Application $application, string $status, string $message = null)
    {
        $statusMessages = [
            'approved' => 'Congratulations! Your application has been approved.',
            'rejected' => 'Your application has been reviewed and unfortunately not approved.',
            'pending' => 'Your application is being reviewed.',
            'under_review' => 'Your application is currently under review.'
        ];

        $defaultMessage = $statusMessages[$status] ?? 'Your application status has been updated.';
        $finalMessage = $message ?: $defaultMessage;

        Notification::create([
            'user_id' => $application->user_id,
            'type' => 'application_status',
            'title' => 'Application Status Update',
            'message' => $finalMessage,
            'data' => [
                'application_id' => $application->id,
                'scholarship_id' => $application->scholarship_id,
                'status' => $status,
                'scholarship_name' => $application->scholarship->scholarship_name ?? 'Unknown Scholarship'
            ]
        ]);

        // Send Email Notification
        $user = User::find($application->user_id);
        if ($user && $user->email) {
            try {
                Mail::to($user->email)->send(new ApplicationStatusUpdateMail($application, $status, $message));
            } catch (\Exception $e) {
                Log::error('Failed to send application status update email to ' . $user->email . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}
