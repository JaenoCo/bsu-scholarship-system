<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentImportController extends Controller
{
    public function store(Request $request)
    {
        if (! session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240',
        ]);

        $sfao = User::with('campus.extensionCampuses')->findOrFail(session('user_id'));
        $managedCampuses = $sfao->campus->getAllCampusesUnder();
        $managedCampusIds = $managedCampuses->pluck('id')->map(fn ($id) => (int) $id);
        $rows = $this->readRows(
            $request->file('file')->getRealPath(),
            strtolower($request->file('file')->getClientOriginalExtension())
        );

        if (count($rows) < 2) {
            return back()->with('error', 'Import file must contain a header row and at least one student row.');
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $data = $this->mapRow($headers, $row);
                if ($this->isBlankRow($data)) {
                    continue;
                }

                $srCode = $this->nullableString($data['sr_code'] ?? $data['student_number'] ?? $data['student_id'] ?? null);
                $email = $this->nullableString($data['email'] ?? null);
                $name = $this->nullableString($data['name'] ?? null);
                $firstName = $this->nullableString($data['first_name'] ?? null);
                $lastName = $this->nullableString($data['last_name'] ?? null);

                if (! $srCode) {
                    $this->error($result, $line, 'Missing sr_code/student_number.');
                    continue;
                }
                if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->error($result, $line, 'Missing or invalid email.');
                    continue;
                }
                if (! $name && (! $firstName || ! $lastName)) {
                    $this->error($result, $line, 'Provide name or both first_name and last_name.');
                    continue;
                }

                $campusId = $this->resolveCampusId($data, $managedCampuses, (int) $sfao->campus_id);
                if (! $campusId || ! $managedCampusIds->contains($campusId)) {
                    $this->error($result, $line, 'Campus is missing, invalid, or outside this SFAO scope.');
                    continue;
                }

                $parts = preg_split('/\s+/', trim((string) $name), 2);
                $payload = [
                    'name' => $name ?: trim($firstName . ' ' . ($data['middle_name'] ?? '') . ' ' . $lastName),
                    'first_name' => $firstName ?: ($parts[0] ?? ''),
                    'middle_name' => $this->nullableString($data['middle_name'] ?? null),
                    'last_name' => $lastName ?: ($parts[1] ?? ''),
                    'email' => $email,
                    'sr_code' => $srCode,
                    'campus_id' => $campusId,
                    'college' => $this->nullableString($data['college'] ?? null),
                    'program' => $this->nullableString($data['program'] ?? null),
                    'track' => $this->nullableString($data['track'] ?? null),
                    'year_level' => $this->nullableString($data['year_level'] ?? null),
                    'education_level' => $this->nullableString($data['education_level'] ?? 'college'),
                    'role' => 'student',
                ];

                $student = User::where('sr_code', $srCode)->orWhere('email', $email)->first();
                if ($student && $student->role !== 'student') {
                    $this->error($result, $line, 'sr_code or email belongs to a non-student account.');
                    continue;
                }

                if ($student) {
                    $student->update($payload);
                    $result['updated']++;
                } else {
                    $payload['password'] = Hash::make(Str::random(32));
                    User::create($payload);
                    $result['created']++;
                }
            }
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);
            return back()->with('error', 'Student import failed. Please check the file format and try again.');
        }

        return redirect()
            ->route('sfao.dashboard', ['tabs' => 'import-students'])
            ->with('success', 'Student import completed.')
            ->with('student_import_result', $result);
    }

    private function readRows(string $path, string $extension): array
    {
        if (in_array($extension, ['csv', 'txt'], true)) {
            $handle = fopen($path, 'r');
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
            return $rows;
        }

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header) => Str::of((string) $header)->lower()->replace([' ', '-', '.'], '_')->replaceMatches('/_+/', '_')->trim('_')->toString(), $headers);
    }

    private function mapRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            if ($header !== '') {
                $data[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }
        }
        return $data;
    }

    private function resolveCampusId(array $data, $managedCampuses, int $default): ?int
    {
        $value = $this->nullableString($data['campus_id'] ?? $data['campus'] ?? $data['campus_name'] ?? null);
        if (! $value) {
            return $default;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $campus = $managedCampuses->first(fn (Campus $campus) => Str::lower($campus->name) === Str::lower($value));
        return $campus ? (int) $campus->id : null;
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function isBlankRow(array $data): bool
    {
        return collect($data)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function error(array &$result, int $line, string $message): void
    {
        $result['skipped']++;
        $result['errors'][] = "Row {$line}: {$message}";
    }
}
