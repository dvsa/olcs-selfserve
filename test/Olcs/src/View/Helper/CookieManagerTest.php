<?php

namespace OlcsTest\View\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Helper\CookieManager;

class CookieManagerTest extends MockeryTestCase
{
    /**
     * @var CookieManager
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new CookieManager();
    }

    public function testInvoke()
    {
        $this->assertInstanceOf(CookieManager::class, $this->sut->__invoke());
    }

    public function testSetConfig(){
        /** @var \Zend\ServiceManager\ServiceManager | m\MockInterface $mockSl */
        $mockSl = m::mock(\Zend\ServiceManager\ServiceManager::class);
        $mockSl->shouldReceive('getServiceLocator->get')->once()->with('Config')->andReturn(['cookie-manager' => ['TEST']]);
        $this->sut->setServiceLocator($mockSl);
        $this->assertEquals('["TEST"]', $this->sut->__invoke()->setConfig());
    }

    public function testGetCallbackWhenConfigSet() {
        /** @var \Zend\ServiceManager\ServiceManager | m\MockInterface $mockSl */
        $mockSl = m::mock(\Zend\ServiceManager\ServiceManager::class);
        $callback = 'success';
        $mockSl->shouldReceive('getServiceLocator->get')->once()->with('Config')->andReturn(['cookie-manager' => ['TEST','user-preference-saved-callback'=>$callback]]);
        $this->sut->setServiceLocator($mockSl);
        $this->assertContains("var ". $callback, $this->sut->__invoke()->getCallBack());
    }

    public function testGetCallbackWhenConfigNotSet() {
        /** @var \Zend\ServiceManager\ServiceManager | m\MockInterface $mockSl */
        $mockSl = m::mock(\Zend\ServiceManager\ServiceManager::class);
        $callback = 'success';
        $mockSl->shouldReceive('getServiceLocator->get')->once()->with('Config')->andReturn(['cookie-manager' => ['TEST','user-preference-saved-callback'=>false]]);
        $this->sut->setServiceLocator($mockSl);
        $this->assertEmpty($this->sut->__invoke()->getCallBack());
    }
}
