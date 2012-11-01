<?php
//date_default_timezone_set('Atlantic/Canary');//Europe/Madrid
//setlocale(LC_ALL, 'es_ES', 'es_ES.iso-8859-1');
// Define path to root directory
defined('ROOT_PATH')
    || define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

//dependiendo del dominio cargamos un fichero de configuracion u otro
//$ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/hosts/' . $_SERVER["HTTP_HOST"] . '.ini');
//$application->setOptions($application->mergeOptions($ini->toArray(), $application->getOptions()));
	
$application->bootstrap()
            ->run();
          