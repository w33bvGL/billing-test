<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Resources\UserTransactionCollection;
use App\Http\Services\UserTransactionService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserTransactionController extends Controller
{
    private UserTransactionService $userTransactionService;

    public function __construct(UserTransactionService $userTransactionService)
    {
        $this->userTransactionService = $userTransactionService;
    }

    public function deposit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'required|string',
            ]);

            $this->userTransactionService->createDeposit($request->user_id, $request->amount, $request->description);

            return response()->json(['message' => 'Deposit successful']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function withdrawal(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'required|string',
            ]);

            $this->userTransactionService->createWithdrawal($request->user_id, $request->amount, $request->description);

            return response()->json(['message' => 'Withdrawal successful'], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getUserTransactions(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sort_by' => 'required|in:created_at,amount,description',
                'sort_order' => 'required|in:asc,desc',
            ]);

            $user = User::findOrFail($id);

            $sortBy    = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $transactions = $this->userTransactionService->getUserTransactions($user->id, $sortBy, $sortOrder);

            return response()->json(new UserTransactionCollection($transactions));
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function getAllTransactions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sort_by' => 'required|in:created_at,amount,description',
                'sort_order' => 'required|in:asc,desc',
            ]);

            $sortBy    = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $transactions = $this->userTransactionService->getAllTransactions($sortBy, $sortOrder);

            return response()->json(new UserTransactionCollection($transactions));
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
