<?php
/**
 * Bootstrap the application
 *
 *
 * @category application
 * @copyright Codefathers team
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Init the autoloader
     * 
     * @access protected
     * @return void
     */
    protected function _initAutoloader()
    {
        $loader = Zend_Loader_Autoloader::getInstance();

        $loader->registerNamespace('YHack_');
        $loader->registerNamespace('ZendX_');
        $loader->registerNamespace('ZFAdmin_');
        $loader->registerNamespace('ZFAdminCli_');

        $loader->setFallbackAutoloader(true);
    }

    /**
     * Init the generic configuration object
     * 
     * @access protected
     * @return void
     */
    protected function _initConfig()
    {
        $app = $this->getApplication();
        $config = new Zend_Config($app->getOptions());

        Zend_Registry::set('AppConfig', $config);
    }

    /**
     * Initialize the default database connection
     * 
     * @access protected
     * @return void
     */
    protected function _initDb()
    {
        $config = Zend_Registry::get('AppConfig');
        $dbAdapter = Zend_Db::factory($config->resources->db);

        Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);

        ZFAdmin_Table_TableAbstract::setGlobalItemsPerPage(5);
    }
    

    /**
     * Init the view options
     * 
     * @access protected
     * @return void
     */
    protected function _initViewOptions()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');

        $view->addHelperPath('ZFAdmin/View/Helper', 'ZFAdmin_View_Helper');
        $view->addHelperPath('YHack/View/Helper', 'YHack_View_Helper');

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');
        $view->addScriptPath(APPLICATION_PATH . '/views/partials');
        $view->addScriptPath(APPLICATION_PATH . '/views/paginators');

        if (((int) ini_get('short_open_tag')) == 0) {
            $view->setUseStreamWrapper(true);
        }
    }

    /**
     * Inits ZFDebug - only available in development
     * 
     * @access protected
     * @return void
     */
    protected function _initZfDebug()
    {
        if (APPLICATION_ENV == 'development') {
            // enhanced error reporting, excerpt from PHP's manual (http://php.net/manual/en/function.error-reporting.php):
            // Passing in the value -1 will show every possible error, even when new 
            // levels and constants are added in future PHP versions.
            error_reporting(-1);

            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->registerNamespace('ZFDebug');

            $front = Zend_Controller_Front::getInstance();

            $options = array(
                'plugins' => array(
                    'Database',
                    'Exception',
                    'File',
                    'Html',
                    'Memory',
                    'Registry',
                    'Time',
                    'Variables',
                ),
            );

            $zfdebug = new ZFDebug_Controller_Plugin_Debug($options);

            Zend_Registry::set('ZFDEBUG', $zfdebug);

            $front->registerPlugin($zfdebug);
        }
	}
}
