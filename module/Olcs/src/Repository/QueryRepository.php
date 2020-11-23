<?php

namespace Olcs\Repository;

use Common\Controller\Plugin\HandleQuery;

class QueryRepository
{
    /**
     * @var HandleQuery
     */
    protected $queryHandler;

    /**
     * @param HandleQuery $queryHandler
     */
    public function __construct(HandleQuery $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }
}