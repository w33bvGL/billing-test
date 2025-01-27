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

    /**
     * @OA\Post(
     *     path="/api/v1/user",
     *     summary="Регистрация нового пользователя",
     *     description="Создает нового пользователя",
     *     tags={"Пользователи"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "email", "password", "password_confirmation"},
     *
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password123")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="201",
     *         description="Пользователь успешно создан",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="User Successfully created"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01T12:00:00Z")
     *             )
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
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/user/{id}",
     *     summary="Получение информации о пользователе по ID",
     *     description="Получает информацию о пользователе",
     *     tags={"Пользователи"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Идентификатор пользователя",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Информация о пользователе",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01T12:00:00Z")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function show($id): UserResource
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     summary="Получение списка всех пользователей",
     *     description="Получает список всех пользователей",
     *     tags={"Пользователи"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Список пользователей",
     *
     *         @OA\JsonContent(
     *             type="array",
     *             items={
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01T12:00:00Z")
     *             }
     *         )
     *     )
     * )
     */
    public function index(): UserCollection
    {
        $users = User::all();

        return new UserCollection($users);
    }
}
