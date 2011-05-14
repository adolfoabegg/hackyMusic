<?php
/**
 * Parent for classes querying Last.fm's APIs
 *
 * @category YHack
 * @package LastFm
 * @copyright Codefathers team
 */

abstract class YHack_LastFmAPI_Abstract 
{
    /**
     * Last.fm's API endpoint
     */
    const API_ENDPOINT = 'http://ws.audioscrobbler.com/2.0/';
    
    /**
     * The Last.fm's method to execute.
     * @var string
     */
    protected $_rpcMethod = null    ;
    
    /**
     * Last.fm API key
     * 
     * @var string
     */
    protected $_apiKey = null;

    /**
     * Get the Last.fm API key
     *
     * @return string
     */
    public function getApiKey()
    {
        if ($this->_apiKey) {
            return $this->_apiKey;
        }

        $config = Zend_Registry::get('AppConfig');
        
        if (isset($config->lastfm->apiKey)) {
            return $config->lastfm->apiKey;
        }
        
        throw new YHack_LastFmAPI_Exception_NoApiKey(
            'No API key provided!'
        );
    }
    
    /**
     * Set the Last.fm API key
     *
     * @param string $apiKey
     * @access public
     * @return YHack_LastFm_Abstract
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
     * @return YHack_LastFmAPI_Abstract
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
    
    /**
     * Gets data from Last.fm
     * 
     * @param array $parameters
     * @return SimpleXMLElement
     */
    protected function _rpc(array $parameters)
    {
        $xmlBody = '';
       
        $parameters['api_key'] = $this->getApiKey();
        $parameters['method'] = $this->_rpcMethod;
                
        $httpClient = new Zend_Http_Client(self::API_ENDPOINT);
        $httpClient->setParameterGet($parameters);
        $httpResponse = $httpClient->request('GET');
        $xmlBody = $httpResponse->getBody();
        $xmlObject = simplexml_load_string($xmlBody);

        return $xmlObject;
    }
}

