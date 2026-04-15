<?php

namespace App\Http\Controllers;

use App\Enums\Activity\Event;
use App\Models\Card;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class AccessController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = explode('|', $request->getContent() ?: '') + [null, null];

        $requestIsValid = (
            isset($payload, $payload[0], $payload[1]) &&
            ($card = $this->resolveCard($payload[0])) &&
            ($device = $this->resolveDevice($payload[1]))
        );

        $accessGranted = (
            $requestIsValid &&
            isset($card, $device) &&
            $card->canAccess($device)
        );

        if ($requestIsValid && isset($card, $device)) {
            $this->logAccessAttempt($card, $device, $accessGranted);
        } else {
            $this->logInvalidRequest(null, null);
        }

        return response()->json(
            data: $accessGranted,
            status: $accessGranted
                ? BaseResponse::HTTP_OK
                : BaseResponse::HTTP_FORBIDDEN,
        );
    }

    private function resolveDevice(string $identifier): Device|null
    {
        return Device::query()
                     ->with(['area'])
                     ->whereIdentifier($identifier)
                     ->first();
    }

    private function resolveCard(string $identifier): Card|null
    {
        return Card::query()
                   ->whereIdentifier($identifier)
                   ->first();
    }

    private function logInvalidRequest(
        Card|null $card,
        Device|null $device
    ): void
    {
        $activity = activity()
            ->causedBy($card)
            ->event(Event::INVALID_ACCESS->value);

        if (isset($device)) {
            $activity->performedOn($device);
        }

        $activity->log('access_required');
    }

    private function logAccessAttempt(
        Card $card,
        Device $device,
        bool $accessGranted
    ): void
    {
        activity()
            ->causedBy($card)
            ->performedOn($device)
            ->event(Event::ACCESS->value)
            ->withProperties([
                'access_granted' => $accessGranted,
            ])
            ->log('access_required');
    }
}
