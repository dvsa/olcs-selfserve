<?php

namespace Olcs\Exception\Licence;

use Exception;

class LicenceNotFoundException extends Exception
{
    // @todo constructor should take DTOs

    /**
     * Creates a new exception where one or more vehicles were not found from a given set of ids.
     *
     * @param int $licenceId
     * @return static
     */
    public static function withId(int $licenceId)
    {
        return new static(sprintf('Licence was not found with id: "%s".', $licenceId));
    }
}
