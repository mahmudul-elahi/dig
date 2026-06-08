<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Models\QuoteLike;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

#[Group('Admins/Users Quotes')]
class QuoteLikeController extends Controller
{
    #[Endpoint(title: 'Like Quote', description: 'Like a quote as the authenticated user.')]
    public function store(Request $request, Quote $quote): JsonResponse
    {
        $created = $this->likeQuote($request->user(), $quote);
        $quote = $quote->fresh();
        $quote->setAttribute('is_liked', true);

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode($created ? JsonResponse::HTTP_CREATED : JsonResponse::HTTP_OK);
    }

    #[Endpoint(title: 'Unlike Quote', description: 'Remove the authenticated user like from a quote.')]
    public function destroy(Request $request, Quote $quote): JsonResponse
    {
        $this->unlikeQuote($request->user(), $quote);
        $quote = $quote->fresh();
        $quote->setAttribute('is_liked', false);

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_OK);
    }

    private function likeQuote(?User $user, Quote $quote): bool
    {
        if ($user === null) {
            abort(401);
        }

        return DB::transaction(function () use ($quote, $user): bool {
            $lockedQuote = Quote::query()
                ->whereKey($quote->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existingLike = QuoteLike::query()
                ->where('user_id', $user->getKey())
                ->where('quote_id', $lockedQuote->getKey())
                ->first();

            if ($existingLike !== null) {
                return false;
            }

            QuoteLike::create([
                'user_id' => $user->getKey(),
                'quote_id' => $lockedQuote->getKey(),
            ]);

            $lockedQuote->increment('reactions_count');

            return true;
        });
    }

    private function unlikeQuote(?User $user, Quote $quote): bool
    {
        if ($user === null) {
            abort(401);
        }

        return DB::transaction(function () use ($quote, $user): bool {
            $lockedQuote = Quote::query()
                ->whereKey($quote->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existingLike = QuoteLike::query()
                ->where('user_id', $user->getKey())
                ->where('quote_id', $lockedQuote->getKey())
                ->first();

            if ($existingLike === null) {
                return false;
            }

            $existingLike->delete();

            $lockedQuote->update([
                'reactions_count' => max(0, $lockedQuote->reactions_count - 1),
            ]);

            return true;
        });
    }
}
