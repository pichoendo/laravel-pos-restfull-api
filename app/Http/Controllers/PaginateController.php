<?php

namespace App\Http\Controllers;


use App\Responses\ApiResponse;

abstract class PaginateController extends Controller
{
    public function __construct(public ApiResponse $apiResponse, public $PER_PAGE = 10, public $START_PAGE = 1) {}
}
