<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    /**
     * @var int http status code
     */
    protected $statusCode;

    public function __construct($message = "", $code = 0, $statusCode = 500)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        return response()->json([
            'code' => $this->code,
            'server_time' => time(),
            'message' => $this->message
        ])->setStatusCode($this->statusCode);
    }
}
