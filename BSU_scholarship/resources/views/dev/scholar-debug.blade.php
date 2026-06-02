<!DOCTYPE html>
<html>
<head>
    <title>Scholar Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .section { margin: 20px 0; padding: 15px; background: #2d2d2d; border-left: 3px solid #007acc; }
        .label { color: #4ec9b0; font-weight: bold; }
        .value { color: #ce9178; }
        .error { color: #f48771; }
        .success { color: #4ec9b0; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background: #3c3c3c; }
    </style>
</head>
<body>
    <h1>Scholar Debug Information</h1>
    
    @php
        $sfaoUser = \App\Models\User::with('campus')->find(session('user_id'));
        $sfaoCampus = $sfaoUser->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id')->toArray();
        
        // Get all scholars
        $allScholars = \App\Models\Scholar::with('user', 'scholarship')->get();
        
        // Get all campuses
        $allCampuses = \App\Models\Campus::all();
    @endphp
    
    <div class="section">
        <div class="label">SFAO User:</div>
        <div class="value">ID: {{ $sfaoUser->id }} | Name: {{ $sfaoUser->name }} | Email: {{ $sfaoUser->email }}</div>
        <div class="value">Campus: {{ $sfaoCampus->name ?? 'None' }} (ID: {{ $sfaoCampus->id ?? 'N/A' }})</div>
        <div class="value">Managed Campus IDs: {{ json_encode($campusIds) }}</div>
    </div>
    
    <div class="section">
        <div class="label">All Campuses in System:</div>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Parent ID</th>
            </tr>
            @foreach($allCampuses as $campus)
            <tr>
                <td>{{ $campus->id }}</td>
                <td>{{ $campus->name }}</td>
                <td>{{ $campus->parent_id ?? 'NULL' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="section">
        <div class="label">All Scholars in System:</div>
        @if($allScholars->isEmpty())
            <div class="error">NO SCHOLARS FOUND IN DATABASE!</div>
        @else
            <table>
                <tr>
                    <th>Scholar ID</th>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>User Campus ID</th>
                    <th>User Campus Name</th>
                    <th>Scholarship ID</th>
                    <th>Scholarship Name</th>
                    <th>Status</th>
                    <th>Has Email?</th>
                    <th>Matches Filter?</th>
                </tr>
                @foreach($allScholars as $scholar)
                <tr>
                    <td>{{ $scholar->id }}</td>
                    <td>{{ $scholar->user_id }}</td>
                    <td>{{ $scholar->user->name ?? 'NULL' }}</td>
                    <td>{{ $scholar->user->campus_id ?? 'NULL' }}</td>
                    <td>{{ $scholar->user->campus->name ?? 'NULL' }}</td>
                    <td>{{ $scholar->scholarship_id }}</td>
                    <td>{{ $scholar->scholarship->scholarship_name ?? 'NULL' }}</td>
                    <td class="{{ $scholar->status === 'active' ? 'success' : 'error' }}">{{ $scholar->status }}</td>
                    <td class="{{ $scholar->user && $scholar->user->email ? 'success' : 'error' }}">{{ $scholar->user && $scholar->user->email ? 'YES' : 'NO' }}</td>
                    <td class="{{ in_array($scholar->user->campus_id ?? null, $campusIds) && $scholar->status === 'active' ? 'success' : 'error' }}">
                        {{ in_array($scholar->user->campus_id ?? null, $campusIds) && $scholar->status === 'active' ? 'YES' : 'NO' }}
                    </td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>
    
    <div class="section">
        <div class="label">Diagnosis:</div>
        @php
            $matchingScholars = $allScholars->filter(function($s) use ($campusIds) {
                return $s->status === 'active' 
                    && $s->user 
                    && in_array($s->user->campus_id, $campusIds)
                    && $s->user->email;
            });
        @endphp
        <div class="value">Total Scholars: {{ $allScholars->count() }}</div>
        <div class="value">Active Scholars: {{ $allScholars->where('status', 'active')->count() }}</div>
        <div class="value {{ $matchingScholars->count() > 0 ? 'success' : 'error' }}">
            Scholars Matching Filter: {{ $matchingScholars->count() }}
        </div>
        
        @if($matchingScholars->count() === 0 && $allScholars->count() > 0)
            <div class="error" style="margin-top: 10px;">
                <strong>Problem Found:</strong><br>
                @if($allScholars->where('status', '!=', 'active')->count() > 0)
                    - Some scholars have status != 'active'<br>
                @endif
                @if($allScholars->filter(fn($s) => !$s->user)->count() > 0)
                    - Some scholars have no linked user<br>
                @endif
                @if($allScholars->filter(fn($s) => $s->user && !in_array($s->user->campus_id, $campusIds))->count() > 0)
                    - Some scholars' users are in different campuses than SFAO manages<br>
                @endif
                @if($allScholars->filter(fn($s) => $s->user && !$s->user->email)->count() > 0)
                    - Some scholars' users have no email<br>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
