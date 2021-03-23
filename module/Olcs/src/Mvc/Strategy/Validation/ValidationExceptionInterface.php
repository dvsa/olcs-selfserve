<?php

declare(strict_types=1);

namespace Olcs\Mvc\Strategy\Validation;

use Throwable;

// @todo this should be a http exception

interface ValidationExceptionInterface extends Throwable
{
    /**
     * Gets validation messages.
     *
     * @return array
     */
    public function getValidationMessages(): array;

    /**
     * Gets the input being validated.
     *
     * @return array
     */
    public function getInput(): array;
}
