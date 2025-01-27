<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Resources\UserCollection;
use App\Http\Api\V1\Resources\UserResource;
use App\Http\Api\V1\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = $this->userService->createUser($validatedData);

            return response()->json([
                'message' => 'User Successfully created',
                'user' => new UserResource($user),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }
    }

    public function show($id): UserResource
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
    }

    public function index(): UserCollection
    {
        $users = User::all();

        return new UserCollection($users);
    }
}
