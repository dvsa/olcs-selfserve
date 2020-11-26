<?php

namespace Olcs\Exception\Http;

use Exception;
use Throwable;

class HttpException extends Exception
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @param int $statusCode
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $statusCode, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
}
