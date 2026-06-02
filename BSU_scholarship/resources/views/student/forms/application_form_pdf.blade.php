<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Scholarship Application Form</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.2; margin: 10px; }
        h1 { text-align: center; color: #b91c1c; font-size: 14px; margin: 0; }
        p { margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin: 0; }
        td, th { border: 1px solid #000; padding: 2px 4px; vertical-align: top; }
        .section-title { font-weight: bold; background: #f2f2f2; font-size: 11px; padding: 2px; text-transform: uppercase; }
        .checkbox { font-size: 11px; margin-right: 4px; }
        .signature { margin-top: 30px; text-align: right; }
    </style>
</head>
<body>

    <h1>APPLICATION FORM FOR STUDENT SCHOLARSHIP / FINANCIAL ASSISTANCE</h1>
    <p style="text-align:center;">Directions: Fill out the necessary information below.</p>

    <!-- Scholarship Info -->
    <table>
        <tr>
            <td width="50%">
                Scholarship Applied For:
                <span class="checkbox">[{{ $form->scholarship_type == 'private' ? 'x' : ' ' }}]</span> Private
                <span class="checkbox">[{{ $form->scholarship_type == 'government' ? 'x' : ' ' }}]</span> Government
            </td>
            <td>Name of Scholarship: <strong>{{ $form->scholarship_name ?? '' }}</strong></td>
        </tr>
    </table>

    <!-- Personal Data -->
    <div class="section-title">Personal Data</div>
    <table>
        <tr>
            <td width="33%">Last Name: {{ $form->last_name }}</td>
            <td width="33%">First Name: {{ $form->first_name }}</td>
            <td width="34%">Middle Name: {{ $form->middle_name }}</td>
        </tr>
        <tr>
            <td colspan="3">Permanent Home Address: {{ $form->address }}, Zip: {{ $form->zip_code }}</td>
        </tr>
        <tr>
            <td>Age: {{ $form->age }}</td>
            <td>Sex: {{ $form->sex }}</td>
            <td>Civil Status: {{ $form->civil_status }}</td>
        </tr>
        <tr>
            <td>Birthdate: {{ $form->birthdate?->format('F d, Y') }}</td>
            <td>Birthplace: {{ $form->birthplace }}</td>
            <td>Disability: {{ $form->disability }}</td>
        </tr>
        <tr>
            <td>Tribe: {{ $form->tribe }}</td>
            <td>Citizenship: {{ $form->citizenship }}</td>
            <td>Birth Order: {{ $form->birth_order }}</td>
        </tr>
        <tr>
            <td>Email: {{ $form->email }}</td>
            <td>Telephone: {{ $form->telephone }}</td>
            <td>Religion: {{ $form->religion }}</td>
        </tr>
        <tr>
            <td colspan="3">
                High School Type:
                <span class="checkbox">[{{ $form->highschool_type == 'Public' ? 'x' : ' ' }}]</span> Public
                <span class="checkbox">[{{ $form->highschool_type == 'Private' ? 'x' : ' ' }}]</span> Private
            </td>
        </tr>
        <tr>
            <td>Monthly Allowance: {{ $form->monthly_allowance }}</td>
            <td colspan="2">Living Arrangement: {{ $form->living_arrangement }}</td>
        </tr>
        <tr>
            <td colspan="3">Transportation: {{ $form->transportation }}</td>
        </tr>
    </table>

    <!-- Academic Data -->
    <div class="section-title">Academic Data</div>
    <table>
        <tr>
            <td colspan="2">
                <span class="checkbox">{{ $form->education_level == 'Undergraduate' ? '☑' : '☐' }}</span> Undergraduate
                <span class="checkbox">{{ $form->education_level == 'Graduate' ? '☑' : '☐' }}</span> Graduate
                <span class="checkbox">{{ $form->education_level == 'Integrated' ? '☑' : '☐' }}</span> Integrated School
            </td>
        </tr>
        <tr>
            <td>Program: {{ $form->program }}</td>
            <td>College: {{ $form->college }}</td>
        </tr>
        <tr>
            <td>Year Level: {{ $form->year_level }}</td>
            <td>Campus: {{ $form->campus }}</td>
        </tr>
        <tr>
            <td>GWA: {{ $form->gwa }}</td>
            <td>Honors: {{ $form->honors }}</td>
        </tr>
        <tr>
            <td>Units Enrolled: {{ $form->units_enrolled }}</td>
            <td>Academic Year: {{ $form->academic_year }}</td>
        </tr>
        <tr>
            <td colspan="2">
                Existing Scholarship?
                <span class="checkbox">{{ $form->has_existing_scholarship ? '☑' : '☐' }}</span> Yes
                <span class="checkbox">{{ !$form->has_existing_scholarship ? '☑' : '☐' }}</span> No
                <br>Details: {{ $form->existing_scholarship_details }}
            </td>
        </tr>
    </table>

    <!-- Family Data -->
    <div class="section-title">Family Data</div>
    <table>
        <tr>
            <td colspan="2"><strong>Father</strong> ({{ $form->father_living ? 'Living' : 'Deceased' }})</td>
            <td colspan="2"><strong>Mother</strong> ({{ $form->mother_living ? 'Living' : 'Deceased' }})</td>
        </tr>
        <tr>
            <td>Name: {{ $form->father_name }}</td>
            <td>Age: {{ $form->father_age }}</td>
            <td>Name: {{ $form->mother_name }}</td>
            <td>Age: {{ $form->mother_age }}</td>
        </tr>
        <tr>
            <td>Residence: {{ $form->father_residence }}</td>
            <td>Education: {{ $form->father_education }}</td>
            <td>Residence: {{ $form->mother_residence }}</td>
            <td>Education: {{ $form->mother_education }}</td>
        </tr>
        <tr>
            <td>Contact: {{ $form->father_contact }}</td>
            <td>Occupation: {{ $form->father_occupation }}</td>
            <td>Contact: {{ $form->mother_contact }}</td>
            <td>Occupation: {{ $form->mother_occupation }}</td>
        </tr>
        <tr>
            <td>Company: {{ $form->father_company }}</td>
            <td>Employment: {{ $form->father_employment_status }}</td>
            <td>Company: {{ $form->mother_company }}</td>
            <td>Employment: {{ $form->mother_employment_status }}</td>
        </tr>
    </table>

    <p style="margin:15px 0; text-align:center;">
        I hereby attest to the truthfulness and accuracy of the above information.
    </p>
    <div class="signature">
        ___________________________<br>
        Signature over Printed Name
    </div>

</body>
</html>
