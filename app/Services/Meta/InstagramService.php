<?php

namespace App\Services\Meta;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private FacebookGraphService $graph;
    private ?string $igBusinessId;
    private ?string $accessToken;

    public function __construct(FacebookGraphService $graph)
    {
        $this->graph = $graph;
        $this->igBusinessId = MarketingSetting::get('instagram_business_id');
        $this->accessToken = MarketingSetting::get('facebook_page_access_token');
    }

    public function isEnabled(): bool
    {
        return !empty($this->igBusinessId) && !empty($this->accessToken);
    }

    public function getProfile(): ?array
    {
        if (!$this->isEnabled()) return null;

        $this->graph->setUserAccessToken($this->accessToken);

        $result = $this->graph->get($this->igBusinessId, [
            'fields' => 'id,username,name,biography,followers_count,follows_count,media_count,profile_picture_url,website',
        ]);

        return $result['id'] ?? null ? $result : null;
    }

    public function getMedia(int $limit = 25): array
    {
        if (!$this->isEnabled()) return [];

        $this->graph->setUserAccessToken($this->accessToken);

        $result = $this->graph->get("{$this->igBusinessId}/media", [
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'limit' => $limit,
        ]);

        return $result['data'] ?? [];
    }

    public function getInsights(string $period = 'day', int $days = 30): array
    {
        if (!$this->isEnabled()) return [];

        $this->graph->setUserAccessToken($this->accessToken);

        $metrics = 'impressions,reach,profile_views,follower_count';
        $since = now()->subDays($days)->timestamp;
        $until = now()->timestamp;

        $result = $this->graph->get("{$this->igBusinessId}/insights", [
            'metric' => $metrics,
            'period' => $period,
            'since' => $since,
            'until' => $until,
        ]);

        $insights = [];
        foreach ($result['data'] ?? [] as $metric) {
            $insights[$metric['name']] = [
                'name' => $metric['name'],
                'period' => $metric['period'],
                'values' => $metric['values'] ?? [],
                'total' => array_sum(array_column($metric['values'] ?? [], 'value')),
            ];
        }

        return $insights;
    }

    public function getFollowersGrowth(int $days = 30): array
    {
        $insights = $this->getInsights('day', $days);
        $followerData = $insights['follower_count']['values'] ?? [];

        $growth = [];
        $previous = null;
        foreach ($followerData as $point) {
            $current = $point['value'] ?? 0;
            $growth[] = [
                'date' => $point['end_time'] ?? '',
                'count' => $current,
                'change' => $previous !== null ? $current - $previous : 0,
            ];
            $previous = $current;
        }

        return $growth;
    }

    public function getEngagementRate(): float
    {
        $profile = $this->getProfile();
        if (!$profile) return 0;

        $followers = $profile['followers_count'] ?? 0;
        if ($followers === 0) return 0;

        $media = $this->getMedia(10);
        $totalLikes = array_sum(array_column($media, 'like_count'));
        $totalComments = array_sum(array_column($media, 'comments_count'));

        $avgEngagement = ($totalLikes + $totalComments) / max(count($media), 1);

        return round(($avgEngagement / $followers) * 100, 2);
    }

    public function getTopPosts(int $limit = 5): array
    {
        $media = $this->getMedia(25);

        $posts = array_map(function ($m) {
            return [
                'id' => $m['id'],
                'caption' => mb_substr($m['caption'] ?? '', 0, 100),
                'media_type' => $m['media_type'],
                'likes' => $m['like_count'] ?? 0,
                'comments' => $m['comments_count'] ?? 0,
                'engagement' => ($m['like_count'] ?? 0) + ($m['comments_count'] ?? 0),
                'permalink' => $m['permalink'] ?? '',
                'timestamp' => $m['timestamp'] ?? '',
            ];
        }, $media);

        usort($posts, fn($a, $b) => $b['engagement'] <=> $a['engagement']);

        return array_slice($posts, 0, $limit);
    }

    public function getDashboard(): array
    {
        $profile = $this->getProfile();
        $insights = $this->getInsights('day', 30);
        $engagement = $this->getEngagementRate();
        $topPosts = $this->getTopPosts();

        return [
            'profile' => $profile,
            'insights' => $insights,
            'engagement_rate' => $engagement,
            'top_posts' => $topPosts,
            'followers' => $profile['followers_count'] ?? 0,
            'media_count' => $profile['media_count'] ?? 0,
        ];
    }
}
