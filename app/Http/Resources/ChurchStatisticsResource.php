<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChurchStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'church_info' => [
                'id' => $this->resource['church_info']['id'],
                'name' => $this->resource['church_info']['name'],
                'created_at' => $this->resource['church_info']['created_at'],
                'days_active' => now()->diffInDays($this->resource['church_info']['created_at']),
            ],

            'overview' => [
                'total_sermons' => $this->resource['sermon_stats']['total_sermons'],
                'published_sermons' => $this->resource['sermon_stats']['published_sermons'],
                'draft_sermons' => $this->resource['sermon_stats']['draft_sermons'],
                'total_views' => $this->resource['listening_stats']['total_listens'],
                'unique_listeners' => $this->resource['listening_stats']['unique_listeners'],
                'total_favorites' => $this->resource['user_engagement']['total_favorites'],
                'avg_views_per_sermon' => $this->resource['listening_stats']['avg_listens_per_sermon'],
            ],

            'charts_data' => [
                'monthly_sermons' => $this->formatChartData(
                    $this->resource['sermon_stats']['monthly_breakdown'],
                    'count',
                    'Sermons publiés'
                ),
                'monthly_views' => $this->formatChartData(
                    $this->resource['listening_stats']['monthly_listens'],
                    'count',
                    'Écoutes'
                ),
                'yearly_analysis' => $this->formatYearlyChart(
                    $this->resource['publication_analysis']['yearly_analysis']
                ),
                'monthly_comparison' => $this->formatMonthlyComparison(
                    $this->resource['publication_analysis']['monthly_analysis']
                ),
                'weekday_distribution' => $this->formatWeekdayChart(
                    $this->resource['publication_analysis']['weekday_analysis']
                ),
            ],

            'engagement' => [
                'top_sermons' => $this->formatTopSermons(
                    $this->resource['top_sermons']
                ),
                'top_listeners' => $this->resource['user_engagement']['top_listeners'],
                'engagement_rate' => $this->calculateEngagementRate(),
            ],

            'recent_activity' => [
                'recent_sermons' => $this->resource['recent_activity']['recent_sermons'],
                'recent_views' => $this->resource['recent_activity']['recent_listens'],
            ],

            'disk_usage' => $this->resource['disk_usage'] ?? [
                'quota' => [
                    'total_bytes' => 3221225472,
                    'total_gb' => 3.0,
                ],
                'used' => [
                    'bytes' => 0,
                    'mb' => 0,
                    'gb' => 0,
                    'percentage' => 0,
                ],
                'remaining' => [
                    'bytes' => 3221225472,
                    'mb' => 3072,
                    'gb' => 3.0,
                    'percentage' => 100,
                ],
                'sermons' => [
                    'total' => 0,
                    'published' => 0,
                    'avg_size_mb' => 0,
                ],
                'status' => 'normal',
                'messages' => [
                    'normal' => 'Espace de stockage disponible',
                    'warning' => 'Attention: vous approchez de la limite de stockage',
                    'critical' => 'Critique: espace de stockage presque épuisé',
                ],
                'current_message' => 'Espace de stockage disponible',
            ],
        ];
    }

    /**
     * Format data for Flutter charts
     */
    private function formatChartData($data, $valueKey, $label)
    {
        return [
            'label' => $label,
            'data' => collect($data)->map(function ($item) use ($valueKey) {
                return [
                    'x' => $item['month_name'] ?? $item['year'] ?? $item['day_name'],
                    'y' => $item[$valueKey],
                    'period' => $item['month_name'] ?? $item['year'] ?? $item['day_name'],
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Format yearly analysis for charts
     */
    private function formatYearlyChart($yearlyData)
    {
        return [
            'label' => 'Évolution annuelle',
            'data' => collect($yearlyData)->map(function ($item) {
                return [
                    'x' => $item['year'],
                    'y' => $item['sermon_count'],
                    'period' => $item['year'],
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Format monthly comparison (sermons vs views)
     */
    private function formatMonthlyComparison($monthlyData)
    {
        return [
            'sermons' => [
                'label' => 'Sermons publiés',
                'data' => collect($monthlyData)->map(function ($item) {
                    return [
                        'x' => $item['month_name'],
                        'y' => $item['sermon_count'],
                        'period' => $item['month_name'],
                    ];
                })->values()->toArray(),
            ],
            'views' => [
                'label' => 'Écoutes',
                'data' => collect($monthlyData)->map(function ($item) {
                    return [
                        'x' => $item['month_name'],
                        'y' => $item['listen_count'],
                        'period' => $item['month_name'],
                    ];
                })->values()->toArray(),
            ],
        ];
    }

    /**
     * Format weekday distribution
     */
    private function formatWeekdayChart($weekdayData)
    {
        return [
            'label' => 'Publications par jour de la semaine',
            'data' => collect($weekdayData)->map(function ($item) {
                return [
                    'x' => $item['day_name'],
                    'y' => $item['count'],
                    'period' => $item['day_name'],
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Format top sermons with additional metrics
     */
    private function formatTopSermons($topSermons)
    {
        return collect($topSermons)->map(function ($sermon) {
            return [
                'id' => $sermon['id'],
                'title' => $sermon['title'],
                'views_count' => $sermon['views_count'],
                'favorites_count' => $sermon['favorites_count'],
                'engagement_score' => $sermon['views_count'] + ($sermon['favorites_count'] * 3), // Weighted score
                'created_at' => $sermon['created_at'],
                'days_since_published' => now()->diffInDays($sermon['created_at']),
            ];
        })->values()->toArray();
    }

    /**
     * Calculate overall engagement rate
     */
    private function calculateEngagementRate()
    {
        $totalViews = $this->resource['listening_stats']['total_listens'];
        $totalFavorites = $this->resource['user_engagement']['total_favorites'];
        $uniqueListeners = $this->resource['listening_stats']['unique_listeners'];

        if ($uniqueListeners == 0) {
            return 0;
        }

        // Engagement rate = (favorites / unique_listeners) * 100
        return round(($totalFavorites / $uniqueListeners) * 100, 2);
    }
}
