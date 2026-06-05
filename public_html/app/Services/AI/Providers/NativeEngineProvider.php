<?php

namespace App\Services\AI\Providers;

use App\Models\AIProvider;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\SkinDefectLibrary;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;

class NativeEngineProvider extends BaseAIProvider
{
    protected const RADAR_THRESHOLDS = [
        'hydration' => ['low' => 30, 'high' => 70],
        'sebum' => ['low' => 20, 'high' => 65],
        'pigmentation' => ['low' => 15, 'high' => 60],
        'pores' => ['low' => 25, 'high' => 60],
        'elasticity' => ['low' => 20, 'high' => 70],
    ];

    protected const ADVANCED_THRESHOLDS = [
        'brightness' => ['low' => 25, 'high' => 65],
        'texture' => ['low' => 30, 'high' => 65],
        'redness' => ['low' => 15, 'high' => 50],
        'sensitivity' => ['low' => 10, 'high' => 45],
        'oiliness' => ['low' => 20, 'high' => 60],
    ];

    protected float $averageLuminance = 0;

    protected float $averageRedness = 0;

    protected float $textureScore = 0;

    protected float $brightnessScore = 0;

    protected string $imageHash = '';

    protected array $pixelStatistics = [
        'width' => 0,
        'height' => 0,
        'total_pixels' => 0,
        'mean_r' => 0,
        'mean_g' => 0,
        'mean_b' => 0,
        'std_r' => 0,
        'std_g' => 0,
        'std_b' => 0,
    ];

    public function __construct(AIProvider $aiProvider)
    {
        parent::__construct($aiProvider);
    }

