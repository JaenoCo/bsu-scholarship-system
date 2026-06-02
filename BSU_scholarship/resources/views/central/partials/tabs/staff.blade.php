<!-- Staff Management Tab -->
<div x-show="tab === 'staff'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     class="space-y-6">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-white mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        Manage Admins
    </h2>
    
    <!-- Create New SFAO Admin -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Create New SFAO Admin</h3>
      
      <form method="POST" action="{{ route('central.staff.invite') }}" class="space-y-4">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Full Name
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name') }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 
                          focus:ring-2 focus:ring-bsu-red focus:border-transparent
                          dark:bg-gray-700 dark:text-white">
            @error('name')
              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Email Address
            </label>
            <input type="email" id="email" name="email" required
                   value="{{ old('email') }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 
                          focus:ring-2 focus:ring-bsu-red focus:border-transparent
                          dark:bg-gray-700 dark:text-white">
            @error('email')
              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Campus Assignment -->
        <div>
          <label for="campus_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Campus Assignment
          </label>
          <select id="campus_id" name="campus_id" required
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 
                         focus:ring-2 focus:ring-bsu-red focus:border-transparent
                         dark:bg-gray-700 dark:text-white">
            <option value="">Select Campus</option>
            @foreach(\App\Models\Campus::constituent()->withSfaoAdmin()->get() as $campus)
              <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                {{ $campus->name }}
              </option>
            @endforeach
          </select>
          @error('campus_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
          <button type="submit" 
                  class="bg-bsu-red text-white px-6 py-2 rounded-lg hover:bg-bsu-redDark 
                         transition duration-200 flex items-center space-x-2">
            <span class="bg-gray-200 dark:bg-gray-700 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
            <span>Create Account & Send Verification</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Current Staff -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Current SFAO Staff</h3>
      
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-700 rounded-lg shadow">
          <thead class="bg-gray-50 dark:bg-gray-600">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Campus
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
            @forelse(\App\Models\User::where('role', 'sfao')->whereNotNull('email_verified_at')->with(['campus', 'invitation'])->get() as $staff)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-bsu-red flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                          {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $staff->name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ $staff->email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ $staff->campus->name ?? 'Not Assigned' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                               bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Active
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <form method="POST" action="{{ route('central.staff.deactivate', $staff->id) }}" 
                        class="inline" 
                        onsubmit="return confirm('WARNING: This will permanently remove {{ $staff->name }} from the system. Their account will be completely deleted and cannot be recovered. Are you sure you want to proceed?')">
                    @csrf
                    <button type="submit" 
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 
                                   hover:underline font-semibold">
                      <span class="flex items-center gap-1">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Remove Staff
                      </span>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No SFAO staff members found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Invitation Status -->
    <div>
      <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Invitation Status</h3>
      
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-700 rounded-lg shadow">
          <thead class="bg-gray-50 dark:bg-gray-600">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Campus
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Invited
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Verified
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
            @forelse(\App\Models\Invitation::with(['campus', 'inviter'])->orderBy('created_at', 'desc')->get() as $invitation)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full 
                        @if($invitation->status === 'active') bg-green-400
                        @elseif($invitation->status === 'pending') bg-yellow-400
                        @elseif($invitation->status === 'removed') bg-gray-400
                        @else bg-red-400
                        @endif flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                          {{ strtoupper(substr($invitation->name, 0, 1)) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $invitation->name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ $invitation->email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ $invitation->campus->name ?? 'Not Assigned' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ $invitation->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                    @if($invitation->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                    @elseif($invitation->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                    @elseif($invitation->status === 'removed') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                    @endif">
                    {{ ucfirst($invitation->status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  @if($invitation->accepted_at)
                    {{ $invitation->accepted_at->format('M d, Y H:i') }}
                  @else
                    <span class="text-gray-400">Not verified</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No invitations found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
