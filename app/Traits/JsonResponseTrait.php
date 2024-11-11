<?php

namespace App\Traits;

trait JsonResponseTrait
{
    protected function successResponse($data = null, $message = '', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function errorResponse($message = '', $status = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'data' => $data,
            'message' => $message,
        ], $status);
    }
}
