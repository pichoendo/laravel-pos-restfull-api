<?php

namespace App\Http\Controllers;

use App\Models\ImageFile;
use App\Responses\ApiResponse;
use App\Services\FileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Create a new instance of the controller.
     * 
     * 
     * This constructor initializes the `CouponController` by injecting 
     * the `FileService` and `ApiResponse` dependencies. The `FileService` 
     * is used for handling Coupon CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     * 
     * @param \App\Services\FileService $fileService The service for managing coupons.
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public FileService $fileService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }


    /**
     * Display the specified file.
     *
     * @param \App\Models\Category $category The category instance to be displayed.
     * @return \Illuminate\Http\Response The response containing the category data.
     */
    public function show(ImageFile $imageFile)
    {
        return $this->fileService->getFile($imageFile);
    }
}
