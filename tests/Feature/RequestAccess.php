<?php

use App\Models\Card;
use App\Models\Device;
use Illuminate\Http\Client\Pool;

test('that api/access-requests can handle sufficient number of request per second', function () {
    $PAYLOAD_COUNT = 170;

    $cards = Card::query()
                 ->inRandomOrder()
                 ->limit($PAYLOAD_COUNT)
                 ->toBase()
                 ->pluck('identifier')
                 ->toArray();
    $devices = Device::query()
                     ->inRandomOrder()
                     ->limit($PAYLOAD_COUNT)
                     ->toBase()
                     ->pluck('identifier')
                     ->toArray();

    (expect(count($cards))->toBeGreaterThanOrEqual($PAYLOAD_COUNT))
        ->and(count($devices))->toBeGreaterThanOrEqual($PAYLOAD_COUNT);

    $payloads = [];

    for ($i = 0; $i < $PAYLOAD_COUNT; ++$i) {
        $payloads[] = "{$cards[$i]}|{$devices[$i]}";
    }

    $startTime = microtime(true);

    $responses = Http::pool(function (Pool $pool) use ($PAYLOAD_COUNT, $payloads) {
        $requests = [];

        for ($i = 0; $i < $PAYLOAD_COUNT; ++$i) {
            $requests[] = $pool
                ->withBody($payloads[$i], 'text/plain')
                ->post('http://127.0.0.1:8000/api/request-access');
        }

        return $requests;
    }, concurrency: 1);

    $endTime = microtime(true);

    $successCount = 0;
    foreach ($responses as $response) {
        if (in_array($response->body(), ['true', 'false'])) {
            $successCount++;
        }
    }

    (expect($successCount)->toBe($PAYLOAD_COUNT))
        ->and($endTime - $startTime)->toBeLessThan(1);
});
