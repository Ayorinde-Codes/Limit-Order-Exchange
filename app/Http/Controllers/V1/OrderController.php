<?php

namespace App\Http\Controllers\V1;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\GetOrderHistoryAction;
use App\Actions\Order\GetOrdersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request, GetOrdersAction $action): JsonResponse
    {
        try {
            $result = $action->execute($request->symbol);

            return $this->okResponse('Orders retrieved successfully', $result);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve orders', $e->getMessage());
        }
    }

    public function store(CreateOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        try {
            $order = $action->execute($request->validated(), Auth::user());

            return $this->createdResponse(
                'Order created successfully',
                new OrderResource($order)
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage(), $e->errors());
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create order', $e->getMessage());
        }
    }

    public function cancel(Order $order, CancelOrderAction $action): JsonResponse
    {
        try {
            $order = $action->execute($order, Auth::user());

            return $this->okResponse('Order cancelled successfully', new OrderResource($order));
        } catch (ValidationException $e) {
            return $this->badRequestResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to cancel order', $e->getMessage());
        }
    }

    public function history(Request $request, GetOrderHistoryAction $action): JsonResponse
    {
        try {
            $orders = $action->execute(
                Auth::user(),
                $request->get('status'),
                $request->get('symbol')
            );

            return $this->okResponse(
                'Order history retrieved successfully',
                OrderResource::collection($orders)
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve order history', $e->getMessage());
        }
    }
}
