<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Campus;
use App\Models\Department;

class CampusDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the mapping of Campuses to Departments (by short name)
        $campusDepartments = [
            'Pablo Borbon' => ['CABEIHM', 'CAS', 'CHS', 'COL', 'CTE'],
            'Alangilan' => ['COE', 'CAFAD', 'CET', 'CICS'],
            'Lipa' => ['CABEIHM', 'CAS', 'COE', 'CET', 'CICS', 'CTE'],
            'ARASOF' => ['CTE', 'CABEIHM', 'CICS', 'CAS', 'CHS'],
            'JPLPC' => ['CTE', 'COE', 'CICS', 'CAS', 'CABEIHM'],
            'Lemery' => ['CIT', 'CABEIHM', 'CTE'],
            'Rosario' => ['CTE', 'CIT', 'CABEIHM'],
            'San Juan' => ['CTE', 'CIT', 'CABEIHM'],
            'Lobo' => ['CAF'],
            'Mabini' => ['CICS'],
            'Balayan' => ['CIT', 'CICS'],
        ];

        foreach ($campusDepartments as $campusName => $deptShortNames) {
            // Find the campus (case-insensitive search)
            $campus = Campus::where('name', 'like', $campusName)->first();

            if ($campus) {
                foreach ($deptShortNames as $shortName) {
                    // Find the department
                    $department = Department::where('short_name', $shortName)->first();

                    if ($department) {
                        // Attach department to campus if not already attached
                        // Using DB facade to insert into pivot table directly or check existence
                        $exists = DB::table('campus_department')
                            ->where('campus_id', $campus->id)
                            ->where('department_id', $department->id)
                            ->exists();

                        if (!$exists) {
                            DB::table('campus_department')->insert([
                                'campus_id' => $campus->id,
                                'department_id' => $department->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
