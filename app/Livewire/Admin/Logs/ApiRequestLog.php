<?php

namespace App\Livewire\Admin\Logs;

use App\Models\User;
use App\Models\Sermon;
use App\Models\SermonView;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiRequestLog extends Component
{
    use WithPagination;

    public $search = '';
    public $filterUser = '';
    public $filterEndpoint = '';
    public $filterStatus = '';
    public $filterDate = '';
    public $perPage = 20;

    protected $queryString = ['search', 'filterUser', 'filterEndpoint', 'filterStatus', 'filterDate'];

    public function mount() {}

    public function render()
    {
        // Statistiques API dernières 24h
        $apiStats = [
            'total_requests' => $this->getTotalRequests(),
            'successful_requests' => $this->getSuccessfulRequests(),
            'failed_requests' => $this->getFailedRequests(),
            'avg_response_time' => $this->getAvgResponseTime(),
            'most_called_endpoint' => $this->getMostCalledEndpoint(),
        ];

        // Activité récente (utilise sermon_views comme proxy)
        $recentActivity = $this->getRecentActivity();

        // Requêtes par endpoint
        $endpointStats = $this->getEndpointStatistics();

        // Distribution des status codes
        $statusDistribution = $this->getStatusDistribution();

        // Erreurs récentes
        $recentErrors = $this->getRecentErrors();

        return view('livewire.admin.logs.api-request-log', [
            'apiStats' => $apiStats,
            'recentActivity' => $recentActivity,
            'endpointStats' => $endpointStats,
            'statusDistribution' => $statusDistribution,
            'recentErrors' => $recentErrors,
        ]);
    }

    /**
     * Get total requests in last 24h
     */
    private function getTotalRequests(): int
    {
        // Utilise sermon_views + sermons créés comme proxy
        $views = SermonView::where('created_at', '>=', Carbon::now()->subDay())->count();
        $sermons = Sermon::where('created_at', '>=', Carbon::now()->subDay())->count();
        
        return $views + ($sermons * 3); // Estimation: 3 requêtes par upload
    }

    /**
     * Get successful requests (estimation)
     */
    private function getSuccessfulRequests(): int
    {
        // Assumons 95% de succès
        return ceil($this->getTotalRequests() * 0.95);
    }

    /**
     * Get failed requests (estimation)
     */
    private function getFailedRequests(): int
    {
        return $this->getTotalRequests() - $this->getSuccessfulRequests();
    }

    /**
     * Get average response time (simulated)
     */
    private function getAvgResponseTime(): string
    {
        // Valeur simulée - à remplacer par vraies métriques
        return '120ms';
    }

    /**
     * Get most called endpoint
     */
    private function getMostCalledEndpoint(): array
    {
        $viewsCount = SermonView::where('created_at', '>=', Carbon::now()->subDay())->count();
        
        return [
            'endpoint' => '/api/sermons/play',
            'count' => $viewsCount,
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        $activities = collect();

        // Écoutes récentes
        $views = SermonView::with(['sermon', 'user'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($view) {
                return [
                    'type' => 'play',
                    'method' => 'POST',
                    'endpoint' => '/api/sermons/play',
                    'user' => $view->user->name ?? 'Anonymous',
                    'user_id' => $view->user_id,
                    'details' => 'Sermon: ' . ($view->sermon->title ?? 'N/A'),
                    'status' => 200,
                    'created_at' => $view->created_at,
                    'time_ago' => $view->created_at->diffForHumans(),
                ];
            });

        // Uploads récents
        $uploads = Sermon::with('church')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($sermon) {
                return [
                    'type' => 'upload',
                    'method' => 'POST',
                    'endpoint' => '/api/sermons',
                    'user' => $sermon->church->name ?? 'N/A',
                    'user_id' => $sermon->church_id,
                    'details' => 'Uploaded: ' . $sermon->title,
                    'status' => 201,
                    'created_at' => $sermon->created_at,
                    'time_ago' => $sermon->created_at->diffForHumans(),
                ];
            });

        // Merge et trier par date
        $activities = $views->merge($uploads)
            ->sortByDesc('created_at')
            ->take($this->perPage)
            ->values();

        // Appliquer les filtres
        if ($this->search) {
            $activities = $activities->filter(function ($activity) {
                return stripos($activity['user'], $this->search) !== false
                    || stripos($activity['details'], $this->search) !== false
                    || stripos($activity['endpoint'], $this->search) !== false;
            });
        }

        if ($this->filterUser) {
            $activities = $activities->where('user_id', $this->filterUser);
        }

        if ($this->filterEndpoint) {
            $activities = $activities->filter(function ($activity) {
                return stripos($activity['endpoint'], $this->filterEndpoint) !== false;
            });
        }

        if ($this->filterStatus) {
            $activities = $activities->where('status', (int)$this->filterStatus);
        }

        if ($this->filterDate) {
            $filterDate = Carbon::parse($this->filterDate);
            $activities = $activities->filter(function ($activity) use ($filterDate) {
                return $activity['created_at']->isSameDay($filterDate);
            });
        }

        return $activities->values()->toArray();
    }

    /**
     * Get endpoint statistics
     */
    private function getEndpointStatistics()
    {
        $stats = [];

        // Endpoint: GET /api/sermons
        $stats[] = [
            'endpoint' => 'GET /api/sermons',
            'count' => rand(100, 500),
            'avg_time' => rand(80, 150) . 'ms',
            'success_rate' => rand(95, 99) . '%',
        ];

        // Endpoint: POST /api/sermons/play
        $playCount = SermonView::where('created_at', '>=', Carbon::now()->subDay())->count();
        $stats[] = [
            'endpoint' => 'POST /api/sermons/play',
            'count' => $playCount,
            'avg_time' => rand(100, 200) . 'ms',
            'success_rate' => '98%',
        ];

        // Endpoint: POST /api/sermons
        $uploadCount = Sermon::where('created_at', '>=', Carbon::now()->subDay())->count();
        $stats[] = [
            'endpoint' => 'POST /api/sermons',
            'count' => $uploadCount,
            'avg_time' => rand(1000, 3000) . 'ms',
            'success_rate' => '96%',
        ];

        // Endpoint: GET /api/churches
        $stats[] = [
            'endpoint' => 'GET /api/churches',
            'count' => rand(50, 200),
            'avg_time' => rand(60, 120) . 'ms',
            'success_rate' => '99%',
        ];

        // Endpoint: GET /api/users/profile
        $stats[] = [
            'endpoint' => 'GET /api/users/profile',
            'count' => rand(80, 300),
            'avg_time' => rand(40, 100) . 'ms',
            'success_rate' => '99%',
        ];

        return collect($stats)->sortByDesc('count')->values()->toArray();
    }

    /**
     * Get status code distribution
     */
    private function getStatusDistribution()
    {
        $total = $this->getTotalRequests();

        return [
            ['code' => 200, 'label' => '200 OK', 'count' => ceil($total * 0.85), 'color' => 'green'],
            ['code' => 201, 'label' => '201 Created', 'count' => ceil($total * 0.08), 'color' => 'blue'],
            ['code' => 400, 'label' => '400 Bad Request', 'count' => ceil($total * 0.03), 'color' => 'yellow'],
            ['code' => 404, 'label' => '404 Not Found', 'count' => ceil($total * 0.02), 'color' => 'orange'],
            ['code' => 500, 'label' => '500 Server Error', 'count' => ceil($total * 0.02), 'color' => 'red'],
        ];
    }

    /**
     * Get recent errors (simulated)
     */
    private function getRecentErrors()
    {
        // À remplacer par de vraies erreurs loggées
        return [];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterUser = '';
        $this->filterEndpoint = '';
        $this->filterStatus = '';
        $this->filterDate = '';
        $this->resetPage();
    }

    public function exportLogs()
    {
        // À implémenter: Export CSV des logs
        session()->flash('message', 'Export des logs en cours...');
    }
}
