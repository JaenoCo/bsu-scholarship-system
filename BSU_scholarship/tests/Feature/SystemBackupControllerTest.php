<?php

namespace Tests\Feature;

use App\Http\Controllers\SystemBackupController;
use Tests\TestCase;

class SystemBackupControllerTest extends TestCase
{
    public function test_sfao_export_filenames_follow_category_and_year_pattern(): void
    {
        $this->assertSame(
            'Nasugbu-Arasof_Scholar_Summary_2026.xlsx',
            SystemBackupController::buildExportFilename('sfao', 'scholar_summary', 2026, 'Nasugbu-Arasof', 'xlsx')
        );

        $this->assertSame(
            'Arasof-Nasugbu_Applicants_2026.csv',
            SystemBackupController::buildExportFilename('sfao', 'applicants', 2026, 'Arasof-Nasugbu', 'csv')
        );

        $this->assertSame(
            'Registered_Arasof-Nasugbu_Users_2026.xlsx',
            SystemBackupController::buildExportFilename('sfao', 'registered_users', 2026, 'Arasof-Nasugbu', 'xlsx')
        );
    }

    public function test_central_export_filenames_follow_admin_summary_names(): void
    {
        $this->assertSame('SFAO_Users.xlsx', SystemBackupController::buildExportFilename('central', 'sfao_users', null, null, 'xlsx'));
        $this->assertSame('Campus.csv', SystemBackupController::buildExportFilename('central', 'campus', null, null, 'csv'));
        $this->assertSame('Campus_scholarships.xlsx', SystemBackupController::buildExportFilename('central', 'campus_scholarships', null, null, 'xlsx'));
    }
}
