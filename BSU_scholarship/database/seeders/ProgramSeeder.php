<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Campus;
use App\Models\CampusCollege;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        // 1. Define Standard Programs per College (Super-set)
        $standards = [
            'CABEIHM' => [
                ['name' => 'Bachelor of Science in Accountancy', 'short_name' => 'BSA'],
                ['name' => 'Bachelor of Science in Management Accounting', 'short_name' => 'BSMA'],
                ['name' => 'Bachelor of Science in Business Administration', 'short_name' => 'BSBA'],
                ['name' => 'Bachelor of Science in Entrepreneurship', 'short_name' => 'BSEntrep'],
                ['name' => 'Bachelor in Public Administration', 'short_name' => 'BPA'],
                ['name' => 'Bachelor of Science in Hospitality Management', 'short_name' => 'BSHM'],
                ['name' => 'Bachelor of Science in Tourism Management', 'short_name' => 'BSTM'],
                ['name' => 'Bachelor of Science in Customs Administration', 'short_name' => 'BSCA'],
            ],
            'CAS' => [
                ['name' => 'Bachelor of Arts in English Language Studies', 'short_name' => 'BA ELS'],
                ['name' => 'Bachelor of Science in Biology', 'short_name' => 'BS Bio'],
                ['name' => 'Bachelor of Science in Chemistry', 'short_name' => 'BS Chem'],
                ['name' => 'Bachelor of Science in Development Communication', 'short_name' => 'BS DevComm'],
                ['name' => 'Bachelor of Science in Mathematics', 'short_name' => 'BS Math'],
                ['name' => 'Bachelor of Science in Psychology', 'short_name' => 'BS Psych'],
            ],
            'CHS' => [
                ['name' => 'Bachelor of Science in Nursing', 'short_name' => 'BSN'],
                ['name' => 'Bachelor of Science in Nutrition and Dietetics', 'short_name' => 'BSND'],
                ['name' => 'Bachelor of Science in Public Health (Disaster Response)', 'short_name' => 'BSPH'],
            ],
            'CTE' => [
                ['name' => 'Bachelor of Elementary Education', 'short_name' => 'BEEd'],
                ['name' => 'Bachelor of Early Childhood', 'short_name' => 'BECEd'],
                ['name' => 'Bachelor of Secondary Education', 'short_name' => 'BSEd'],
                ['name' => 'Bachelor of Physical Education', 'short_name' => 'BPEd'],
                ['name' => 'Bachelor of Technology and Livelihood Education', 'short_name' => 'BTLEd'],

            ],
            'CCJE' => [
                ['name' => 'Bachelor of Science in Criminology', 'short_name' => 'BS Crim'],
            ],
            'CABE' => [
                ['name' => 'Bachelor of Science in Management Accounting', 'short_name' => 'BSMA'],
                ['name' => 'Bachelor of Science in Business Administration', 'short_name' => 'BSBA'],
            ],
            'COE' => [
                ['name' => 'Bachelor of Science in Chemical Engineering', 'short_name' => 'BSCheE'],
                ['name' => 'Bachelor of Science in Civil Engineering', 'short_name' => 'BSCE'],
                ['name' => 'Bachelor of Science in Computer Engineering', 'short_name' => 'BSCpE'],
                ['name' => 'Bachelor of Science in Electrical Engineering', 'short_name' => 'BSEE'],
                ['name' => 'Bachelor of Science in Electronics Engineering', 'short_name' => 'BSECE'],
                ['name' => 'Bachelor of Science in Food Engineering', 'short_name' => 'BSFE'],
                ['name' => 'Bachelor of Science in Industrial Engineering', 'short_name' => 'BSIE'],
                ['name' => 'Bachelor of Science in Instrumentation & Control Engineering', 'short_name' => 'BSICE'],
                ['name' => 'Bachelor of Science in Mechatronics Engineering', 'short_name' => 'BSMeE'],
                ['name' => 'Bachelor of Science in Mechanical Engineering', 'short_name' => 'BSME'],
                ['name' => 'Bachelor of Science in Petroleum Engineering', 'short_name' => 'BSPetE'],
                ['name' => 'Bachelor of Science in Sanitary Engineering', 'short_name' => 'BSEnSE'],
                ['name' => 'Bachelor of Science in Automotive Engineering', 'short_name' => 'BSAE'],
                ['name' => 'Bachelor of Science in Aerospace Engineering', 'short_name' => 'BSAeroE'],
                ['name' => 'Bachelor of Science in Biomedical Engineering', 'short_name' => 'BSBioMedE'],
                ['name' => 'Bachelor of Science in Ceramics Engineering', 'short_name' => 'BSCeraE'],
                ['name' => 'Bachelor of Science in Geodetic Engineering', 'short_name' => 'BSGE'],
                ['name' => 'Bachelor of Science in Geological Engineering', 'short_name' => 'BSGeoE'],
                ['name' => 'Bachelor of Science in Metallurgical Engineering', 'short_name' => 'BSMetE'],
                ['name' => 'Bachelor of Science in Naval Architecture and Marine Engineering', 'short_name' => 'BSNAME'],
                ['name' => 'Bachelor of Science in Transportation Engineering', 'short_name' => 'BSTransE'],
            ],
            'CAFAD' => [
                ['name' => 'Bachelor of Science in Architecture', 'short_name' => 'BSArch'],
                ['name' => 'Bachelor of Fine Arts and Design', 'short_name' => 'BFAD'],
                ['name' => 'Bachelor of Science in Interior Design', 'short_name' => 'BSID'],
            ],
            'CICS' => [
                ['name' => 'Bachelor of Science in Computer Science', 'short_name' => 'BSCS'],
                ['name' => 'Bachelor of Science in Information Technology', 'short_name' => 'BSIT'],
            ],
            'CET' => [
                ['name' => 'Bachelor of Automotive Engineering Technology', 'short_name' => 'BAET'],
                ['name' => 'Bachelor of Civil Engineering Technology', 'short_name' => 'BCET'],
                ['name' => 'Bachelor of Computer Engineering Technology', 'short_name' => 'BCpET'],
                ['name' => 'Bachelor of Drafting Engineering Technology', 'short_name' => 'BDET'],
                ['name' => 'Bachelor of Electrical Engineering Technology', 'short_name' => 'BEET'],
                ['name' => 'Bachelor of Electronics Engineering Technology', 'short_name' => 'BECT'],
                ['name' => 'Bachelor of Food Engineering Technology', 'short_name' => 'BFET'],
                ['name' => 'Bachelor of Instrumentation and Control Engineering Technology', 'short_name' => 'BICET'],
                ['name' => 'Bachelor of Mechanical Engineering Technology', 'short_name' => 'BMET'],
                ['name' => 'Bachelor of Mechatronics Engineering Technology', 'short_name' => 'BMeT'],
                ['name' => 'Bachelor of Welding and Fabrication Engineering Technology', 'short_name' => 'BWFET'],
            ],
            'CAF' => [
                 ['name' => 'Bachelor of Science in Agriculture', 'short_name' => 'BSA'],
                 ['name' => 'Bachelor of Science in Forestry', 'short_name' => 'BSF'],
            ],
        ];

        // 2. Define Specific Overrides
        $overrides = [
            'Rosario' => [
                'CABEIHM' => [
                    ['name' => 'Bachelor of Science in Business Administration', 'short_name' => 'BSBA'],
                ],
                'CTE' => [
                    ['name' => 'Bachelor of Secondary Education', 'short_name' => 'BSEd'],
                    ['name' => 'Bachelor of Technology and Livelihood Education', 'short_name' => 'BTLEd'],
                    ['name' => 'Bachelor of Elementary Education', 'short_name' => 'BEEd'],
                ]
            ],
            'San Juan' => [
                'CABEIHM' => [
                    ['name' => 'Bachelor of Science in Business Administration', 'short_name' => 'BSBA'],
                ],
                'CTE' => [
                    ['name' => 'Bachelor of Secondary Education', 'short_name' => 'BSEd'],
                    ['name' => 'Bachelor of Technology and Livelihood Education', 'short_name' => 'BTLEd'],
                ]
            ],
            'Lemery' => [
                'CTE' => [
                    ['name' => 'Bachelor of Technical-Vocational Teacher Education', 'short_name' => 'BTVTEd'],
                    ['name' => 'Bachelor of Secondary Education', 'short_name' => 'BSEd'],
                ]
            ],
            'Balayan' => [
                'CET' => [
                     ['name' => 'Bachelor of Automotive Engineering Technology', 'short_name' => 'BAET'],
                     ['name' => 'Bachelor of Civil Engineering Technology', 'short_name' => 'BCET'],
                     ['name' => 'Bachelor of Computer Engineering Technology', 'short_name' => 'BCpET'],
                     ['name' => 'Bachelor of Drafting Engineering Technology', 'short_name' => 'BDET'],
                     ['name' => 'Bachelor of Electrical Engineering Technology', 'short_name' => 'BEET'],
                     ['name' => 'Bachelor of Electronics Engineering Technology', 'short_name' => 'BECT'],
                     ['name' => 'Bachelor of Instrumentation and Control Engineering Technology', 'short_name' => 'BICET'],
                     ['name' => 'Bachelor of Mechanical Engineering Technology', 'short_name' => 'BMET'],
                ],
                'CICS' => [
                    ['name' => 'Bachelor of Science in Information Technology', 'short_name' => 'BSIT'],
                ]
            ],
            'Mabini' => [
                'CICS' => [
                    ['name' => 'Bachelor of Science in Information Technology', 'short_name' => 'BSIT'],
                ]
            ],
        ];

        // 3. Loop through Campuses and seed based on logic
        $campuses = Campus::all();

        foreach ($campuses as $campus) {
            // Retrieve attached colleges through pivot
            $campusColleges = CampusCollege::where('campus_id', $campus->id)->with('college')->get();

            foreach ($campusColleges as $cc) {
                $collegeShort = $cc->college->short_name;
                
                $programsToSeed = [];

                if (isset($overrides[$campus->name][$collegeShort])) {
                    $programsToSeed = $overrides[$campus->name][$collegeShort];
                } elseif (isset($standards[$collegeShort])) {
                    $programsToSeed = $standards[$collegeShort];
                }

                foreach ($programsToSeed as $prog) {
                    Program::updateOrCreate(
                        [
                            'campus_college_id' => $cc->id,
                            'name' => $prog['name']
                        ],
                        [
                            'short_name' => $prog['short_name']
                        ]
                    );
                }
            }
        }
    }
}
