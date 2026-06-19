<?php

namespace App\Shared\Exceptions;

use Exception;

class ApiException extends Exception
{
    /**
     * The HTTP status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Create a new exception instance
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  Exception|null  $previous
     */
    public function __construct(string $message = '', int $statusCode = 400, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Render the exception into a response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
        ], $this->statusCode);
    }
}
