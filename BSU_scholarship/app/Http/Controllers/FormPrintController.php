<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Form;
use PhpOffice\PhpWord\TemplateProcessor;

class FormPrintController extends Controller
{
    /**
     * Print application form as PDF
     * 
     * 1. Fetches the form data from DB (Form::find($id))
     * 2. Loads the official .docx template (storage/app/templates/...)
     * 3. Uses PHPWord to replace placeholders with database values
     * 4. Saves the filled file as .docx
     * 5. Converts it to .pdf using LibreOffice
     * 6. Returns a download response (PDF, or DOCX if conversion fails)
     */
    public function printApplication($scholarship_id = null)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::find(session('user_id'));
        
        // Get the user's form (one form per user)
        $form = Form::where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        if (!$form) {
            return redirect()->back()->with('error', 'Application form not found. Please save the form first before printing.');
        }
        
        // Refresh the form model to ensure we have the latest data
        $form->refresh();

        // Determine form type from request
        $formType = request('type', 'sfao'); // Default to 'sfao'

        // Path to your Word template
        // Primary location: storage/app/templates/ (secure, not web-accessible)
        if ($formType === 'tdp') {
            $templatePath = storage_path('app/templates/Annex 1 TDP Application Form_Template.docx');
        } else {
            $templatePath = storage_path('app/templates/BatStateU-FO-SFA-01_Application Form_Template_Final.docx');
        }
        
        if (!file_exists($templatePath)) {
            // Fallback to alternative filename (only for SFAO usually)
            if ($formType === 'sfao') {
                $templatePath = storage_path('app/templates/application_form_template.docx');
            }
        }
        
        if (!file_exists($templatePath)) {
            // Last fallback: resources/forms/ (for backward compatibility)
            if ($formType === 'sfao') {
                $templatePath = resource_path('forms/BatStateU-FO-SFA-01_Application Form_Template_Final.docx');
            }
        }
        
        if (!file_exists($templatePath)) {
            $templateName = $formType === 'tdp' ? 'Annex 1 TDP Application Form_Template.docx' : 'BatStateU-FO-SFA-01_Application Form_Template_Final.docx';
            return redirect()->back()->with('error', "Template file not found. Please ensure the template exists at: storage/app/templates/{$templateName}");
        }

        // Load the template
        $templateProcessor = new TemplateProcessor($templatePath);

        // Map estimated_gross_annual_income to readable text
        $incomeLabels = [
            'not_over_250000' => 'Not over P 250,000.00',
            'over_250000_not_over_400000' => 'Over P 250,000 but not over P 400,000',
            'over_400000_not_over_800000' => 'Over P 400,000 but not over P 800,000',
            'over_800000_not_over_2000000' => 'Over P 800,000 but not over P 2,000,000',
            'over_2000000_not_over_8000000' => 'Over P 2,000,000 but not over P 8,000,000',
            'over_8000000' => 'Over P 8,000,000'
        ];

        // Format birthdate for display (mm/dd/yyyy)
        $birthdateFormatted = $form->birthdate ? $form->birthdate->format('m/d/Y') : '';
        $birthdateMonth = $form->birthdate ? $form->birthdate->format('m') : '';
        $birthdateDay = $form->birthdate ? $form->birthdate->format('d') : '';
        $birthdateYear = $form->birthdate ? $form->birthdate->format('Y') : '';

        // Recalculate age from birthdate (same logic as JavaScript in the form)
        // This ensures age is always current, not using outdated stored value
        $calculatedAge = '';
        if ($form->birthdate) {
            $today = now();
            $birthdate = $form->birthdate;
            $age = $today->year - $birthdate->year;
            $monthDiff = $today->month - $birthdate->month;
            if ($monthDiff < 0 || ($monthDiff === 0 && $today->day < $birthdate->day)) {
                $age--;
            }
            $calculatedAge = $age > 0 ? $age : '';
        }

        // Format date_signed
        $dateSignedFormatted = $form->date_signed ? $form->date_signed->format('m/d/Y') : '';

        // Handle education level checkboxes
        $edu_undergrad = ' ';
        $edu_grad = ' ';
        $edu_integrated = ' ';

        switch ($form->education_level) {
            case 'Undergraduate':
                $edu_undergrad = '/';
                break;
            case 'Graduate School':
                $edu_grad = '/';
                break;
            case 'Integrated School':
                $edu_integrated = '/';
                break;
        }

        // Handle existing scholarship checkboxes
        $scholarship_yes = ' ';
        $scholarship_no = ' ';
        
        if ($form->has_existing_scholarship) {
            $scholarship_yes = '/';
        } else {
            $scholarship_no = '/';
        }

