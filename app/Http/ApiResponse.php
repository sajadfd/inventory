<?php

namespace App\Http;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    } // end of success

    public static function error($message = 'Error', $statusCode = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    } // end of error

}
