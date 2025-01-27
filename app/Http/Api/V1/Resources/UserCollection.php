<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($user) {
            return new UserResource($user);
        })->toArray();
    }
}
