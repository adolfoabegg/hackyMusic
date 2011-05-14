<?php
/**
 * Application entry point
 *
 *
 * @category public
 * @copyright Codefathers team
 */

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

$environmentPath = APPLICATION_PATH . '/configs/environment.php';
if (!file_exists($environmentPath)) {
	require_once 'Zend/Exception.php';
    throw new Zend_Exception(
        sprintf(
            'Configuration file %1$s is not found. To create one, please read the comments in %1$s.example!',
            $environmentPath
        )
    );
}

require_once $environmentPath;

require_once 'Zend/Application.php';

$config     = array(APPLICATION_PATH . '/configs/application.ini');
$envConfig  = APPLICATION_PATH . '/configs/environment.ini';

if (file_exists($envConfig)) {
    $config []= $envConfig;
}

$application = new Zend_Application(
    APPLICATION_ENV,
    array('config' => $config,)
);

$application->bootstrap()
            ->run();
