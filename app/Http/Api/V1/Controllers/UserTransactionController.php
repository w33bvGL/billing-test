<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Resources\UserTransactionCollection;
use App\Http\Api\V1\Services\UserTransactionService;
use App\Jobs\DepositTransactionJob;
use App\Jobs\WithdrawalTransactionJob;
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

    /**
     * @OA\Post(
     *     path="/api/v1/user/transaction/deposit",
     *     summary="Пополнение счета пользователя",
     *     description="Пополнение счета пользователя через транзакцию депозита",
     *     tags={"Транзакции"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id", "amount", "description"},
     *
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", format="float", example=100.50),
     *                 @OA\Property(property="description", type="string", example="Пополнение через банк"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Транзакция депозита успешно завершена",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Deposit successful")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="422",
     *         description="Ошибка валидации данных",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             additionalProperties=true
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Внутренняя ошибка сервера",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function deposit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'required|string',
            ]);

            // $this->userTransactionService->createDeposit($request->user_id, $request->amount, $request->description);
            DepositTransactionJob::dispatch($request->user_id, $request->amount, $request->description);

            return response()->json(['message' => 'Deposit successful']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/user/transaction/withdrawal",
     *     summary="Снятие средств с счета пользователя",
     *     description="Снятие средств с счета пользователя через транзакцию вывода",
     *     tags={"Транзакции"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id", "amount", "description"},
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", format="float", example=50.75),
     *                 @OA\Property(property="description", type="string", example="Снятие через терминал"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Транзакция вывода успешно завершена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Withdrawal successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ошибка валидации данных",
     *         @OA\JsonContent(
     *             type="object",
     *             additionalProperties=true
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function withdrawal(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'required|string',
            ]);

            // $this->userTransactionService->createWithdrawal($request->user_id, $request->amount, $request->description);
            WithdrawalTransactionJob::dispatch($request->user_id, $request->amount, $request->description);

            return response()->json(['message' => 'Withdrawal successful'], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/transaction/history/{id}",
     *     summary="Получение списка транзакций пользователя",
     *     description="Получение всех транзакций для пользователя по ID",
     *     tags={"Транзакции"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Идентификатор пользователя",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"sort_by", "sort_order"},
     *                 @OA\Property(property="sort_by", type="string", enum={"created_at", "amount", "description"}, example="created_at"),
     *                 @OA\Property(property="sort_order", type="string", enum={"asc", "desc"}, example="desc")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Список транзакций пользователя",
     *         @OA\JsonContent(
     *             type="array",
     *             items={
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", format="float", example=50.75),
     *                 @OA\Property(property="description", type="string", example="Пополнение через терминал"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01T12:00:00Z")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ошибка валидации данных",
     *         @OA\JsonContent(
     *             type="object",
     *             additionalProperties=true
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/user/transaction/history/",
     *     summary="Получение списка всех транзакций",
     *     description="Получение всех транзакций с сортировкой по различным полям",
     *     tags={"Транзакции"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"sort_by", "sort_order"},
     *                 @OA\Property(property="sort_by", type="string", enum={"created_at", "amount", "description"}, example="created_at"),
     *                 @OA\Property(property="sort_order", type="string", enum={"asc", "desc"}, example="desc")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Список всех транзакций",
     *         @OA\JsonContent(
     *             type="array",
     *             items={
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", format="float", example=50.75),
     *                 @OA\Property(property="description", type="string", example="Пополнение через терминал"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01T12:00:00Z")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ошибка валидации данных",
     *         @OA\JsonContent(
     *             type="object",
     *             additionalProperties=true
     *         )
     *     )
     * )
     */
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
