<?php

namespace App\Helpers;

class ResponseFormatter
{
    public static function success($data = null, string $message = 'OK', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private static function defaultMessageForStatus(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthenticated',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            410 => 'Gone',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'Error',
        };
    }

    public static function error(?string $message = null, int $code = 400, $errors = null)
    {
        $message = ($message === null || $message === '') ? self::defaultMessageForStatus($code) : $message;
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
