<?php

namespace App\Http\Responses;

class APIResponse
{
    /**
     * Return an error response.
     *
     * @param string $message The error message to return.
     * @param int $status The HTTP status code for the response (default is 400).
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message, $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Return a success response.
     *
     * @param mixed $result The result data to return.
     * @param string|null $message Optional success message to include in the response.
     * @param int $status The HTTP status code for the response (default is 200).
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($result, $message = null, $status = 200)
    {
        $response = [
            'success' => true,
            'result' => $result,
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }
}
