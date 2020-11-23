<?php

namespace Olcs\Repository\Licence;

use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\Exception\Licence\LicenceNotFoundWithIdException;
use Olcs\Repository\QueryRepository;

class LicenceRepository extends QueryRepository
{
    /**
     * Gets the licence number for a licence from a given licence id.
     *
     * @param int $licenceId
     * @return LicenceDTO
     * @throws LicenceNotFoundWithIdException
     */
    public function findOneById(int $licenceId): LicenceDTO
    {
        $query = Licence::create(['id' => $licenceId]);
        try {
            $queryResult = $this->queryHandler->__invoke($query);
        } catch (NotFoundException|AccessDeniedException $exception) {
            throw new LicenceNotFoundWithIdException($licenceId);
        }
        return new LicenceDTO($queryResult->getResult());
    }
}