<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">API Request Logs</h2>
        <div class="flex gap-2">
            <button wire:click="export" class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700">
                <svg class="inline h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export
            </button>
            <button wire:click="$refresh" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                <svg class="inline h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Total Requests</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($recentActivity->total()) }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Success Rate</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                {{ number_format(collect($statusDistribution)->where('status_code', 200)->sum('count') / max(collect($statusDistribution)->sum('count'), 1) * 100, 1) }}%
            </p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Avg Response Time</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format(collect($endpointStatistics)->avg('avg_response_time'), 0) }} ms
            </p>
        </div>
        <div class="rounded-ring bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Error Rate</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                {{ number_format((collect($statusDistribution)->whereIn('status_code', [400, 401, 403, 404, 500])->sum('count') / max(collect($statusDistribution)->sum('count'), 1)) * 100, 1) }}%
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-4">
        <input wire:model.live="searchUser" type="text" placeholder="Search user..." 
               class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        
        <input wire:model.live="searchEndpoint" type="text" placeholder="Search endpoint..." 
               class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        
        <select wire:model.live="filterStatus" class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">All Status</option>
            <option value="200">200 OK</option>
            <option value="201">201 Created</option>
            <option value="400">400 Bad Request</option>
            <option value="401">401 Unauthorized</option>
            <option value="404">404 Not Found</option>
            <option value="500">500 Server Error</option>
        </select>

        <input wire:model.live="filterDate" type="date" 
               class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>

    <!-- Recent Activity Table -->
    <div class="mb-6 overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Endpoint</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Response Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse($recentActivity as $activity)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $activity->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $activity->user->name ?? 'Guest' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $activity->method === 'POST' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                {{ $activity->method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activity->endpoint }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ in_array($activity->status_code, [200, 201]) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $activity->status_code }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activity->response_time }} ms</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No activity found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            {{ $recentActivity->links() }}
        </div>
    </div>

    <!-- Endpoint Statistics -->
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Endpoint Statistics</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Endpoint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Requests</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg Response Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Success Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($endpointStatistics as $stat)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $stat['endpoint'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($stat['total_requests']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format($stat['avg_response_time']) }} ms</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-green-500" style="width: {{ $stat['success_rate'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($stat['success_rate'], 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Distribution -->
    <div>
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Status Code Distribution</h3>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            @foreach($statusDistribution as $status)
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-400">{{ $status['status_code'] }}</p>
                    <p class="text-2xl font-bold {{ in_array($status['status_code'], [200, 201]) ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($status['count']) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $status['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
