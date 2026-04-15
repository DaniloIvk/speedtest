<?php

use App\Models\Card;
use App\Models\Device;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    // ->use(RefreshDatabase::class)
      ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function get_payload(): array
{
    $PAYLOAD_COUNT = 652;

    $database = config('database.default');
    $isTransactionalDatabase = $database !== 'mongodb';

    $cards = Card::query()
                 ->limit($PAYLOAD_COUNT)
                 ->when($isTransactionalDatabase, fn ($query) => $query
                     ->inRandomOrder())
                 ->pluck('identifier')
                 ->toArray();
    $devices = Device::query()
                     ->limit($PAYLOAD_COUNT)
                     ->when($isTransactionalDatabase, fn ($query) => $query
                         ->inRandomOrder())
                     ->pluck('identifier')
                     ->toArray();

    expect(count($cards))->toBe($PAYLOAD_COUNT)->and(count($devices))->toBe($PAYLOAD_COUNT);

    $payloads = [];
    for ($i = 0; $i < $PAYLOAD_COUNT; ++$i) {
        $payloads[] = "{$cards[$i]}|{$devices[$i]}";
    }

    return $payloads;
}

function print_benchmark_report(array $metrics, string $testName, bool $showRpm = false): void
{
    if ($metrics['requestCount'] === 0) {
        return;
    }

    $csvFilename = "{$testName}_metrics.csv";
    $csv = fopen($csvFilename, 'w');
    fputcsv($csv, ['Request Number', 'Response Time (ms)']);
    foreach ($metrics['responseTimes'] as $idx => $time) {
        fputcsv($csv, [$idx + 1, round($time * 1000, 2)]);
    }
    fclose($csv);

    sort($metrics['responseTimes']);
    $count = $metrics['requestCount'];
    $min = round($metrics['responseTimes'][0] * 1000, 2);
    $max = round($metrics['responseTimes'][$count - 1] * 1000, 2);

    // FIX: Calculate average using the sum of actual latencies, not Wall Time
    $sumOfLatencies = array_sum($metrics['responseTimes']);
    $avg = round(($sumOfLatencies / $count) * 1000, 2);

    $median = round($metrics['responseTimes'][(int) ($count * 0.5)] * 1000, 2);
    $p95 = round($metrics['responseTimes'][(int) ($count * 0.95)] * 1000, 2);
    $p99 = round($metrics['responseTimes'][(int) ($count * 0.99)] * 1000, 2);

    $totalTimeMs = round($metrics['responseTimeSum'] * 1000, 2);
    $rps = round($metrics['successCount'] / $metrics['responseTimeSum'], 2);

    echo "\n";
    echo "  [ {$testName} ]\n";
    echo "  Total Sent: {$count}\n";
    echo "  Success:    {$metrics['successCount']}\n";
    echo "  Errors:     {$metrics['errorCount']}\n";
    echo "  Too Slow:   {$metrics['slowCount']}\n";
    echo "  Total Time: {$totalTimeMs} ms\n";

    if ($showRpm) {
        $rpm = round($rps * 60, 2);
        echo "  Speed:      {$rps} RPS ({$rpm} RPM)\n";
    } else {
        echo "  Speed:      {$rps} RPS\n";
    }

    echo "\n  [ Latency Distribution ]\n";
    echo "  Min: {$min} ms | Avg: {$avg} ms | Median: {$median} ms\n";
    echo "  95th: {$p95} ms | 99th: {$p99} ms | Max: {$max} ms\n\n";

    $buckets = 10;
    $binSize = ($max - $min) / $buckets;
    $binSize = $binSize ?: 1;
    $histogram = array_fill(0, $buckets, 0);

    foreach ($metrics['responseTimes'] as $t) {
        $idx = min($buckets - 1, floor(((round($t * 1000, 2)) - $min) / $binSize));
        $histogram[$idx]++;
    }

    $maxBinCount = max($histogram) ?: 1;
    for ($i = 0; $i < $buckets; $i++) {
        $rangeStart = round($min + ($i * $binSize), 2);
        $rangeEnd = round($min + (($i + 1) * $binSize), 2);

        $barLength = (int) round(($histogram[$i] / $maxBinCount) * 40);
        $bar = str_repeat('█', $barLength) . str_repeat(' ', 40 - $barLength);

        printf("  %7s - %-7s ms | %s (%d)\n", $rangeStart, $rangeEnd, $bar, $histogram[$i]);
    }
    echo "  -> Raw data exported to: {$csvFilename}\n\n";
}
