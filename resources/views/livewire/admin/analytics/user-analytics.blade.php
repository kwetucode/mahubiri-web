<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">User Analytics</h2>
        
        <div class="flex gap-2">
            <button wire:click="$set('period', 7)" 
                    class="px-4 py-2 rounded-lg {{ $period === 7 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                7 Days
            </button>
            <button wire:click="$set('period', 14)" 
                    class="px-4 py-2 rounded-lg {{ $period === 14 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                14 Days
            </button>
            <button wire:click="$set('period', 30)" 
                    class="px-4 py-2 rounded-lg {{ $period === 30 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                30 Days
            </button>
            <button wire:click="$set('period', 90)" 
                    class="px-4 py-2 rounded-lg {{ $period === 90 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                90 Days
            </button>
        </div>
    </div>

    <!-- Retention Metrics -->
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Retention Metrics</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">D+1 Retention</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($retentionMetrics['retention_d1'], 1) }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $retentionMetrics['returning_d1'] }}/{{ $retentionMetrics['new_users'] }} users</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">D+7 Retention</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($retentionMetrics['retention_d7'], 1) }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $retentionMetrics['returning_d7'] }}/{{ $retentionMetrics['new_users'] }} users</p>
                    </div>
                    <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">D+30 Retention</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($retentionMetrics['retention_d30'], 1) }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $retentionMetrics['returning_d30'] }}/{{ $retentionMetrics['new_users'] }} users</p>
                    </div>
                    <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Engagement Metrics</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Active Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($engagementMetrics['active_users']) }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Avg. Sessions/User</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($engagementMetrics['avg_sessions'], 1) }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Avg. Plays/Session</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($engagementMetrics['avg_plays_per_session'], 1) }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Favorite Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($engagementMetrics['favorite_rate'], 1) }}%</p>
            </div>
        </div>
    </div>

    <!-- Conversion Stats -->
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Conversion Stats</h3>
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">New Users</p>
                    <p class="mb-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($conversionStats['new_users']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Registered in period</p>
                </div>
                <div>
                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">First Day Listeners</p>
                    <p class="mb-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($conversionStats['first_day_listeners']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($conversionStats['first_day_listener_rate'], 1) }}% of new users</p>
                </div>
                <div>
                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Church Activations</p>
                    <p class="mb-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($conversionStats['activated_churches']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($conversionStats['church_activation_rate'], 1) }}% uploaded sermon</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Segmentation -->
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Activity by Role</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Active</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg Plays</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg Favorites</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($roleSegmentation as $role)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $role['role_name'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($role['total_users']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($role['active_users']) }}
                                <span class="text-xs text-gray-400">({{ number_format($role['activity_rate'], 1) }}%)</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($role['avg_plays'], 1) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($role['avg_favorites'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Users -->
    <div>
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Top 10 Most Active Users</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Plays</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Favorites</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($topUsers as $index => $user)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->roles->first()?->name ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($user->views_count) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($user->favorites_count) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->last_view_at?->diffForHumans() ?? 'Never' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