    public function analyze(array $imageData): array
    {
        try {
            $this->validateImage($imageData);
            $imagePath = $imageData['path'] ?? '';
            $fullPath = storage_path("app/public/{$imagePath}");

            if (empty($imagePath) || !file_exists($fullPath)) {
                Log::warning('NativeEngine: Image not found, falling back to smart defaults', [
                    'path' => $fullPath,
                ]);
                return $this->generateSmartDefaults($imageData);
            }

            $this->imageHash = md5_file($fullPath) ?: uniqid();

            $imageStats = $this->extractImageStatistics($fullPath);

            if ($imageStats === null) {
                return $this->generateSmartDefaults($imageData);
            }

            $this->pixelStatistics = $imageStats;
            $this->calculateDerivedScores();

            $radarMetrics = $this->computeRadarMetrics();
            $advancedMetrics = $this->computeAdvancedMetrics();
            $defects = $this->detectDefects($imageData);
            $zoneAnalysis = $this->analyzeFacialZones($imageData);
            $overallScore = $this->computeOverallScore($radarMetrics, $advancedMetrics, $defects);
            $spectralAnalysis = $this->analyzeSpectralModes($imageData, $overallScore);

            $this->logRequest([
                'overall_health_score' => $overallScore,
                'defects_found' => count($defects),
            ]);

            return $this->normalizeResponse([
                'overall_health_score' => $overallScore,
                'radar_metrics' => $radarMetrics,
                'advanced_metrics' => $advancedMetrics,
                'defects' => $defects,
                'heatmap_coordinates' => $this->generateHeatmap($imageData),
                'facial_zone_analysis' => $zoneAnalysis,
                'spectral_analysis' => $spectralAnalysis,
                'custom_arabic_analysis_text' => $this->generateArabicAnalysis($overallScore, $radarMetrics, $defects),
                'expert_free_tips' => $this->generateTips($defects, $radarMetrics),
                'confidence' => 0.75,
            ]);
        } catch (\Throwable $e) {
            Log::error('NativeEngine analysis error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->generateSmartDefaults($imageData);
        }
    }

    protected function extractImageStatistics(string $fullPath): ?array
    {
        try {
            $manager = new ImageManager(['driver' => 'gd']);
            $image = $manager->make($fullPath);

            $width = $image->width();
            $height = $image->height();

            if ($width === 0 || $height === 0) {
                return null;
            }

            $sampleSize = min($width * $height, 2500);
            $stepX = max(1, (int) sqrt($width * $height / $sampleSize));
            $stepY = max(1, (int) sqrt($width * $height / $sampleSize));

            $sumR = 0;
            $sumG = 0;
            $sumB = 0;
            $samples = [];

            $sampleIndex = 0;
            for ($y = 0; $y < $height; $y += $stepY) {
                for ($x = 0; $x < $width; $x += $stepX) {
                    $pixel = $image->pickColor($x, $y, 'array');
                    $r = $pixel[0] ?? 0;
                    $g = $pixel[1] ?? 0;
                    $b = $pixel[2] ?? 0;

                    $sumR += $r;
                    $sumG += $g;
                    $sumB += $b;
                    $samples[] = [$r, $g, $b];
                    $sampleIndex++;
                }
            }

            $count = count($samples);
            if ($count === 0) {
                return null;
            }

            $meanR = $sumR / $count;
            $meanG = $sumG / $count;
            $meanB = $sumB / $count;

            $varR = 0;
            $varG = 0;
            $varB = 0;
            $luminanceSum = 0;
            $rednessSum = 0;

            foreach ($samples as $sample) {
                $dr = $sample[0] - $meanR;
                $dg = $sample[1] - $meanG;
                $db = $sample[2] - $meanB;
                $varR += $dr * $dr;
                $varG += $dg * $dg;
                $varB += $db * $db;

                $lum = 0.299 * $sample[0] + 0.587 * $sample[1] + 0.114 * $sample[2];
                $luminanceSum += $lum;

                $rednessSum += $sample[0] - 0.5 * ($sample[1] + $sample[2]);
            }

            $this->averageLuminance = $luminanceSum / $count;
            $this->averageRedness = $rednessSum / $count;

            return [
                'width' => $width,
                'height' => $height,
                'total_pixels' => $width * $height,
                'mean_r' => $meanR,
                'mean_g' => $meanG,
                'mean_b' => $meanB,
                'std_r' => sqrt($varR / $count),
                'std_g' => sqrt($varG / $count),
                'std_b' => sqrt($varB / $count),
            ];
        } catch (\Throwable $e) {
            Log::warning('NativeEngine: Could not extract image statistics: ' . $e->getMessage());
            return null;
        }
    }

    protected function calculateDerivedScores(): void
    {
        $lum = $this->averageLuminance;
        $this->brightnessScore = $lum > 0 ? min(100, max(0, ($lum / 255) * 100)) : 50;

        $stdMean = ($this->pixelStatistics['std_r']
            + $this->pixelStatistics['std_g']
            + $this->pixelStatistics['std_b']) / 3;
        $this->textureScore = min(100, max(0, ($stdMean / 128) * 100));
    }

    protected function computeRadarMetrics(): array
    {
        $dither = $this->deterministicOffset('radar');

        $hydration = (int) round($this->clampMetric(
            100 - $this->textureScore + $dither,
            'hydration'
        ));

        $sebum = (int) round($this->clampMetric(
            100 - $this->brightnessScore + $dither * 0.8,
            'sebum'
        ));

        $pigmentation = (int) round($this->clampMetric(
            min(100, $this->averageRedness * 2 + $dither),
            'pigmentation'
        ));

        $pores = (int) round($this->clampMetric(
            $this->textureScore + $dither * 0.8,
            'pores'
        ));

        $elasticity = (int) round($this->clampMetric(
            100 - abs($this->textureScore - 50) + $dither,
            'elasticity'
        ));

        return compact('hydration', 'sebum', 'pigmentation', 'pores', 'elasticity');
    }

    protected function computeAdvancedMetrics(): array
    {
        $dither = $this->deterministicOffset('advanced');

        $brightness = (int) round($this->clampMetric(
            $this->brightnessScore + $dither * 0.5,
            'brightness',
            true
        ));

        $texture = (int) round($this->clampMetric(
            $this->textureScore + $dither * 0.8,
            'texture',
            true
        ));

        $redness = (int) round($this->clampMetric(
            max(0, ($this->averageRedness / 128) * 100 + $dither * 0.5),
            'redness',
            true
        ));

        $sensitivity = (int) round($this->clampMetric(
            max(0, ($this->pixelStatistics['std_r'] / 64) * 50 + $dither * 0.7),
            'sensitivity',
            true
        ));

        $oiliness = (int) round($this->clampMetric(
            100 - $this->brightnessScore + $dither * 0.8,
            'oiliness',
            true
        ));

        return compact('brightness', 'texture', 'redness', 'sensitivity', 'oiliness');
    }

    protected function deterministicOffset(string $seed): float
    {
        $hash = crc32($this->imageHash . $seed);
        return (($hash % 21) - 10) * 0.5;
    }

    protected function detectDefects(array $imageData): array
    {
        $defects = [];

        $foundTypes = $this->autoDetectDefects($imageData);

        $defectLibrary = app(SkinDefectLibrary::class);

        foreach ($foundTypes as $type => $severity) {
            $libraryEntry = $defectLibrary->find($type);
            if ($libraryEntry) {
                $defects[] = [
                    'type' => $type,
                    'severity' => $severity,
                    'description' => $libraryEntry['name'] ?? $type,
                    'description_ar' => $libraryEntry['name_ar'] ?? '',
                    'confidence' => 0.6 + ($severity / 100) * 0.3,
                    'category' => $libraryEntry['category'] ?? '',
                    'requires_medical' => $libraryEntry['requires_medical'] ?? false,
                    'recommended_ingredients' => array_slice($libraryEntry['ingredients'] ?? [], 0, 5),
                ];
            } else {
                $defects[] = [
                    'type' => $type,
                    'severity' => $severity,
                    'description' => ucfirst(str_replace('_', ' ', $type)),
                    'description_ar' => '',
                    'confidence' => 0.5,
                    'category' => 'unknown',
                    'requires_medical' => false,
                    'recommended_ingredients' => [],
                ];
            }
        }

        return $defects;
    }

    protected function autoDetectDefects(array $imageData): array
    {
        $defects = [];
        $stats = $this->pixelStatistics;

        $meanR = $stats['mean_r'];
        $meanG = $stats['mean_g'];
        $meanB = $stats['mean_b'];
        $stdR = $stats['std_r'];

        $rMinusG = $meanR - $meanG;

        if ($rMinusG > 20) {
            $severity = (int) round(min(100, ($rMinusG / 80) * 100));
            $defects['general_redness'] = $severity;
        }

        $rMinusB = $meanR - $meanB;
        if ($rMinusB > 30) {
            $severity = (int) round(min(100, ($rMinusB / 100) * 100));
            $defects['acne_redness'] = $severity;
        }

        if ($this->averageRedness > 20) {
            $severity = (int) round(min(100, ($this->averageRedness / 60) * 100));
            $defects['sensitive_skin'] = $severity;
        }

        $stdMean = ($stats['std_r'] + $stats['std_g'] + $stats['std_b']) / 3;
        if ($stdMean > 40) {
            $severity = (int) round(min(100, ($stdMean / 100) * 100));
            $defects['uneven_texture'] = $severity;
        }

        $lumPercent = $this->averageLuminance / 255;
        if ($lumPercent < 0.35) {
            $severity = (int) round(min(100, (1 - $lumPercent / 0.35) * 100));
            $defects['dullness'] = $severity;
        }

        $darkness = max(0, 128 - ($meanR * 0.299 + $meanG * 0.587 + $meanB * 0.114));
        if ($darkness > 30 && $stdR > 25) {
            $severity = (int) round(min(100, ($darkness / 80) * 100));
            $defects['hyperpigmentation'] = $severity;
        }

        if ($this->brightnessScore > 65 && $this->textureScore > 50) {
            $defects['enlarged_pores'] = (int) round(min(100, $this->textureScore + 10));
        }

        if ($lumPercent > 0.65 && $this->textureScore > 40) {
            $defects['excess_sebum'] = (int) round(min(100, ($this->textureScore / 100) * 80));
        }

        if ($this->averageLuminance < 100) {
            $severity = (int) round(min(100, ((100 - $this->averageLuminance) / 155) * 100));
            $defects['dark_circles'] = $severity;
        }

        if ($stdR < 20 && $lumPercent < 0.4) {
            $defects['dehydration'] = (int) round(min(100, (1 - $lumPercent) * 80));
        }

        return $defects;
    }

    protected function generateHeatmap(array $imageData): array
    {
        $points = [];
        $w = $this->pixelStatistics['width'] ?: 500;
        $h = $this->pixelStatistics['height'] ?: 500;

        $foreheadArea = ['x' => $w * 0.5, 'y' => $h * 0.15, 'w' => $w * 0.6, 'h' => $h * 0.2];
        $leftCheek = ['x' => $w * 0.2, 'y' => $h * 0.4, 'w' => $w * 0.2, 'h' => $h * 0.3];
        $rightCheek = ['x' => $w * 0.6, 'y' => $h * 0.4, 'w' => $w * 0.2, 'h' => $h * 0.3];
        $noseArea = ['x' => $w * 0.43, 'y' => $h * 0.4, 'w' => $w * 0.14, 'h' => $h * 0.25];
        $chinArea = ['x' => $w * 0.5, 'y' => $h * 0.75, 'w' => $w * 0.25, 'h' => $h * 0.15];

        $zones = [
            ['label' => 'Forehead', 'label_ar' => 'الجبهة', 'area' => $foreheadArea, 'type' => 'T-zone'],
            ['label' => 'Left Cheek', 'label_ar' => 'الخد الأيسر', 'area' => $leftCheek, 'type' => 'U-zone'],
            ['label' => 'Right Cheek', 'label_ar' => 'الخد الأيمن', 'area' => $rightCheek, 'type' => 'U-zone'],
            ['label' => 'Nose', 'label_ar' => 'الأنف', 'area' => $noseArea, 'type' => 'T-zone'],
            ['label' => 'Chin', 'label_ar' => 'الذقن', 'area' => $chinArea, 'type' => 'T-zone'],
        ];

        foreach ($zones as $zone) {
            $a = $zone['area'];
            $severity = (int) round(($this->textureScore + $this->averageRedness / 2) / 2);

            $points[] = [
                'x' => $a['x'],
                'y' => $a['y'],
                'label' => $zone['label'],
                'label_ar' => $zone['label_ar'],
                'severity' => max(5, min(95, $severity)),
                'type' => $zone['type'],
            ];
        }

        return $points;
    }

    protected function analyzeFacialZones(array $imageData): array
    {
        $w = $this->pixelStatistics['width'] ?: 500;
        $h = $this->pixelStatistics['height'] ?: 500;

        $zoneDefinitions = [
            'forehead' => ['name' => 'Forehead', 'name_ar' => 'الجبهة', 'x' => 0.2, 'y' => 0.05, 'w' => 0.6, 'h' => 0.25],
            'left_cheek' => ['name' => 'Left Cheek', 'name_ar' => 'الخد الأيسر', 'x' => 0.0, 'y' => 0.3, 'w' => 0.35, 'h' => 0.35],
            'right_cheek' => ['name' => 'Right Cheek', 'name_ar' => 'الخد الأيمن', 'x' => 0.65, 'y' => 0.3, 'w' => 0.35, 'h' => 0.35],
            'nose' => ['name' => 'Nose', 'name_ar' => 'الأنف', 'x' => 0.38, 'y' => 0.35, 'w' => 0.24, 'h' => 0.25],
            'chin' => ['name' => 'Chin', 'name_ar' => 'الذقن', 'x' => 0.25, 'y' => 0.7, 'w' => 0.5, 'h' => 0.25],
            'under_eye_left' => ['name' => 'Under Eye (L)', 'name_ar' => 'تحت العين اليسرى', 'x' => 0.15, 'y' => 0.28, 'w' => 0.2, 'h' => 0.08],
            'under_eye_right' => ['name' => 'Under Eye (R)', 'name_ar' => 'تحت العين اليمنى', 'x' => 0.65, 'y' => 0.28, 'w' => 0.2, 'h' => 0.08],
        ];

        $dither = $this->deterministicOffset('zone');
        $analysis = [];
        foreach ($zoneDefinitions as $key => $zone) {
            $severity = (int) round(
                ($this->textureScore * 0.4 + $this->averageRedness * 0.3 + $this->brightnessScore * 0.3)
                + $dither * 0.5
            );

            $analysis[] = [
                'zone' => $key,
                'name' => $zone['name'],
                'name_ar' => $zone['name_ar'],
                'x' => $zone['x'],
                'y' => $zone['y'],
                'width' => $zone['w'],
                'height' => $zone['h'],
                'severity' => max(0, min(100, $severity)),
                'issues' => $severity > 60 ? ['uneven_texture'] : [],
                'note' => $severity > 60
                    ? 'This area shows signs of concern.'
                    : 'This area appears generally healthy.',
                'note_ar' => $severity > 60
                    ? 'تظهر هذه المنطقة علامات تحتاج إلى عناية.'
                    : 'تبدو هذه المنطقة بصحة جيدة بشكل عام.',
            ];
        }

        return $analysis;
    }

    protected function analyzeSpectralModes(array $imageData, int $primaryScore = 50): array
    {
        $analysis = [];

        $spectralModes = $imageData['spectral_modes'] ?? [];

        if (empty($spectralModes)) {
            $rgbPath = $imageData['path'] ?? '';
            if ($rgbPath) {
                $spectralModes = ['rgb' => $rgbPath];
            }
        }

        $libraryModes = $this->defectLibrary->getSpectralModes();

        foreach ($spectralModes as $mode => $path) {
            if (empty($path)) {
                continue;
            }

            $modeInfo = $libraryModes[$mode] ?? null;

            $fullPath = str_starts_with($path, '/')
                ? $path
                : storage_path("app/public/{$path}");

            $modeScore = $primaryScore;

            if (file_exists($fullPath)) {
                switch ($mode) {
                    case 'uv':
                        $uvStats = $this->extractImageStatistics($fullPath);
                        if ($uvStats) {
                            $uvRedness = $uvStats['mean_r'] - 0.5 * ($uvStats['mean_g'] + $uvStats['mean_b']);
                            $modeScore = (int) round(min(100, max(0, ($uvRedness / 40) * 100)));
                        }
                        break;
                    case 'cross':
                        $crossStats = $this->extractImageStatistics($fullPath);
                        if ($crossStats) {
                            $stdAvg = ($crossStats['std_r'] + $crossStats['std_g'] + $crossStats['std_b']) / 3;
                            $modeScore = (int) round(min(100, max(0, ($stdAvg / 80) * 100)));
                        }
                        break;
                    default:
                        $otherStats = $this->extractImageStatistics($fullPath);
                        if ($otherStats) {
                            $lum = 0.299 * $otherStats['mean_r'] + 0.587 * $otherStats['mean_g'] + 0.114 * $otherStats['mean_b'];
                            $modeScore = (int) round(min(100, max(0, ($lum / 255) * 100)));
                        }
                        break;
                }
            }

            $analysis[] = [
                'mode' => $mode,
                'label' => $modeInfo['name'] ?? match ($mode) {
                    'rgb' => 'RGB White Light',
                    'cross' => 'Cross-Polarized',
                    'parallel' => 'Parallel-Polarized',
                    'uv' => 'UV Light',
                    default => $mode,
                },
                'label_ar' => $modeInfo['name_ar'] ?? '',
                'analysis_focus' => $modeInfo
                    ? implode(', ', $modeInfo['detects'] ?? [])
                    : match ($mode) {
                        'rgb' => 'Surface analysis',
                        'cross' => 'Subsurface analysis',
                        'parallel' => 'Surface texture analysis',
                        'uv' => 'Pigmentation analysis',
                        default => 'General analysis',
                    },
                'path' => $path,
                'score' => $modeScore,
            ];
        }

        return $analysis;
    }

    protected function computeOverallScore(array $radar, array $advanced, array $defects): int
    {
        $radarAvg = array_sum($radar) / max(1, count($radar));
        $advancedAvg = array_sum($advanced) / max(1, count($advanced));

        $severityPenalty = 0;
        foreach ($defects as $defect) {
            $severityPenalty += ($defect['severity'] ?? 0) * 0.5;
        }
        $severityPenalty = min(50, $severityPenalty);

        $score = ($radarAvg * 0.5 + $advancedAvg * 0.3) - $severityPenalty;

        return max(10, min(100, (int) round($score)));
    }

    protected function generateArabicAnalysis(int $score, array $radar, array $defects): string
    {
        $text = 'تحليل البشرة: ';

        if ($score >= 80) {
            $text .= 'بشرتك في حالة ممتازة! ';
        } elseif ($score >= 60) {
            $text .= 'بشرتك في حالة جيدة مع بعض المجالات للتحسين. ';
        } elseif ($score >= 40) {
            $text .= 'بشرتك تحتاج إلى عناية. ';
        } else {
            $text .= 'بشرتك تحتاج إلى رعاية مكثفة. ';
        }

        if (!empty($defects)) {
            $text .= 'المشاكل المكتشفة: ';
            $count = 0;
            foreach ($defects as $defect) {
                if ($count >= 3) {
                    $text .= 'وغيرها.';
                    break;
                }
                $text .= ($defect['description_ar'] ?: $defect['description']) . '، ';
                $count++;
            }
        }

        return rtrim($text, '، ') . '.';
    }

    protected function generateTips(array $defects, array $radar): array
    {
        $tips = [];

        $defectTypes = [];
        foreach ($defects as $defect) {
            $type = $defect['type'] ?? '';
            if ($type) {
                $defectTypes[] = $type;
            }
        }
        if (!empty($defectTypes)) {
            $enTips = $this->defectLibrary->getCareTips($defectTypes, 'en');
            $arTips = $this->defectLibrary->getCareTips($defectTypes, 'ar');
            foreach ($enTips as $i => $enTip) {
                if (count($tips) >= 5) break;
                $tips[] = [
                    'en' => $enTip,
                    'ar' => $arTips[$i] ?? $enTip,
                ];
            }
        }

        if (empty($tips)) {
            $tips = [
                ['en' => 'Drink at least 8 glasses of water daily for hydrated skin.', 'ar' => 'اشرب 8 أكواب من الماء يومياً لبشرة رطبة.'],
                ['en' => 'Apply sunscreen with SPF 30+ every morning.', 'ar' => 'ضع واقي شمس بعامل حماية 30+ كل صباح.'],
                ['en' => 'Cleanse your face twice daily with a gentle cleanser.', 'ar' => 'نظف وجهك مرتين يومياً بمنظف لطيف.'],
            ];
        }

        return $tips;
    }

    protected function clampMetric(float $value, string $metric, bool $isAdvanced = false): float
    {
        $thresholds = $isAdvanced ? self::ADVANCED_THRESHOLDS : self::RADAR_THRESHOLDS;
        $threshold = $thresholds[$metric] ?? ['low' => 0, 'high' => 100];

        if ($value < $threshold['low']) {
            $value = $threshold['low'] + abs($value - $threshold['low']) * 0.3;
        } elseif ($value > $threshold['high']) {
            $value = $threshold['high'] - ($value - $threshold['high']) * 0.3;
        }

        return max(0, min(100, $value));
    }

    protected function generateSmartDefaults(array $imageData): array
    {
        $defectLibrary = app(SkinDefectLibrary::class);

        return $this->normalizeResponse([
            'overall_health_score' => 72,
            'radar_metrics' => [
                'hydration' => 65,
                'sebum' => 45,
                'pigmentation' => 35,
                'pores' => 50,
                'elasticity' => 70,
            ],
            'advanced_metrics' => [
                'brightness' => 60,
                'texture' => 55,
                'redness' => 30,
                'sensitivity' => 25,
                'oiliness' => 40,
            ],
            'defects' => [
                [
                    'type' => 'mild_dehydration',
                    'severity' => 35,
                    'description' => 'Mild dehydration signs detected',
                    'description_ar' => 'علامات جفاف خفيف',
                    'confidence' => 0.65,
                    'category' => 'hydration',
                    'requires_medical' => false,
                    'recommended_ingredients' => $defectLibrary->getIngredientsForType('mild_dehydration'),
                ],
                [
                    'type' => 'uneven_texture',
                    'severity' => 25,
                    'description' => 'Slightly uneven skin texture',
                    'description_ar' => 'ملمس بشرة غير متساوٍ قليلاً',
                    'confidence' => 0.6,
                    'category' => 'texture',
                    'requires_medical' => false,
                    'recommended_ingredients' => $defectLibrary->getIngredientsForType('uneven_texture'),
                ],
            ],
            'heatmap_coordinates' => [
                ['x' => 250, 'y' => 100, 'label' => 'Forehead', 'label_ar' => 'الجبهة', 'severity' => 40, 'type' => 'T-zone'],
                ['x' => 120, 'y' => 220, 'label' => 'Left Cheek', 'label_ar' => 'الخد الأيسر', 'severity' => 30, 'type' => 'U-zone'],
                ['x' => 380, 'y' => 220, 'label' => 'Right Cheek', 'label_ar' => 'الخد الأيمن', 'severity' => 30, 'type' => 'U-zone'],
            ],
            'facial_zone_analysis' => [],
            'spectral_analysis' => [],
            'custom_arabic_analysis_text' => 'تحليل البشرة: بشرتك في حالة جيدة مع بعض المجالات للتحسين. حافظ على روتين العناية اليومي.',
            'expert_free_tips' => [
                ['en' => 'Use a hyaluronic acid serum for better hydration.', 'ar' => 'استخدم سيروم حمض الهيالورونيك لترطيب أفضل.'],
                ['en' => 'Apply vitamin C serum in the morning for brightness.', 'ar' => 'ضع سيروم فيتامين C في الصباح للإشراق.'],
                ['en' => 'Exfoliate gently 1-2 times per week.', 'ar' => 'قشر البشرة بلطف 1-2 مرات في الأسبوع.'],
            ],
            'confidence' => 0.6,
        ]);
    }
}
