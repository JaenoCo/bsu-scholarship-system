<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'College of Accountancy, Business, Economics and International Hospitality Management',
                'short_name' => 'CABEIHM',
                'description' => 'Focuses on business administration, accountancy, economics, and hospitality management, preparing students for leadership roles in the corporate and service sectors.'
            ],
            [
                'name' => 'College of Arts and Sciences',
                'short_name' => 'CAS',
                'description' => 'Provides a strong foundation in the liberal arts and sciences, fostering critical thinking, creativity, and scientific inquiry across various disciplines.'
            ],
            [
                'name' => 'College of Health Sciences',
                'short_name' => 'CHS',
                'description' => 'Dedicated to educating future healthcare professionals through rigorous training in nursing, nutrition, and other allied health fields.'
            ],
            [
                'name' => 'College of Law',
                'short_name' => 'COL',
                'description' => 'Offers a comprehensive legal education designed to produce competent, ethical, and socially responsible lawyers and legal practitioners.'
            ],
            [
                'name' => 'College of Teacher Education',
                'short_name' => 'CTE',
                'description' => ' committed to developing highly competent and dedicated teachers who are equipped with modern pedagogical skills and a passion for lifelong learning.'
            ],
            [
                'name' => 'College of Engineering',
                'short_name' => 'COE',
                'description' => 'Produces globally competitive engineers with strong technical skills and a commitment to innovation and sustainable development.'
            ],
            [
                'name' => 'College of Architecture, Fine Arts and Design',
                'short_name' => 'CAFAD',
                'description' => 'Nurtures creative talent in architecture, fine arts, and design, encouraging students to shape the built environment and visual culture.'
            ],
            [
                'name' => 'College of Engineering Technology',
                'short_name' => 'CET',
                'description' => 'Focuses on the practical application of engineering principles, preparing students for technical careers in various industries.'
            ],
            [
                'name' => 'College of Informatics and Computing Sciences',
                'short_name' => 'CICS',
                'description' => 'Is a university department that focuses on programs related to computers, information technology, and related fields.'
            ],
            [
                'name' => 'College of Industrial Technology',
                'short_name' => 'CIT',
                'description' => 'Provides technical education and training in industrial technologies, equipping students with the skills needed for the manufacturing and service industries.'
            ],
            [
                'name' => 'College of Agriculture and Forestry',
                'short_name' => 'CAF',
                'description' => 'Promotes sustainable agriculture and forestry practices through education, research, and extension services to ensure food security and environmental conservation.'
            ],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['short_name' => $department['short_name']], // Check for duplicates by short_name
                [
                    'name' => $department['name'],
                    'description' => $department['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
