<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Storage Monitor</h2>
        <button wire:click="$refresh" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            <svg class="inline h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>

    <!-- Global Overview -->
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Total Churches</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format(count($churchStorageUsage)) }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Total Storage Used</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format(collect($churchStorageUsage)->sum('used_bytes') / 1024 / 1024 / 1024, 2) }} GB
            </p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Churches at Risk</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                {{ collect($churchStorageUsage)->where('status', 'critical')->count() }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">&gt;90% quota used</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Average Usage</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format(collect($churchStorageUsage)->avg('percentage_used'), 1) }}%
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex gap-4">
        <input wire:model.live="search" type="text" placeholder="Search church..." 
               class="flex-1 rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        
        <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">All Status</option>
            <option value="normal">Normal</option>
            <option value="warning">Warning</option>
            <option value="critical">Critical</option>
        </select>

        <select wire:model.live="sortBy" class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="usage_desc">Usage (High to Low)</option>
            <option value="usage_asc">Usage (Low to High)</option>
            <option value="name_asc">Name (A-Z)</option>
            <option value="sermons_desc">Sermons (Most)</option>
        </select>
    </div>

    <!-- Church Storage Table -->
    <div class="mb-6 overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Church</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sermons</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Storage Used</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Quota</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Usage %</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Remaining</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse($churchStorageUsage as $church)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $church['church_name'] }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($church['sermon_count']) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($church['used_bytes'] / 1024 / 1024, 2) }} MB
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($church['quota_bytes'] / 1024 / 1024 / 1024, 1) }} GB
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full {{ $church['status'] === 'critical' ? 'bg-red-600' : ($church['status'] === 'warning' ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                         style="width: {{ min($church['percentage_used'], 100) }}%"></div>
                                </div>
                                <span class="text-sm {{ $church['status'] === 'critical' ? 'text-red-600 font-bold' : ($church['status'] === 'warning' ? 'text-yellow-600 font-semibold' : 'text-gray-500 dark:text-gray-400') }}">
                                    {{ number_format($church['percentage_used'], 1) }}%
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($church['remaining_bytes'] / 1024 / 1024, 2) }} MB
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $church['status'] === 'critical' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ($church['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                                {{ ucfirst($church['status']) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No churches found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Saturation Forecast -->
    @if(count($saturationForecast) > 0)
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Saturation Forecast</h3>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="space-y-3">
                    @foreach($saturationForecast as $forecast)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $forecast['church_name'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($forecast['avg_daily_upload'] / 1024 / 1024, 2) }} MB/day average upload
                                </p>
                            </div>
                            <div class="text-right">
                                @if($forecast['days_until_full'] !== null)
                                    <p class="text-lg font-bold {{ $forecast['days_until_full'] < 30 ? 'text-red-600' : ($forecast['days_until_full'] < 90 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $forecast['days_until_full'] }} days
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">until full</p>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No forecast available</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Largest Sermons -->
    <div>
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Top 10 Largest Sermons</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sermon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Church</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Uploaded</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($largestSermons as $index => $sermon)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($sermon->title, 50) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $sermon->church->name ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($sermon->size / 1024 / 1024, 2) }} MB
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $sermon->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No sermons found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
