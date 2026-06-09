<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Admin Dashboard')]
class DashboardController extends Controller
{
    #[Endpoint(title: 'Dashboard', description: 'Retrieve dashboard information.')]
    public function index(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
