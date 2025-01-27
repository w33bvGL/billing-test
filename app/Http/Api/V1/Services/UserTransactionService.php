<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Services;

use App\Models\User;
use App\Models\UserTransaction;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserTransactionService
{
    /**
     * @throws Throwable
     */
    public function createDeposit(int $userId, float $amount, ?string $description): void
    {
        User::findOrFail($userId);

        $this->createTransaction($userId, $amount, 'deposit', $description);
    }

    /**
     * @throws Throwable
     */
    public function createWithdrawal(int $userId, float $amount, ?string $description): void
    {
        User::findOrFail($userId);

        $this->createTransaction($userId, $amount, 'withdrawal', $description);
    }

    public function getUserTransactions(int $userId, string $sortBy = 'created_at', string $sortOrder = 'desc'): Collection
    {
        $user = User::findOrFail($userId);

        return $user->transactions()
            ->orderBy($sortBy, $sortOrder)
            ->get();
    }

    public function getAllTransactions(string $sortBy = 'created_at', string $sortOrder = 'desc'): Collection
    {
        return UserTransaction::orderBy($sortBy, $sortOrder)->get();
    }

    /**
     * @throws Throwable
     */
    private function createTransaction(int $userId, float $amount, string $type, ?string $description): void
    {
        $user        = User::findOrFail($userId);
        $userBalance = $user->balance;

        if ($type === 'withdrawal' && $userBalance->balance < $amount) {
            throw new Exception('Insufficient balance');
        }

        DB::transaction(function () use ($user, $userBalance, $amount, $type, $description) {

            if ($type === 'deposit') {
                $userBalance->balance += $amount;
            } else {
                $userBalance->balance -= $amount;
            }

            $userBalance->save();

            UserTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
            ]);
        });
    }
}
