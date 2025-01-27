<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Saga Online Application Programming Interface API",
 *     version="1.0.0",
 *     description="API для работы cайта SagaOnline",
 *
 *     @OA\Contact(
 *         name="Vahe",
 *         email="w33bv.gl@gmail.com"
 *     )
 * )
 */
abstract class Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"AuthController"},
     *     summary="AuthController::register | Регистрация пользователя",
     *
     *     @OA\Parameter( name="Accept-Language", in="header", description="Язык ответа", required=true, @OA\Schema(type="string", example="en")),
     *
     *     @OA\Response( response=200, description="Успешный ответ"),
     * )
     */
    public function exampleMethod() {}
}
