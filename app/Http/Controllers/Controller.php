<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
    protected function sendResponse(int $statusCode = 200, $message = null, $data = "Success")
    {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function sendMessage(int $statusCode = 200, $message = null)
    {
        return $this->sendResponse($statusCode, $message, null);
    }

    protected function sendError(int $statusCode = 500, $message = null, $data = null)
    {
        return $this->sendResponse($statusCode, $message, $data);
    }
}
