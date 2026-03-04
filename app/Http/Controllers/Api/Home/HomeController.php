<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Controller;
use App\Services\HomeAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeAggregatorService $aggregator,
    ) {}

    /**
     * GET /api/v1/home
     *
     * Aggregated home page data:
     * recent_sermons, popular_sermons, churches, preachers, categories, user_stats.
     *
     * Single request replaces 6 separate API calls.
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->aggregator->aggregate();

            return response()->json([
                'success' => true,
                'data'    => $data,
                'message' => 'Données de la page d\'accueil récupérées avec succès.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Home aggregation failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            // Graceful degradation: return whatever sections we can
            return $this->gracefulFallback($e);
        }
    }

    /**
     * If the full aggregation blows up, try each section independently
     * so the client gets partial data instead of a blank screen.
     */
    private function gracefulFallback(\Throwable $originalException): JsonResponse
    {
        $sections = [
            'recent_sermons'  => fn() => $this->aggregator->getSection('recent_sermons'),
            'popular_sermons' => fn() => $this->aggregator->getSection('popular_sermons'),
            'churches'        => fn() => $this->aggregator->getSection('churches'),
            'preachers'       => fn() => $this->aggregator->getSection('preachers'),
            'categories'      => fn() => $this->aggregator->getSection('categories'),
            'user_stats'      => fn() => $this->aggregator->getSection('user_stats'),
        ];

        $data   = [];
        $errors = [];

        foreach ($sections as $key => $fetcher) {
            try {
                $data[$key] = $fetcher();
            } catch (\Throwable $e) {
                Log::warning("Home section [$key] failed", [
                    'error' => $e->getMessage(),
                ]);
                $data[$key] = $key === 'user_stats' ? (object) [] : [];
                $errors[]   = $key;
            }
        }

        $status = empty($errors) ? 200 : 206; // 206 Partial Content

        return response()->json([
            'success'        => empty($errors),
            'data'           => $data,
            'message'        => empty($errors)
                ? 'Données de la page d\'accueil récupérées avec succès.'
                : 'Certaines sections n\'ont pas pu être chargées.',
            'failed_sections' => $errors,
        ], $status);
    }
}
