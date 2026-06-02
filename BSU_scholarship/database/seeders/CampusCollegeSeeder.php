<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Campus;
use App\Models\College;

class CampusCollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the mapping of Campuses to Colleges (by short name)
        $campusColleges = [
            'Pablo Borbon' => ['CABEIHM', 'CAS', 'CHS', 'CTE', 'CCJE'],
            'Alangilan' => ['COE', 'CAFAD', 'CET', 'CICS'],
            'Lipa' => ['CABEIHM', 'CAS', 'COE', 'CET', 'CICS', 'CTE'],
            'ARASOF' => ['CTE', 'CABEIHM', 'CICS', 'CAS', 'CHS'],
            'JPLPC' => ['CTE', 'COE', 'CICS', 'CAS', 'CABEIHM'],
            'Lemery' => ['CABE', 'CTE'],
            'Rosario' => ['CABEIHM', 'CTE'],
            'San Juan' => ['CABEIHM', 'CTE'],
            'Lobo' => ['CAF'],
            'Mabini' => ['CICS'],
            'Balayan' => ['CET', 'CICS'],
        ];

        foreach ($campusColleges as $campusName => $collegeShortNames) {
            // Find the campus (case-insensitive search)
            $campus = Campus::where('name', 'like', $campusName)->first();

            if ($campus) {
                foreach ($collegeShortNames as $shortName) {
                    // Find the college
                    $college = College::where('short_name', $shortName)->first();

                    if ($college) {
                        // Attach college to campus if not already attached
                        // Using DB facade to insert into pivot table directly or check existence
                        $exists = DB::table('campus_college')
                            ->where('campus_id', $campus->id)
                            ->where('college_id', $college->id)
                            ->exists();

                        if (!$exists) {
                            DB::table('campus_college')->insert([
                                'campus_id' => $campus->id,
                                'college_id' => $college->id,
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
