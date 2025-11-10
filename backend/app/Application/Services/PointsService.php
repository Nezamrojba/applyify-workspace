<?php

namespace App\Application\Services;

use App\Models\Application;
use App\Models\Setting;
use App\Models\StaffPoint;
use Illuminate\Support\Carbon;

class PointsService
{
    public function creditForLetter(Application $app, string $letterType): void
    {
        $map = ['MOL' => 'mol', 'EVAL' => 'eval', 'VISA' => 'visa'];
        $key = $map[$letterType] ?? null;
        if (!$key) return;
        $config = $this->config();
        $total = (int)($config['total'] ?? 100);
        $dist = $config['distribution'] ?? ['mol' => 30, 'eval' => 30, 'visa' => 40];
        $points = (int) round($total * (($dist[$key] ?? 0) / 100));
        if ($points <= 0) return;
        if (StaffPoint::where('application_id', $app->id)->where('reason', strtoupper($key))->exists()) return;
        if (!$app->assigned_staff_id) return;
        StaffPoint::create([
            'staff_id' => $app->assigned_staff_id,
            'application_id' => $app->id,
            'points' => $points,
            'reason' => strtoupper($key),
            'credited_at' => Carbon::now(),
        ]);
    }

    public function rate(): float
    {
        $config = $this->config();
        return (float)($config['rate'] ?? 1.0);
    }

    protected function config(): array
    {
        $raw = Setting::query()->whereIn('key', [
            'points.total', 'points.distribution', 'points.rate'
        ])->pluck('value', 'key')->all();
        return [
            'total' => $raw['points.total']['value'] ?? ($raw['points.total'] ?? null),
            'distribution' => $raw['points.distribution'] ?? null,
            'rate' => $raw['points.rate']['value'] ?? ($raw['points.rate'] ?? null),
        ];
    }
}

