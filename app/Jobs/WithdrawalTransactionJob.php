<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Http\Api\V1\Services\UserTransactionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WithdrawalTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public float $amount;

    public ?string $description;

    public function __construct(int $userId, float $amount, ?string $description)
    {
        $this->userId      = $userId;
        $this->amount      = $amount;
        $this->description = $description;
    }

    public function handle(UserTransactionService $userTransactionService): void
    {
        try {
            $userTransactionService->createWithdrawal($this->userId, $this->amount, $this->description);
        } catch (Exception $e) {
            Log::error("WithdrawalTransactionJob failed: {$e->getMessage()}");
        } catch (\Throwable $e) {
        }
    }
}