        // Initialize all income brackets as blank
        $inc_1 = $inc_2 = $inc_3 = $inc_4 = $inc_5 = $inc_6 = ' ';

        switch ($form->estimated_gross_annual_income) {
            case 'not_over_250000':
                $inc_1 = '/';
                break;
            case 'over_250000_not_over_400000':
                $inc_2 = '/';
                break;
            case 'over_400000_not_over_800000':
                $inc_3 = '/';
                break;
            case 'over_800000_not_over_2000000':
                $inc_4 = '/';
                break;
            case 'over_2000000_not_over_8000000':
                $inc_5 = '/';
                break;
            case 'over_8000000':
                $inc_6 = '/';
                break;
        }

        // Initialize all parent status checkboxes as blank
        $father_living = $father_deceased = $mother_living = $mother_deceased = ' ';

        // Father status
        if ($form->father_status === 'living') {
            $father_living = '/';
        } elseif ($form->father_status === 'deceased') {
            $father_deceased = '/';
        }

        // Mother status
        if ($form->mother_status === 'living') {
            $mother_living = '/';
        } elseif ($form->mother_status === 'deceased') {
            $mother_deceased = '/';
        }

        // Replace all placeholders with form data
        $replacements = [
            // Personal Data
            '{{last_name}}' => $form->last_name ?? '',
            '{{first_name}}' => $form->first_name ?? '',
            '{{middle_name}}' => $form->middle_name ?? '',
            '{{age}}' => $calculatedAge !== '' ? $calculatedAge : ($form->age ?? ''),
            '{{age}}' => $calculatedAge !== '' ? $calculatedAge : ($form->age ?? ''),
            '{{sex}}' => ucfirst($form->sex ?? ''),
            '{{sex_male}}' => (strtolower($form->sex ?? '') === 'male') ? '/' : ' ',
            '{{sex_female}}' => (strtolower($form->sex ?? '') === 'female') ? '/' : ' ',
            '{{civil_status}}' => $form->civil_status ?? '',
            '{{birthdate}}' => $birthdateFormatted,
            '{{birthdate_month}}' => $birthdateMonth,
            '{{birthdate_day}}' => $birthdateDay,
            '{{birthdate_year}}' => $birthdateYear,
            '{{birth_mm}}' => $birthdateMonth,
            '{{birth_dd}}' => $birthdateDay,
            '{{birth_yyyy}}' => $birthdateYear,
            '{{birthplace}}' => $form->birthplace ?? '',
            '{{email}}' => $form->email ?? '',
            '{{contact_number}}' => $form->contact_number ?? '',
            '{{mobile_number}}' => $form->contact_number ?? '',
            '{{street_barangay}}' => $form->street_barangay ?? '',
            '{{town_city}}' => $form->town_city ?? '',
            '{{province}}' => $form->province ?? '',
            '{{permanent_address}}' => implode(', ', array_filter([
                $form->street_barangay ?? null,
                $form->town_city ?? null,
                $form->province ?? null
            ])),
            '{{zip_code}}' => $form->zip_code ?? '',
            '{{citizenship}}' => $form->citizenship ?? '',
            '{{disability}}' => $form->disability ?? '',
            '{{tribe}}' => $form->tribe ?? '',

            // Academic Data
            '{{sr_code}}' => $form->sr_code ?? '',
            '{{education_level}}' => $form->education_level ?? '',
            '{{edu_undergrad}}' => $edu_undergrad,
            '{{edu_grad}}' => $edu_grad,
            '{{edu_integrated}}' => $edu_integrated,
            '{{program}}' => $form->program ?? '',
            '{{college_department}}' => $form->college_department ?? '',
            '{{year_level}}' => $form->year_level ?? '',
            '{{campus}}' => $form->campus ?? '',
            '{{previous_gwa}}' => $form->previous_gwa ?? '',
            '{{honors_received}}' => $form->honors_received ?? '',
            '{{units_enrolled}}' => $form->units_enrolled ?? '',
            '{{scholarship_applied}}' => $form->scholarship_applied ?? '',
            '{{semester}}' => $form->semester ?? '',
            '{{academic_year}}' => $form->academic_year ?? '',
            '{{has_existing_scholarship}}' => $form->has_existing_scholarship ? 'Yes' : 'No',
            '{{scholarship_yes}}' => $scholarship_yes,
            '{{scholarship_no}}' => $scholarship_no,
            '{{existing_scholarship_details}}' => trim($form->existing_scholarship_details ?? ''),

            // Family Data
            '{{father_status}}' => ucfirst($form->father_status ?? ''),
            '{{father_living}}' => $father_living,
            '{{father_deceased}}' => $father_deceased,
            '{{father_name}}' => $form->father_name ?? '',
            '{{father_address}}' => $form->father_address ?? '',
            '{{father_contact}}' => $form->father_contact ?? '',
            '{{father_occupation}}' => $form->father_occupation ?? '',
            '{{mother_status}}' => ucfirst($form->mother_status ?? ''),
            '{{mother_living}}' => $mother_living,
            '{{mother_deceased}}' => $mother_deceased,
            '{{mother_name}}' => $form->mother_name ?? '',
            '{{mother_address}}' => $form->mother_address ?? '',
            '{{mother_contact}}' => $form->mother_contact ?? '',
            '{{mother_occupation}}' => $form->mother_occupation ?? '',
            '{{estimated_gross_annual_income}}' => $incomeLabels[$form->estimated_gross_annual_income] ?? ($form->estimated_gross_annual_income ?? ''),
            '{{total_parent_gross_income}}' => $incomeLabels[$form->estimated_gross_annual_income] ?? ($form->estimated_gross_annual_income ?? ''),
            '{{inc_1}}' => $inc_1,
            '{{inc_2}}' => $inc_2,
            '{{inc_3}}' => $inc_3,
            '{{inc_4}}' => $inc_4,
            '{{inc_5}}' => $inc_5,
            '{{inc_6}}' => $inc_6,
            '{{siblings_count}}' => $form->siblings_count ?? '',

            // Essay / Question
            '{{reason_for_applying}}' => $form->reason_for_applying ?? '',

            // Certification
            '{{student_signature}}' => $form->student_signature ?? '',
            '{{date_signed}}' => $dateSignedFormatted,
        ];

