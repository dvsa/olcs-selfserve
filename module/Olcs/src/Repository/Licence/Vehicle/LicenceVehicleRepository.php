<?php

namespace Olcs\Repository\Licence\Vehicle;

use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Exception\Licence\Vehicle\VehiclesNotFoundWithIdsException;

class LicenceVehicleRepository
{
    /**
     * Finds one or more licence vehicles by vehicle id.
     *
     * @param array<int> $vehicleIds
     * @return array<LicenceVehicleDTO>
     * @throws VehiclesNotFoundWithIdsException
     */
    public function findByVehicleId(array $vehicleIds): array
    {
        if (empty($vehicleIds)) {
            return [];
        }
        $query = LicenceVehiclesById::create(['ids' => $vehicleIds]);
        try {
            $queryResult = $this->queryBus->__invoke($query);
        } catch (NotFoundException|AccessDeniedException $exception) {
            throw new VehiclesNotFoundWithIdsException($vehicleIds);
        }
        $licenceVehicles = $queryResult->getResult()['results'] ?? [];
        return array_map(function ($licenceVehicle) {
            return new LicenceVehicleDTO($licenceVehicle);
        }, $licenceVehicles);
    }
}