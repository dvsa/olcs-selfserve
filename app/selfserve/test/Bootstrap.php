<?php

namespace OlcsTest;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Di\Di;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{

    protected static $serviceManager;
    protected static $di;

    public static function init()
    {
        // Setup the autloader
        $loader = static::initAutoloader();
        $loader->addPsr4('OlcsTest\\', __DIR__ . '/Olcs');
        $loader->addPsr4('CommonTest\\', __DIR__ . '/../vendor/olcs/OlcsCommon/application_test/Common/src/Common/');

        // Grab the application config
        $config = include dirname(__DIR__) . '/config/application.config.php';

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        // Mess up the backend, so any real rest calls will fail
        $config = $serviceManager->get('Config');
        $serviceManager->setAllowOverride(true);
        $config['service_api_mapping']['endpoints']['backend'] = 'http://some-fake-backend/';
        $serviceManager->setService('Config', $config);
        $serviceManager->setAllowOverride(false);

        static::$serviceManager = $serviceManager;

        // Setup Di
        $di = new Di();

        $di->instanceManager()->addTypePreference(
            'Zend\ServiceManager\ServiceLocatorInterface',
            'Zend\ServiceManager\ServiceManager'
        );
        $di->instanceManager()->addTypePreference(
            'Zend\EventManager\EventManagerInterface',
            'Zend\EventManager\EventManager'
        );
        $di->instanceManager()->addTypePreference(
            'Zend\EventManager\SharedEventManagerInterface',
            'Zend\EventManager\SharedEventManager'
        );

        self::$di = $di;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        return require('vendor/autoload.php');
    }

    public static function getDi()
    {
        return self::$di;
    }
}

Bootstrap::init();
