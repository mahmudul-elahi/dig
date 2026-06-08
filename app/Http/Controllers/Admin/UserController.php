<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\MessageResponse;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Admin')]
class UserController extends Controller
{
    #[Endpoint(title: 'List Users', description: 'Retrieve a list of all users.')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = QueryBuilder::for(User::class)
            ->where('role', 'user')
            ->allowedFilters(
                AllowedFilter::scope('created_at', 'created_at_date'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::scope('subscription_type'),
            )
            ->paginate(min((int) $request->input('per_page', 10), 100));

        return UserResource::collection($users);
    }

    #[Endpoint(title: 'Show User', description: 'Retrieve details of a specific user.')]
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    #[Endpoint(title: 'Update User', description: "Update a user's active status.")]
    public function update(Request $request, User $user): UserResource
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return new UserResource($user->fresh());
    }

    #[Endpoint(title: 'Delete User', description: 'Delete a user from the system.')]
    public function destroy(User $user): MessageResponse
    {
        $user->delete();

        return new MessageResponse('User deleted successfully');
    }
}
