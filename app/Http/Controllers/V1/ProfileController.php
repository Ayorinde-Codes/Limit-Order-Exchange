<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = Auth::user()->load(['assets']); // prevents N+1

        return $this->okResponse(
            'Profile retrieved successfully',
            new ProfileResource($user)
        );
    }
}
