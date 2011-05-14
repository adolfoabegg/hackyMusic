<?php
/**
 * Parent for classes querying Yahoo!'s APIs
 *
 * @category YHack
 * @package YahooAPI
 * @copyright Codefathers team
 */

class YHack_YahooAPI_Abstract 
{
    /**
     * Yahoo API key
     * 
     * @var string
     * @access protected
     */
    protected $_apiKey = null;

    /**
     * Get the Yahoo! API key
     *
     * @access public
     * @return string
     */
    public function getApiKey()
    {
        if ($this->_apiKey) {
            return $this->_apiKey;
        }

        $config = Zend_Registry::get('AppConfig');
        
        if (isset($config->yahoo->apiKey)) {
            return $config->yahoo->apiKey;
        }
        
        throw new YHack_YahooAPI_Exception_NoApiKey(
            'No API key provided!'
        );
    }
    
    /**
     * Set the Yahoo! API key
     *
     * @param string $apiKey
     * @access public
     * @return YHack_YahooAPI_Abstract
     */
    public function setApiKey($apiKey)
    {
        $this->_apiKey = $apiKey;
        return $this;
    }
    
    /**
     * Set options
     *
     * @param array|Zend_Config $config
     * @return YHack_YahooAPI_Weather
     */
    public function setOptions($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
    
        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    
        return $this;
    }
}

