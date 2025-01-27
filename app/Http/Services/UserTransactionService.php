<?php

declare(strict_types=1);

namespace App\Http\Services;

use App\Models\User;
use App\Models\UserTransaction;
use http\Exception\BadConversionException;
use Illuminate\Support\Facades\DB;
use Throwable;
use Exception;
class UserTransactionService
{

    /**
     * @param int $userId
     * @param float $amount
     * @param string|null $description
     * @return void
     * @throws Throwable
     */
    public function createDeposit(int $userId, float $amount, ?string $description): void
    {
        User::findOrFail($userId);

        $this->createTransaction($userId, $amount, 'withdrawal', $description);
    }

    /**
     * @param int $userId
     * @param float $amount
     * @param string|null $description
     * @return void
     * @throws Throwable
     */
    public function createWithdrawal(int $userId, float $amount, ?string $description): void
    {
        User::findOrFail($userId);

        $this->createTransaction($userId, $amount, 'withdrawal', $description);
    }

    /**
     * @param int $userId
     * @param float $amount
     * @param string $type
     * @param string|null $description
     * @return void
     * @throws Throwable
     */
    private function createTransaction(int $userId, float $amount, string $type, ?string $description): void
    {
        $user = User::findOrFail($userId);
        $userBalance = $user->balance;

        if ($type === 'withdrawal' && $userBalance->balance < $amount) {
            throw new Exception('Insufficient balance');
        }

        DB::transaction(function () use ($userBalance, $amount, $type, $description) {
            if ($type === 'deposit') {
                $userBalance->balance += $amount;
            } else {
                $userBalance->balance -= $amount;
            }

            $userBalance->save();

            UserTransaction::create([
                'user_id' => $userBalance->user_id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
            ]);
        });
    }
}
