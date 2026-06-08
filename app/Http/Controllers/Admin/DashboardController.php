<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Admin')]

class DashboardController extends Controller
{

    #[Endpoint(title: 'Dashboard', description: 'Retrieve dashboard information.')]
    public function index()
    {
        return auth()->user();
    }
}