        // Replace all placeholders in the template
        // PHPWord TemplateProcessor expects ${variable} format in the Word document
        // We extract the variable name from {{variable}} or [[variable]] format
        foreach ($replacements as $placeholder => $value) {
            // Extract variable name by removing {{, }}, [[, ]], or ${, }
            // Remove opening delimiters: {{, [[, ${
            $variableName = preg_replace('/^(?:\{\{|\[\[|\$\{)/', '', $placeholder);
            // Remove closing delimiters: }}, ]], }
            $variableName = preg_replace('/(?:\}\}|\]\]|})$/', '', $variableName);
            
            // Log replacement for debugging (can be removed in production)
            if (config('app.debug')) {
                Log::debug('Replacing placeholder', [
                    'placeholder' => $placeholder,
                    'variable_name' => $variableName,
                    'value' => $value,
                    'value_type' => gettype($value)
                ]);
            }
            
            // PHPWord setValue expects just the variable name (without ${})
            // Try to replace - if it fails, it means placeholder doesn't exist in template
            try {
                $templateProcessor->setValue($variableName, (string)($value ?? ''));
            } catch (\Exception $e) {
                // Log if replacement fails (placeholder might not exist in template)
                if (config('app.debug')) {
                    Log::warning('Placeholder replacement failed', [
                        'variable_name' => $variableName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Log form data for debugging
        if (config('app.debug')) {
            Log::info('Printing form', [
                'form_id' => $form->id,
                'user_id' => $form->user_id,
                'updated_at' => $form->updated_at,
                'form_data_sample' => [
                    'last_name' => $form->last_name,
                    'first_name' => $form->first_name,
                    'age' => $form->age,
                    'sr_code' => $form->sr_code,
                ]
            ]);
        }

        // Generate filename based on scholarship_applied
        $prefix = $formType === 'tdp' ? 'TDP_Application_Form_' : 'SFAO_Application_Form_';
        
        if (!empty($form->scholarship_applied)) {
            // Clean scholarship name for filename (remove special characters, replace spaces with underscores)
            $scholarshipName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $form->scholarship_applied);
            $scholarshipName = preg_replace('/_+/', '_', $scholarshipName); // Replace multiple underscores with single
            $scholarshipName = trim($scholarshipName, '_'); // Remove leading/trailing underscores
            $filename = $prefix . $scholarshipName . '.docx';
        } else {
            // Fallback to original format if no scholarship_applied
            $filename = $prefix . $user->id . '_' . date('Y-m-d') . '.docx';
        }
        
        // Save the processed document
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $docxPath = $tempDir . '/' . $filename;
        $templateProcessor->saveAs($docxPath);

        // Convert DOCX to PDF using LibreOffice
        $pdfFilename = str_replace('.docx', '.pdf', $filename);
        $pdfPath = $this->convertToPdf($docxPath, $tempDir);
        
        if ($pdfPath && file_exists($pdfPath)) {
            // Clean up DOCX file after conversion
            if (file_exists($docxPath)) {
                @unlink($docxPath);
            }
            
            // Return PDF for download
            return response()->download($pdfPath, $pdfFilename)->deleteFileAfterSend(true);
        } else {
            // Fallback: if PDF conversion fails, return DOCX with notification
            session()->flash('warning', 'PDF conversion unavailable. LibreOffice is not installed. Downloading DOCX instead. Please install LibreOffice to enable PDF conversion.');
            return response()->download($docxPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ])->deleteFileAfterSend(true);
        }
    }

    /**
     * Convert DOCX to PDF using LibreOffice
     * 
     * @param string $docxPath Path to the DOCX file
     * @param string $outputDir Directory to save the PDF
     * @return string|null Path to the generated PDF file, or null if conversion fails
     */
    private function convertToPdf($docxPath, $outputDir)
    {
        // Check if LibreOffice is installed
        $libreOfficePath = $this->findLibreOffice();
        
        if (!$libreOfficePath) {
            Log::warning('LibreOffice not found. PDF conversion skipped. Returning DOCX instead.', [
                'os' => PHP_OS_FAMILY,
                'checked_paths' => $this->getLibreOfficePaths()
            ]);
            return null;
        }

        // Escape paths for shell command (handle spaces and special characters)
        $escapedDocxPath = escapeshellarg($docxPath);
        $escapedOutputDir = escapeshellarg($outputDir);

        // Build LibreOffice command for headless PDF conversion
        // --headless: Run without GUI
        // --convert-to pdf: Convert to PDF format
        // --outdir: Output directory
        $command = sprintf(
            '%s --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($libreOfficePath),
            $escapedOutputDir,
            $escapedDocxPath
        );

        // Execute conversion
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        // Check if conversion was successful
        if ($returnCode === 0) {
            // LibreOffice generates PDF with the same name as DOCX (just .pdf extension)
            $pdfPath = $outputDir . '/' . basename($docxPath, '.docx') . '.pdf';
            
            if (file_exists($pdfPath)) {
                return $pdfPath;
            }
        }

        // Log error if conversion failed
        Log::error('PDF conversion failed', [
            'command' => $command,
            'return_code' => $returnCode,
            'output' => implode("\n", $output),
            'docx_path' => $docxPath
        ]);

        return null;
    }

    /**
     * Get list of possible LibreOffice paths for current OS
     * 
     * @return array List of possible paths
     */
    private function getLibreOfficePaths()
    {
        $possiblePaths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows paths
            $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
            $programFilesX86 = getenv('ProgramFiles(x86)') ?: 'C:\\Program Files (x86)';
            
            $possiblePaths = [
                $programFiles . '\\LibreOffice\\program\\soffice.exe',
                $programFilesX86 . '\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            ];
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            // macOS paths
            $possiblePaths = [
                '/Applications/LibreOffice.app/Contents/MacOS/soffice',
                '/usr/local/bin/soffice',
                '/opt/homebrew/bin/soffice',
            ];
        } else {
            // Linux paths
            $possiblePaths = [
                '/usr/bin/libreoffice',
                '/usr/bin/soffice',
                '/usr/local/bin/libreoffice',
                '/usr/local/bin/soffice',
                '/snap/bin/libreoffice',
            ];
        }

        return $possiblePaths;
    }

    /**
     * Find LibreOffice executable path
     * 
     * @return string|null Path to LibreOffice executable, or null if not found
     */
    private function findLibreOffice()
    {
        $possiblePaths = $this->getLibreOfficePaths();

        // Check which path exists
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                // On Windows, .exe files don't need is_executable check
                if (PHP_OS_FAMILY === 'Windows' || is_executable($path)) {
                    Log::info('LibreOffice found', ['path' => $path]);
                    return $path;
                }
            }
        }

        // Try to find it in PATH
        if (function_exists('shell_exec')) {
            if (PHP_OS_FAMILY === 'Windows') {
                $foundPath = shell_exec('where soffice 2>nul');
            } else {
                $foundPath = shell_exec('which libreoffice soffice 2>/dev/null');
            }
        } else {
            $foundPath = null;
        }
        
        if ($foundPath) {
            $foundPath = trim($foundPath);
            // Handle multiple results (take first one)
            $foundPath = explode("\n", $foundPath)[0];
            if ($foundPath && file_exists($foundPath)) {
                if (PHP_OS_FAMILY === 'Windows' || is_executable($foundPath)) {
                    Log::info('LibreOffice found in PATH', ['path' => $foundPath]);
                    return $foundPath;
                }
            }
        }

        return null;
    }

    /**
     * Download file route (called from download-redirect view)
     */
    public function downloadFile()
    {
        if (!session()->has('download_file_path')) {
            return redirect()->back()->with('error', 'Download file not found.');
        }

        $filePath = session('download_file_path');
        $filename = session('download_filename');
        
        // Clear session
        session()->forget(['download_file_path', 'download_filename']);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }
}

