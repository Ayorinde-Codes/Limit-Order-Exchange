<?php

namespace App\Http\Controllers\V1\Web;

use App\Actions\Order\GetOrdersAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GetOrdersAction $getOrdersAction): Response
    {
        $user = Auth::user()->load('assets');

        $orders = $getOrdersAction->execute($request->get('symbol', 'BTC'));

        $profile = [
            'balance' => (float) $user->balance,
            'assets' => $user->assets->map(fn($asset) => [
                'symbol' => $asset->symbol->value,
                'amount' => (float) $asset->amount,
                'locked_amount' => (float) $asset->locked_amount,
                'available' => (float) $asset->available_amount,
            ])->values()->toArray(),
        ];

        return Inertia::render('Dashboard', [
            'profile' => $profile,
            'orders' => $orders,
            'symbol' => $request->get('symbol', 'BTC'),
        ]);
    }
}
