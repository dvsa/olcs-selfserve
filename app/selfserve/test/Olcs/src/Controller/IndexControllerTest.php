<?php

namespace OlcsTest\Controller;

use Common\Rbac\User;
use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\IndexController;

/**
 * Class Index Controller Test
 */
class IndexControllerTest extends MockeryTestCase
{
    /** @var IndexController|\Mockery\MockInterface  */
    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock(IndexController::class)
            ->makePartial();
    }

    public function testIndexLogin()
    {
        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturnNull();

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('auth/login')
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexLogout()
    {
        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn(new User());

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(false);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('auth/logout')
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexDashboard()
    {
        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn(new User());

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('dashboard', [], ['code' => 303])
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }
}
