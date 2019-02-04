<?php

namespace Olcs\Service\Surrender;

class SurrenderStateService
{
    private $surrenderData;

    public function __construct(array $surrenderData)
    {
        $this->surrenderData = $surrenderData;
    }

    public function fetchRoute(): string
    {
        return '';
    }

    public function hasExpired(): bool
    {
        $now = new \DateTimeImmutable();

        $modified = $this->getModifiedOn() ?? $this->getCreatedOn();
        $modified = new \DateTimeImmutable($modified);

        return $now->diff($modified)->days >= 2;
    }

    private function getStatus()
    {
        return $this->surrenderData['status']['id'];
    }

    private function getCreatedOn()
    {
        return $this->surrenderData['createdOn'];
    }

    private function getModifiedOn()
    {
        return $this->surrenderData['lastModifiedOn'];
    }

    private function getGoodsDiscsOnLicence()
    {
        return $this->surrenderData['goodsDiscsOnLicence']['discCount'];
    }

    private function getPsvDiscsOnLicence()
    {
        return $this->surrenderData['psvDiscsOnLicence']['discCount'];
    }

    private function getDiscsOnSurrender()
    {
        $discDestroyed = $this->surrenderData['discDestroyed'] ?? 0;
        $discLost = $this->surrenderData['discLost'] ?? 0;
        $discStolen = $this->surrenderData['discStolen'] ?? 0;

        return $discDestroyed + $discLost + $discStolen;
    }

    private function getAddressLastModifiedOn()
    {
        return $this->surrenderData['addressLastModified'];
    }

}
