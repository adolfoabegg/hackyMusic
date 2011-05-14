<?php
/**
 * Manages all the AJAX calls
 *
 * @category application
 * @package controllers
 * @copyright Codefathers team
 */

class AjaxController extends YHack_Controller
{
	/**
	 * Overrides Zend_Controller_Action::init()
	 *
	 * @access public
	 * @return void
	 */
	public function init()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->_redirect('/');
		}

		$this->_helper->layout()->setLayout('ajax');
	}
}

