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
    protected static $config = array();
    protected static $di;

    public static function init()
    {
        // Setup the autloader
        $loader = static::initAutoloader();
        $loader->addPsr4('OlcsTest\\', __DIR__ . '/Olcs/src');

        // Grab the application config
        $config = include dirname(__DIR__) . '/config/application.config.php';

        self::$config = $config;

        self::getServiceManager();

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
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', self::$config);
        $serviceManager->get('ModuleManager')->loadModules();
        $serviceManager->setAllowOverride(true);

        // Mess up the backend, so any real rest calls will fail
        $config = $serviceManager->get('Config');
        $config['service_api_mapping']['endpoints']['backend'] = 'http://some-fake-backend/';
        $serviceManager->setService('Config', $config);

        return $serviceManager;
    }

    protected static function initAutoloader()
    {
        require('init_autoloader.php');

        return $loader;
    }

    public static function getDi()
    {
        return self::$di;
    }
}

Bootstrap::init();
