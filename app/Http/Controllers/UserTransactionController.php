<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Services\UserTransactionService;
use Illuminate\Validation\ValidationException;
use Throwable;
use Exception;

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
}
