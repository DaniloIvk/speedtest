<?php

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

test('that api/request-access can handle sufficient number of requests per second (sequential)', function () {
    $payloads = get_payload();
    $payloadCount = count($payloads);

    $metrics = ['requestCount' => 0, 'successCount' => 0, 'errorCount' => 0, 'slowCount' => 0, 'responseTimeSum' => 0.0, 'responseTimes' => []];

    for ($i = 0; $i < $payloadCount && $metrics['responseTimeSum'] < 1.0; ++$i) {
        $startTime = hrtime(true);
        $response = Http::post('http://localhost:8000/api/request-access', [$payloads[$i]]);
        $totalTime = (hrtime(true) - $startTime) / 1e9;

        $metrics['responseTimeSum'] += $totalTime;
        $metrics['responseTimes'][] = $totalTime;

        $statusCode = $response->status();
        $content = $response->body();

        $isValid = (
            ($statusCode === 200 && $content === 'true') ||
            ($statusCode === 403 && $content === 'false')
        );

        if (! $isValid) {
            $metrics['errorCount']++;
        } elseif ($totalTime >= 0.2) {
            $metrics['slowCount']++;
        } else {
            $metrics['successCount']++;
        }

        $metrics['requestCount']++;
    }

    print_benchmark_report($metrics, 'Requests_Per_Second_Sequential');

    $requestsPerSecond = round($metrics['successCount'] / $metrics['responseTimeSum'], 2);
    expect($requestsPerSecond)
        ->toBeGreaterThanOrEqual(64.0, 'count of requests from input interface')
        ->toBeGreaterThanOrEqual(128.0, 'double count of requests from input interface')
        ->toBeGreaterThanOrEqual(196.0, 'minimum count of requests to be processed from input interface')
        ->toBeGreaterThanOrEqual(256.0, 'optimal count of requests to be processed from input interface');
})->skip(fn () => true);

test('that api/request-access can handle sufficient number of requests per second (concurrent)', function () {
    $payloads = get_payload();
    $payloadCount = count($payloads);

    $metrics = ['requestCount' => 0, 'successCount' => 0, 'errorCount' => 0, 'slowCount' => 0, 'responseTimeSum' => 0.0, 'responseTimes' => []];

    for ($i = 0; $i < $payloadCount && $metrics['responseTimeSum'] < 1.0; ++$i) {
        $batchRequestCount = 10;

        $startTime = hrtime(true);
        $responses = Http::pool(function (Pool $pool) use ($batchRequestCount, $payloads, $metrics) {
            $requests = [];
            for ($j = 0; $j < $batchRequestCount; ++$j) {
                $requests[] = $pool
                    ->withBody($payloads[$j + $metrics['requestCount']], 'text/plain')
                    ->post('http://127.0.0.1:8000/api/request-access');
            }
            return $requests;
        }, $batchRequestCount);

        $totalTime = (hrtime(true) - $startTime) / 1e9;
        $metrics['responseTimeSum'] += $totalTime;

        foreach ($responses as $response) {
            $metrics['responseTimes'][] = $totalTime;
            $statusCode = $response->status();
            $content = $response->body();

            $isValid = (
                ($statusCode === 200 && $content === 'true') ||
                ($statusCode === 403 && $content === 'false')
            );

            if (! $isValid) {
                $metrics['errorCount']++;
            } elseif ($totalTime >= 0.2) {
                $metrics['slowCount']++;
            } else {
                $metrics['successCount']++;
            }

            $metrics['requestCount']++;
        }
    }

    print_benchmark_report($metrics, 'Requests_Per_Second_Concurrent');

    $requestsPerSecond = round($metrics['successCount'] / $metrics['responseTimeSum'], 2);
    expect($requestsPerSecond)
        ->toBeGreaterThanOrEqual(64.0, 'count of requests from input interface')
        ->toBeGreaterThanOrEqual(128.0, 'double count of requests from input interface')
        ->toBeGreaterThanOrEqual(196.0, 'minimum count of requests to be processed from input interface')
        ->toBeGreaterThanOrEqual(256.0, 'optimal count of requests to be processed from input interface');
})->skip(fn () => true);
