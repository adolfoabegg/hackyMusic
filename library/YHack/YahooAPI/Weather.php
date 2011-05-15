<?php
/**
 * Fetch weather information from Yahoo!
 *
 *
 * @category YHack
 * @package YahooAPI
 * @copyright Codefathers team
 */

class YHack_YahooAPI_Weather extends YHack_YahooAPI_Abstract
{
    /**
     * Yahoo! Url
     */
    const RSS_URL = 'http://weather.yahooapis.com/forecastrss';

    /**
     * Downloaded XML data
     * 
     * @var SimpleXMLElement
     * @access protected
     */
    protected $_xml;

    /**
     * Yahoo! "Where on Earth" location ID
     * 
     * @var numeric
     * @access protected
     */
    protected $_woeid;

    /**
     * Unit code (c)elsius or (f)arenheit
     * 
     * @var string
     * @access protected
     */
    protected $_unit = 'c';

    /**
     * Construct the weather query
     * 
     * @param array|Zend_Config $config 
     * @access public
     */
    public function __construct($config)
    {
        $this->setOptions($config);
    }

    /**
     * Get the condition 
     * 
     * @see http://developer.yahoo.com/weather/#codes
     * @access public
     * @return int
     */
    public function getCode()
    {
        $xml = $this->_getXml();

        $condition  = $xml->xpath('//yweather:condition');
        $attributes = $condition[0]->attributes();

        return (int) $attributes['code'];
    }

    /**
     * Get datetime status of the given location
     * 
     * @access public
     * @return void
     */
    public function getIsDaytime()
    {
        $xml = $this->_getXml();

        $astronomy  = $xml->xpath('//yweather:astronomy');
        $attributes = $astronomy[0]->attributes();
        $sunrise    = (string) $attributes['sunrise'];
        $sunset     = (string) $attributes['sunset'];

        $condition  = $xml->xpath('//yweather:condition');
        $attributes = $condition[0]->attributes();
        $date       = (string) $attributes['date'];

        $time    = new Zend_Date($date);
        $sunrise = new Zend_Date($sunrise, Zend_Date::TIMES);
        $sunset  = new Zend_Date($sunset, Zend_Date::TIMES);

        if ($sunset->compare($time, Zend_Date::TIMES) == 1 && $time->compare($sunset, Zend_Date::TIMES) == -1) {
            return true;
        }

        return false;
    }

    /**
     * Get the xml
     * 
     * @access protected
     * @return SimpleXMLElement
     */
    protected function _getXml()
    {
        if (null === $this->_xml) {
            $request  = self::RSS_URL . '?w=' . $this->getWoeid() . '&u=' . $this->getUnit();
            $response = file_get_contents($request);

            $xml = new SimpleXMLElement($response);
            if ((string) $xml->channel->item->title == 'City not found') {
                throw new YHack_YahooAPI_Exception_LocationNotFound();
            }

            $xml->registerXPathNamespace(
                'yweather',
                'http://xml.weather.yahoo.com/ns/rss/1.0'
            );
            
            $this->_xml = $xml;
        }

        return $this->_xml;
    }

    /**
     * Get the woeid code
     *
     * @access public
     * @return numeric
     */
    public function getWoeid()
    {
        return $this->_woeid;    
    }
    
    /**
     * Set the woeid code
     *
     * @param numeric $woeid
     * @access public
     * @return YHack_YahooAPI_Weather
     */
    public function setWoeid($woeid)
    {
        $this->_woeid = $woeid;
        return $this;
    }
    
    /**
     * Get the unit (c)elsius or (f)arenheit
     *
     * @access public
     * @return string
     */
    public function getUnit()
    {
        return $this->_unit;    
    }
    
    /**
     * Set the unit (c)elsius or (f)arenheit
     *
     * @param string $unit
     * @access public
     * @return YHack_YahooAPI_Weather
     */
    public function setUnit($unit)
    {
        $this->_unit = $unit;
        return $this;
    }
}
