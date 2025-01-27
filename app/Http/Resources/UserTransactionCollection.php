<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserTransactionCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->transform(function ($transaction) {
            return new UserTransactionResource($transaction);
        })->toArray();
    }
}
