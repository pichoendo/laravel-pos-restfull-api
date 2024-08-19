<?php

namespace App\Responses;

class ApiResponse
{
    public function success($data = [], $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'result' => $data
        ], $status);
    }
   
    public function error($message = "Failed to update coupon. Please try again later.", $status = 400, $errors = [])
    {
        $response = [
            'status' => false,
            'code' => $status,
            'message' => $message,
        ];

        if (!empty($errors))
            $response['errors'] = $errors;

        return response()->json($response, $status);
    }
}
