<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private UserService $userService;

    /**
     * @param UserService $userService
     */
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
                'message' => 'Пользователь успешно создан',
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
