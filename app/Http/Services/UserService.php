<?php

declare(strict_types=1);

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\UserBalance;

class UserService
{
    public function createUser($data): User
    {
         $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        UserBalance::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        return $user;
    }
}
