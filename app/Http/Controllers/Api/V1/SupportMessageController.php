<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSupportMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $messages = $request->user()
            ->supportMessages()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    public function store(StoreSupportMessageRequest $request): JsonResponse
    {
        $message = $request->user()->supportMessages()->create($request->validated());

        return response()->json($message, 201);
    }
}
