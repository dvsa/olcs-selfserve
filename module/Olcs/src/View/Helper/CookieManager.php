<?php

namespace Olcs\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\HelperInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class CookieManagerHelper
 *
 * @package Olcs\View\Helper
 */
class CookieManager extends AbstractHelper implements HelperInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function __invoke()
    {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCallBack()
    {
        $config = $this->getConfig();
        if ($config['user-preference-saved-callback'] !== false) {
            return "var success = function(){ var cookieNotice = document.querySelector('.gem-c-notice');
            var main = document.getElementById('main');
            cookieNotice.after(main);
            }";
        }
    }

    public function setConfig()
    {
        return json_encode($this->getConfig());
    }

    private function getConfig(): array
    {
        $config = $this->getServiceLocator()->getServiceLocator()->get('Config');
        return $config['cookie-manager'];
    }
}
