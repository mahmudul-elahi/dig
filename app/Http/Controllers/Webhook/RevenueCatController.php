<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevenueCatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = $payload['event'] ?? [];

        $type = $event['type'] ?? '';
        $appUserId = $event['app_user_id'] ?? null;

        if ($appUserId === null) {
            return response()->json(['error' => 'Missing app_user_id'], 400);
        }

        $user = User::find($appUserId);

        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $this->handleEvent($user, $event);

        return response()->json(['received' => true]);
    }

    private function handleEvent(User $user, array $event): void
    {
        $type = $event['type'] ?? '';

        $data = [
            'user_id' => $user->id,
            'revenuecat_event_type' => $type,
            'product_id' => $event['product_id'] ?? null,
            'transaction_id' => $event['transaction_id'] ?? null,
            'store' => $event['store'] ?? null,
            'period_type' => $event['period_type'] ?? null,
            'purchased_at' => $this->timestampFromMs($event['purchased_at_ms'] ?? null),
            'expiration_at' => $this->timestampFromMs($event['expiration_at_ms'] ?? null),
            'auto_resume_at' => $this->timestampFromMs($event['auto_resume_at_ms'] ?? null),
            'original_payload' => json_encode($event),
        ];

        Subscription::create($data);

        match ($type) {
            'INITIAL_PURCHASE', 'RENEWAL', 'UNCANCELLATION' => $this->activatePremium($user, $event),
            'CANCELLATION', 'EXPIRATION' => $this->expirePremium($user, $event),
            default => null,
        };
    }

    private function activatePremium(User $user, array $event): void
    {
        $expirationAt = $this->timestampFromMs($event['expiration_at_ms'] ?? null);

        $user->update([
            'is_premium' => true,
            'ends_at' => $expirationAt,
        ]);
    }

    private function expirePremium(User $user, array $event): void
    {
        $user->update([
            'is_premium' => false,
            'ends_at' => $this->timestampFromMs($event['expiration_at_ms'] ?? null),
        ]);
    }

    private function timestampFromMs(?int $milliseconds): ?string
    {
        if ($milliseconds === null) {
            return null;
        }

        return Carbon::createFromTimestamp((int) ($milliseconds / 1000), 'UTC')->toDateTimeString();
    }
}
