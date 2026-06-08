<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuoteRequest;
use App\Http\Requests\Admin\UpdateQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Http\Responses\MessageResponse;
use App\Models\Quote;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Admins/Users Quotes')]
class QuoteController extends Controller
{
    #[Endpoint(title: 'List Quotes', description: 'Retrieve quotes created by authenticated users.')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = QueryBuilder::for(Quote::class)
            ->allowedFilters(
                AllowedFilter::exact('status'),
            )
            ->paginate(min((int) $request->input('per_page', 10), 100));

        return QuoteResource::collection($quotes);
    }

    #[Endpoint(title: 'Create Quote', description: 'Create a quote owned by the authenticated user.')]
    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $quote = $request->user()->quotes()->create($request->validated());

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode(201);
    }

    #[Endpoint(title: 'Show Quote', description: 'Retrieve a single quote.')]
    public function show(Quote $quote): QuoteResource
    {
        return new QuoteResource($quote);
    }

    #[Endpoint(title: 'Update Quote', description: 'Update a single quote.')]
    public function update(UpdateQuoteRequest $request, Quote $quote): QuoteResource
    {
        $this->authorizeQuote($request, $quote);

        $quote->update($request->validated());

        return new QuoteResource($quote->fresh());
    }

    #[Endpoint(title: 'Delete Quote', description: 'Delete a quote.')]
    public function destroy(Request $request, Quote $quote): MessageResponse
    {
        $this->authorizeQuote($request, $quote);

        $quote->delete();

        return new MessageResponse('Quote deleted successfully');
    }

    private function authorizeQuote(Request $request, Quote $quote): void
    {
        $user = $request->user();

        if ($user?->isAdmin() || $user?->is($quote->user)) {
            return;
        }

        abort(403);
    }
}
