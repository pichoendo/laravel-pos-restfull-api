<?php

namespace App\Http\Controllers;


use App\Responses\ApiResponse;

/**
 * @OA\Info(
 *    title="Laravel 11 Pos API Documentation",
 *    version="1.0.0",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    public function __construct(public ApiResponse $apiResponse, public $perPage = 10, public $startPage = 10) {}
}
