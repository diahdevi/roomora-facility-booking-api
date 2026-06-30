<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Override;

class BookingConflictException extends Exception
{
    public function __construct(string $message = "Udah ada yg booking")
    {
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 409); // 409 Conflict
    }
}
