<?php

namespace OlcsTest\Session;

use Mockery as m;

use Olcs\Session\DigitalSignature;

/**
 * Class DigitalSignatureTest
 */
class DigitalSignatureTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var DigitalSignature
     */
    private $sut;

    public function testHasApplicationId()
    {

        $this->assertFalse($this->sut->hasApplicationId());
        $this->sut->setApplicationId(12);
        $this->assertTrue($this->sut->hasApplicationId());
    }

    public function testSetGetApplication()
    {

        $this->assertSame(0, $this->sut->getApplicationId());
        $this->sut->setApplicationId('12');
        $this->assertSame(12, $this->sut->getApplicationId());
    }

    public function testHasContinuationDetailId()
    {

        $this->assertFalse($this->sut->hasContinuationDetailId());
        $this->sut->setContinuationDetailId(12);
        $this->assertTrue($this->sut->hasContinuationDetailId());
    }

    public function testSetGetContinuationDetail()
    {

        $this->assertSame(0, $this->sut->getContinuationDetailId());
        $this->sut->setContinuationDetailId('12');
        $this->assertSame(12, $this->sut->getContinuationDetailId());
    }

    public function testSetGetTransportManagerApplicationId()
    {
        $this->assertSame(0, $this->sut->getTransportManagerApplicationId());
        $this->sut->setTransportManagerApplicationId('12');
        $this->assertSame(12, $this->sut->getTransportManagerApplicationId());
    }

    public function testHasTransportManagerApplicationId()
    {

        $this->assertEquals(0, $this->sut->getTransportManagerApplicationId());
        $this->sut->setTransportManagerApplicationId(7);
        $this->assertTrue($this->sut->hasTransportManagerApplicationId());
    }

    public function testSetGetTransportManagerApplicationOperatorSignature()
    {
        $this->sut->setRole(12);
        $this->assertTrue($this->sut->getTransportManagerApplicationOperatorSignature());
        $this->assertEquals(12, $this->sut->getTransportManagerApplicationId());
    }

    public function testGetSetLva()
    {
        $this->sut->setLva('application');
        $this->assertEquals('application', $this->sut->getLva());
    }

    public function testHasLva()
    {

        $this->assertEquals(0, $this->sut->getLva());
        $this->sut->setLva('__TEST__');
        $this->assertTrue($this->sut->hasLva());
    }

    /**
     * setUp
     *
     a*/
    public function setUp()
    {
        $this->sut = new DigitalSignature();
    }
}
