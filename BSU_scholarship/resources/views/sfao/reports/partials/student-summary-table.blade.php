@foreach($reportData as $data)
    <div class="break-inside-avoid">
        <!-- Campus Header -->
        <div class="mb-4 mt-8 pb-2 border-b border-gray-400">
            <h3 class="text-lg font-bold text-gray-800 uppercase">{{ $data['campus_name'] ?? data_get($data, 'campus.name') ?? 'Campus' }}</h3>
        </div>

        <!-- Certified List Title -->
        <div class="text-center mb-6">
            <h4 class="text-md font-bold uppercase">
                CERTIFIED LIST OF 
                @if($studentType === 'applicants')
                    NEW APPLICANTS
                @elseif($studentType === 'scholars')
                    SCHOLARS
                @else
                    STUDENTS ({{ $studentType }})
                @endif
            </h4>
            <p class="text-sm font-medium uppercase mt-1">Academic Year {{ now()->year }}-{{ now()->addYear()->year }}</p>
        </div>

        <!-- Table -->
        <div class="mt-6 mb-4">
    <h1 class="text-2xl font-bold uppercase underline decoration-2 underline-offset-4">{{ $dynamicTitle ?? 'Student Summary Report' }}</h1>
</div>
<div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-400 text-[10px] print:text-[8px] leading-tight">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-8">SEQ</th>
                        @if($studentType === 'applicants')
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-14">APP NO</th>
                        @endif
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">LAST NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">FIRST NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">MIDDLE NAME</th>
                        <th class="border border-gray-400 px-0.5 py-1 print:py-0.5 font-bold text-center w-6">SEX</th>
                        
                        @if($studentType === 'applicants')
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-14 print:hidden">BIRTHDATE</th>
                        @endif

                        @if($studentType === 'scholars')
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">COLLEGE</th>
                        @endif

                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">COURSE/PROGRAM @if($studentType === 'applicants') ENROLLED @endif</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">TRACK</th>
                        
                        @if($studentType === 'applicants')
                            <th class="border border-gray-400 px-0.5 py-1 print:py-0.5 font-bold text-center w-8">YEAR</th>
                            <th class="border border-gray-400 px-0.5 py-1 print:py-0.5 font-bold text-center w-8 print:hidden">UNITS</th>
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">MUNICIPALITY</th>
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">PROVINCE</th>
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">PWD</th>
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">GRANT</th>
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">REMARKS</th>
                        @endif
                        
                        @if($studentType === 'scholars')
                             <!-- SCHOLAR specific additional columns if any, or shared ones -->
                             <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">SCHOLARSHIP</th>
                        @endif

                        @if($studentType === 'applicants')
                            <!-- For applicants, Scholarship is listed inside REMARKS or separate? In logic I put it. Let's add it separately if not redundant -->
                            <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">SCHOLARSHIP</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['students'] as $student)
                    <tr>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center">{{ $student['seq'] ?? $loop->iteration }}</td>
                        
                        @if($studentType === 'applicants')
                            <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center font-mono text-[9px] print:text-[8px]">{{ $student['app_id'] ?? '' }}</td>
                        @endif

                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $student['last_name'] ?? trim(explode(',', $student['name'] ?? '')[0]) }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $student['first_name'] ?? trim(explode(',', $student['name'] ?? '')[1] ?? '') }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $student['middle_name'] ?? '' }}</td>
                        <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center uppercase">{{ isset($student['sex']) ? substr($student['sex'], 0, 1) : '-' }}</td>

                        @if($studentType === 'applicants')
                            <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center whitespace-nowrap print:hidden">{{ $student['birthdate'] ?? '' }}</td>
                        @endif

                        @if($studentType === 'scholars')
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[8px]">{{ $student['college'] ?: 'N/A' }}</td>
                        @endif

                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px] leading-none">{{ $student['course'] ?? $student['program'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px] leading-none">{{ $student['track'] ?? 'N/A' }}</td>

                        @if($studentType === 'applicants')
                            <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center">{{ preg_replace('/[^0-9]/', '', $student['year_level'] ?? '') }}</td>
                            <td class="border border-gray-400 px-0.5 py-1 print:py-0.5 text-center print:hidden">{{ $student['units'] ?? '' }}</td>
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px]">{{ $student['municipality'] ?? '' }}</td>
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[7px]">{{ $student['province'] ?? '' }}</td>
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-center uppercase text-[9px] print:text-[7px]">{{ isset($student['pwd']) ? ($student['pwd'] ?: 'N/A') : 'N/A' }}</td>
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-right whitespace-nowrap">{{ is_numeric($student['grant'] ?? '') ? '₱'.number_format($student['grant'], 0) : ($student['grant'] ?? '') }}</td>
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-center uppercase text-[8px] print:text-[7px]">{{ $student['status_remarks'] ?? '' }}</td>
                        @endif

                        @if($studentType === 'scholars')
                             <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[8px]">{{ $student['scholarship'] ?? '' }}</td>
                        @endif

                        @if($studentType === 'applicants')
                            <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[8px]">{{ $student['scholarship'] ?? '' }}</td>
                        @endif

                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="border border-gray-400 px-4 py-8 text-center text-gray-500 italic">
                            No records found for this criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
