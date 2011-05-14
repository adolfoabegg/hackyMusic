<?php
/**
 * Error controller - manages the exceptions in the application
 *
 *
 * @category application
 * @package controllers
 * @copyright Codefathers team
 */

class ErrorController extends YHack_Controller
{
    /**
     * Display the errors
     *
     * @access public
     * @return void
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->render('e404');
                break;
            default:    
                $this->view->exception = $errors->exception;
                $this->render('error');
                break;
        }
    }
}

