@foreach($reportData as $data)
    <div class="break-inside-avoid">
        <!-- Campus Header -->
        <div class="mb-4 mt-8 pb-2 border-b border-gray-400">
            <h3 class="text-lg font-bold text-gray-800 uppercase">{{ $data['campus_name'] ?? data_get($data, 'campus.name') ?? 'Campus' }}</h3>
        </div>

        <!-- Certified List Title -->
        <div class="text-center mb-6">
            <h4 class="text-md font-bold uppercase">
                CERTIFIED LIST OF NEW <span class="text-bsu-red">{{ $selectedScholarship ? $selectedScholarship->scholarship_name : 'SCHOLARSHIP' }}</span> APPLICANTS
            </h4>
            <p class="text-sm font-medium uppercase mt-1">Academic Year {{ now()->year }}-{{ now()->addYear()->year }}</p>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-400 text-[10px] print:text-[8px] leading-tight">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-8">SEQ</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-14">APP NO</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">LAST NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">FIRST NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">MIDDLE NAME</th>
                        <th class="border border-gray-400 px-0.5 py-1 print:py-0.5 font-bold text-center w-6">SEX</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-14 print:hidden">BIRTHDATE</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">COURSE/PROGRAM ENROLLED</th>
                        <th class="border border-gray-400 px-0.5 py-1 print:py-0.5 font-bold text-center w-8">YEAR</th>
                        <th class="border border-gray-400 px-0.5 py-1 print:py-0.5 font-bold text-center w-8 print:hidden">UNITS</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">MUNICIPALITY</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">PROVINCE</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">PWD</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">GRANT</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['students'] as $student)
                    <tr>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center">{{ $student['seq'] ?? $loop->iteration }}</td>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center font-mono text-[9px] print:text-[8px]">{{ $student['app_id'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $student['last_name'] ?? trim(explode(',', $student['name'] ?? '')[0]) }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $student['first_name'] ?? trim(explode(',', $student['name'] ?? '')[1] ?? '') }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $student['middle_name'] ?? '' }}</td>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center uppercase">{{ isset($student['sex']) ? substr($student['sex'], 0, 1) : '-' }}</td>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center whitespace-nowrap print:hidden">{{ $student['birthdate'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px] leading-none">{{ $student['course'] ?? '' }}</td>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center">{{ preg_replace('/[^0-9]/', '', $student['year_level'] ?? '') }}</td> <!-- Just the number -->
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center print:hidden">{{ $student['units'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px]">{{ $student['municipality'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px]">{{ $student['province'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-center uppercase text-[9px] print:text-[7px]">{{ isset($student['pwd']) ? ($student['pwd'] ?: 'N/A') : 'N/A' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-right whitespace-nowrap">{{ is_numeric($student['grant'] ?? '') ? '₱'.number_format($student['grant'], 0) : ($student['grant'] ?? '') }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-center uppercase text-[8px] print:text-[7px]">{{ $student['status_remarks'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="border border-gray-400 px-4 py-8 text-center text-gray-500 italic">
                            No records found for this criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
