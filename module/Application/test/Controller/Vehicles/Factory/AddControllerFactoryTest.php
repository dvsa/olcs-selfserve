<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicles\Factory;

use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;

/**
 * @see AddControllerFactory
 */
class AddControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var AddControllerFactory
     */
    protected $sut;

    /**
     * @test
     */
    public function createService_IsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'createService']);
    }

    protected function setupSut()
    {
        $this->sut = new AddControllerFactory();
    }

    protected function setUpDefaultServices(\Laminas\ServiceManager\ServiceManager $serviceManager)
    {
        // TODO: Implement setUpDefaultServices() method.
    }
}
