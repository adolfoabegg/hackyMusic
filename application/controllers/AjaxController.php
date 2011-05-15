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
		if (!$this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
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

	}

	/**
	 * Fetch the song list
	 * 
	 * @access public
	 * @return void
	 */
	public function fetchSongsAction()
	{
		$address      = $this->_getParam('address');

		$yahooWoeId   = new YHack_YahooAPI_WoeId();
		$woeId        = $yahooWoeId->fetchByAddress($address);
		$yahooWeather = new YHack_YahooAPI_Weather(array('woeid' => $woeId));

        try {
            $weatherCondition = $yahooWeather->getCode();
        } catch (YHack_YahooAPI_Exception_LocationNotFound $e) {
            $weatherCondition = 'unknown';
        }

		$orchestrator = YHack_Orchestrator::getInstance();
        $orchestrator->setWeatherCondition($weatherCondition);
        $songsList    = $orchestrator->getSongsList();

		$mapper = YHack_Mapper::getInstance();

		$data = array(
			'songs'		=> $songsList,
			'isDaytime' => $yahooWeather->getIsDaytime(),
			'isSunny'	=> $mapper->isSunny($weatherCondition),
		);

		$this->view->data = $data;
	}
}

