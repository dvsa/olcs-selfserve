<?php

namespace Olcs\Exception\Http;

use Throwable;

class NotFoundHttpException extends HttpException
{
    /**
     * @inheritDoc
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}