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

	/**
	 * Perform the geolocation
	 * 
	 * @access public
	 * @return void
	 */
	public function geolocationAction()
	{
		$latitude    = $this->_getParam('latitude');
		$longitude   = $this->_getParam('longitude');

		// get the address
		$woeidParser = new YHack_YahooAPI_WoeId();
		$address	 = $woeidParser->fetchAddressByLatitudeAndLongitude($latitude, $longitude);

		$this->view->address = $address;
	}
}

