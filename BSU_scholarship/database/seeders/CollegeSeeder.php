<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colleges = [
            [
                'name' => 'College of Accountancy, Business, Economics, International Hospitality Management',
                'short_name' => 'CABEIHM',
                'description' => 'Focuses on business administration, accountancy, economics, and hospitality management.'
            ],
            [
                'name' => 'College of Accountancy, Business and Economics',
                'short_name' => 'CABE',
                'description' => 'Focuses on business administration, accountancy, and economics.'
            ],
            [
                'name' => 'College of Arts and Sciences',
                'short_name' => 'CAS',
                'description' => 'Provides a strong foundation in the liberal arts and sciences.'
            ],
            [
                'name' => 'College of Health Sciences',
                'short_name' => 'CHS',
                'description' => 'Dedicated to educating future healthcare professionals.'
            ],
            [
                'name' => 'College of Law',
                'short_name' => 'COL',
                'description' => 'Offers a comprehensive legal education.'
            ],
            [
                'name' => 'College of Teacher Education',
                'short_name' => 'CTE',
                'description' => 'Committed to developing highly competent and dedicated teachers.'
            ],
            [
                'name' => 'College of Engineering',
                'short_name' => 'COE',
                'description' => 'Produces globally competitive engineers.'
            ],
             [
                'name' => 'College of Criminal Justice Education',
                'short_name' => 'CCJE',
                'description' => 'Dedicated to criminology and criminal justice education.'
            ],
            [
                'name' => 'College of Architecture, Fine Arts and Design',
                'short_name' => 'CAFAD',
                'description' => 'Nurtures creative talent in architecture, fine arts, and design.'
            ],
            [
                'name' => 'College of Engineering Technology',
                'short_name' => 'CET',
                'description' => 'Focuses on the practical application of engineering principles.'
            ],
            [
                'name' => 'College of Informatics and Computing Sciences',
                'short_name' => 'CICS',
                'description' => 'Focuses on programs related to computers and information technology.'
            ],
            [
                'name' => 'College of Industrial Technology',
                'short_name' => 'CIT',
                'description' => 'Provides technical education and training in industrial technologies.'
            ],
            [
                'name' => 'College of Agriculture and Forestry',
                'short_name' => 'CAF',
                'description' => 'Promotes sustainable agriculture and forestry practices.'
            ],
        ];

        foreach ($colleges as $college) {
            DB::table('colleges')->updateOrInsert(
                ['short_name' => $college['short_name']], // Check for duplicates by short_name
                [
                    'name' => $college['name'],
                    'description' => $college['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
