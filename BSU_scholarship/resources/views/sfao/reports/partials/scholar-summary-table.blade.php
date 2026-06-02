@foreach($reportData as $data)
    <div class="break-inside-avoid">
        <!-- Campus Header -->
        <div class="mb-4 mt-8 pb-2 border-b border-gray-400">
            <h3 class="text-lg font-bold text-gray-800 uppercase">{{ $data['campus_name'] ?? data_get($data, 'campus.name') ?? 'Campus' }}</h3>
        </div>

        <!-- Certified List Title -->
        <div class="text-center mb-6">
            <h4 class="text-md font-bold uppercase">
                CERTIFIED LIST OF <span class="text-bsu-red">{{ $selectedScholarship ? $selectedScholarship->scholarship_name : 'SCHOLARSHIP' }}</span> SCHOLARS
            </h4>
            <p class="text-sm font-medium uppercase mt-1">Academic Year {{ now()->year }}-{{ now()->addYear()->year }}</p>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-400 text-[10px] print:text-[8px] leading-tight">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-10">SEQ</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">LAST NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">FIRST NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">MIDDLE NAME</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center w-12">SEX</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">COLLEGE</th>
                        <th class="border border-gray-400 px-1 py-1 print:py-0.5 font-bold text-center">PROGRAM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['scholars'] as $scholar)
                    <tr>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-center">{{ $scholar['seq'] ?? $loop->iteration }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $scholar['last_name'] ?? trim(explode(',', $scholar['name'] ?? '')[0]) }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $scholar['first_name'] ?? trim(explode(',', $scholar['name'] ?? '')[1] ?? '') }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase whitespace-nowrap">{{ $scholar['middle_name'] ?? '' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 text-center uppercase">{{ isset($scholar['sex']) ? substr($scholar['sex'], 0, 1) : '-' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[8px]">{{ $scholar['college'] ?: 'N/A' }}</td>
                        <td class="border border-gray-400 px-1 py-1 print:py-0.5 uppercase text-[9px] print:text-[8px]">{{ $scholar['program'] ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="border border-gray-400 px-4 py-8 text-center text-gray-500 italic">
                            No scholars found for this criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
