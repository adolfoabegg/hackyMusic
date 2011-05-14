<?php
/**
 * Convert a location to an Yahoo! "Where on Earth" code
 *
 * @category YHack
 * @package YahooAPI
 * @copyright Codefathers team
 */

class YHack_YahooAPI_WoeId extends YHack_YahooAPI_Abstract
{
    /**
     * Yahoo API url
     */
    const API_URL = 'http://where.yahooapis.com/geocode';

    /**
     * Fetch the WOEID code via an address
     * 
     * @param string $address 
     * @access public
     * @return numeric
     */
    public function fetchByAddress($address)
    {
        $request = self::API_URL . '?q=' . urlencode($address) . '&appid=' . $this->getApiKey();

        return $this->_parseRequest($request);
    }

    /**
     * Fetch the WOEID code via latitude / longitude
     * 
     * @param float $latitude 
     * @param float $longitude 
     * @access public
     * @return numeric
     */
    public function fetchByLatitudeAndLongitude($latitude, $longitude)
    {
        $request = self::API_URL . '?q=' . $latitude . ',' . $longitude . '&appid=' . $this->getApiKey();

        return $this->_parseRequest($request);
    }

    /**
     * Parse the request and fetch the WOEID
     * 
     * @param $request 
     * @access protected
     * @return void
     */
    protected function _parseRequest($request)
    {
        $response = file_get_contents($request);
        $xml      = new SimpleXMLElement($response);
        
        if ((int) $xml->Found == 0) {
            throw new YHack_YahooAPI_Exception_WoeidNotFound();
        }

        return (string) $xml->Result->woeid;
    }
}
