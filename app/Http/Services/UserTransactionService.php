<?php

declare(strict_types=1);

namespace App\Http\Services;

use App\Models\User;
use App\Models\UserTransaction;
use Exception;
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
