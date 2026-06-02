<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Program;
use App\Models\Campus;
use App\Models\College;
use App\Models\CampusCollege;

class ProgramTrackSeeder extends Seeder
{
    public function run()
    {
        // 1. Specific Tracks per Campus
        $specifics = [
            'Pablo Borbon' => [
                'CABEIHM' => [
                    'Bachelor of Science in Business Administration' => [
                        'majors' => ['Business Economics', 'Financial Management', 'Human Resource Management', 'Marketing Management', 'Operations Management'],
                        'type' => 'Major'
                    ],
                ],
                'CTE' => [
                    'Bachelor of Secondary Education' => [
                        'majors' => ['English', 'Mathematics', 'Sciences', 'Filipino', 'Social Studies'],
                        'type' => 'Major'
                    ],
                    'Bachelor of Technology and Livelihood Education' => [
                        'majors' => [],
                        'type' => 'Major'
                    ]
                ]
            ],
            'Rosario' => [
                'CABEIHM' => [
                    'Bachelor of Science in Business Administration' => [
                        'majors' => ['Financial Management', 'Human Resource Management', 'Marketing Management'],
                        'type' => 'Major'
                    ],
                ],
                'CTE' => [
                    'Bachelor of Secondary Education' => [
                        'majors' => ['English', 'Mathematics'],
                        'type' => 'Major'
                    ],
                    'Bachelor of Technology and Livelihood Education' => [
                        'majors' => ['Home Economics'],
                        'type' => 'Major'
                    ]
                ]
            ],
            'San Juan' => [
                'CABEIHM' => [
                    'Bachelor of Science in Business Administration' => [
                        'majors' => ['Marketing Management'],
                        'type' => 'Major'
                    ],
                ],
                'CTE' => [
                    'Bachelor of Secondary Education' => [
                         'majors' => ['English', 'Filipino'],
                         'type' => 'Major'
                    ],
                    'Bachelor of Technology and Livelihood Education' => [
                        'majors' => [], // No major listed in prompt ("Bachelor of Technology and Livelihood Education" with no "Major in:" below it)
                         'type' => 'Major'
                    ]
                ]
            ],
            'Lemery' => [
                'CTE' => [
                    'Bachelor of Technical-Vocational Teacher Education' => [
                        'majors' => ['Garments, Fashion and Design', 'Electronics Technology'],
                        'type' => 'Major'
                    ],
                    'Bachelor of Secondary Education' => [
                        'majors' => ['Social Studies'],
                        'type' => 'Major'
                    ]
                ],
                'CABE' => [
                    'Bachelor of Science in Business Administration' => [
                        'majors' => ['Financial Management', 'Human Resource Management', 'Marketing Management'],
                        'type' => 'Major'
                    ]
                ]
            ]
        ];

        // 2. Global Defaults (Applied if no specific applied, or generally for other colleges)
        $defaults = [
            'COE' => [
                'Bachelor of Science in Civil Engineering' => ['Construction Engineering Management', 'Geotechnical Engineering', 'Structural Engineering', 'Transportation Engineering', 'Water Resources Engineering'],
                'Bachelor of Science in Electrical Engineering' => ['Machine Automation and Process Control', 'Renewable Energy for Sustainable Development'],
                'Bachelor of Science in Electronics Engineering' => ['Computer Communication', 'Microelectronics', 'Telecommunications and Building Infrastructure'],
            ],
            'CAFAD' => [
                'Bachelor of Fine Arts and Design' => ['Visual Communication'],
            ],
            'CICS' => [
                'Bachelor of Science in Information Technology' => [
                    'majors' => ['Business Analytics', 'Network Technology', 'Service Management'],
                    'type' => 'Specialization'
                ]
            ],
            'CAF' => [
                 'Bachelor of Science in Agriculture' => ['Crop Science', 'Animal Science']
            ]
        ];

        $processedProgramIds = [];

        // Run Specifics
        foreach ($specifics as $campusName => $collegesData) {
            $campus = Campus::where('name', $campusName)->first();
            if (!$campus) continue;

            foreach ($collegesData as $collegeShort => $programsData) {
                // Find CampusCollege
                $cc = CampusCollege::where('campus_id', $campus->id)
                    ->whereHas('college', fn($q) => $q->where('short_name', $collegeShort))
                    ->first();

                if (!$cc) continue;

                foreach ($programsData as $progName => $data) {
                    $program = Program::where('campus_college_id', $cc->id)->where('name', $progName)->first();
                    if ($program) {
                        $this->seedTracks($program, $data);
                        $processedProgramIds[] = $program->id;
                    }
                }
            }
        }

        // Run Defaults (For ALangilan, Balayan, etc, or anything not covered)
        foreach ($defaults as $collegeShort => $programsData) {
             foreach ($programsData as $progName => $data) {
                 // Normalize data format (some are simple arrays, some have 'type')
                 if (!isset($data['majors'])) {
                     $data = ['majors' => $data, 'type' => 'Major'];
                 }

                 // Find Programs
                 $programs = Program::where('name', $progName)
                    ->whereHas('campusCollege.college', fn($q) => $q->where('short_name', $collegeShort))
                    ->whereNotIn('id', $processedProgramIds)
                    ->get();
                
                 foreach ($programs as $program) {
                     $this->seedTracks($program, $data);
                 }
             }
        }
    }

    private function seedTracks($program, $data) {
        if (empty($data['majors'])) return;
        $trackType = $data['type'] ?? 'Major';
        
        foreach ($data['majors'] as $major) {
            DB::table('program_tracks')->updateOrInsert(
                [
                    'program_id' => $program->id,
                    'name' => $major,
                ],
                [
                    'track_type' => $trackType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
