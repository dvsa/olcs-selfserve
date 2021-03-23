<?php

declare(strict_types=1);

namespace Olcs\Mvc\Strategy\Validation;

use Exception;

class ValidationException extends Exception implements ValidationExceptionInterface
{
    /**
     * @var array
     */
    protected $validationMessages = [];

    /**
     * @var array
     */
    protected $input = [];

    /**
     * @param array $messages
     * @param array $input
     */
    public function __construct(array $messages, array $input)
    {
        parent::__construct("Validation failed", 0, null);
        $this->validationMessages = $messages;
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }
}
