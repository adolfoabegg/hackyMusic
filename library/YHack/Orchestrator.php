<?php
/**
 * This class orchestrates all the APIs involved in the app.
 *
 * @category YHack
 * @package Orchestrator
 * @copyright Codefathers team
 */

class YHack_Orchestrator
{
    /**
     * The user's latitude
     * @var string
     */
    protected $_latitude = null;
    
    /**
     * The user's longitude
     * @var string
     */
    protected $_longitude = null;
    
    /**
     * The user's current address
     * @var string
     */
    protected $_address = null;
    
    /**
     * The weather condition of the user's city.
     * 
     * @var string
     */
    protected $_weatherCondition = null;
    
    /**
     * The tag that the app must use to get the songs list
     * 
     * @var string
     */
    protected $_tag = null;
    
    /**
     * Sets the user's latitude
     * 
     * @param float $latitude 
     */
    public function setLatitude($latitude)
    {
        $this->_latitude = $latitude;
    }
    
    /**
     * Sets the user's longitude
     * 
     * @param float $longitude 
     */
    public function setLongitude($longitude)
    {
        $this->_longitude = $longitude;
    }
    
    /**
     * Sets the user's current address
     * 
     * @param string $address 
     */
    public function setAddress($address)
    {
        $this->_address = $address;
    }
    
    /**
     * Single instance of the object
     *
     * @static
     * @var YHack_Orchestrator
     * @access private
     */
    private static $_instance = null;
    
    /**
     * Default contructor - must not be called from "outside"
     *
     * @access private
     * @return void
     */
    private function __construct()
    {
    }
    
    /**
     * Returns the singleton object
     *
     * @static
     * @access public
     * @return YHack_Orchestrator
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new YHack_Orchestrator();
        }
        return self::$_instance;
    }
    
    /**
     * Gets a list of related songs using many parameters:
     * - The user's location
     * - The weather condition at the user's location, and
     * - The mood mapped to the weather condition
     * 
     * Returns an array of songs, each song will contain this info:
     * - the song's title
     * - the song's artist
     * - the url to the picture of the song's album
     * - the youtube's id of the song at youtube.com
     * 
     * @return array
     */
    public function getSongsList()
    {
        $this->_mapWeatherConditionToTag();
        
        $topSongsByTag = $this->_getSongListByTag();
        shuffle($topSongsByTag);
        
        $finalSongsList = array();
        $youtubeSearchService = new YHack_YahooYql_YoutubeSearch();
        foreach($topSongsByTag as $song) {
            $youtubeQuery = (string) $song['track'] . ' ' . (string) $song['artist'];
            $youtubeSearchService->setQuery($youtubeQuery);
            $youtubeJsonResponse = $youtubeSearchService->run();
            $resultArray = Zend_Json::decode($youtubeJsonResponse);
            if ($resultArray['query']['results'] == null) {
                continue;
            }
            $videoUrl = $resultArray['query']['results']['video']['url'];
            $httpParts = parse_url($videoUrl);
            $httpParams = array();
            parse_str($httpParts['query'], $httpParams);
            $song['youtubeId'] = $httpParams['v'];

			foreach ($song as $key => $value) {
				$song[$key] = (string) $value;
			}

            $finalSongsList[] = $song;
        }
        
        return $finalSongsList;
    }
    
	/**
	 * Get the weather condition
	 *
	 * @access public
	 * @return numeric
	 */
	public function getWeatherCondition()
	{
		return $this->_weatherCondition;	
	}
	
	/**
	 * Set the weather condition
	 *
	 * @param numeric $weatherCondition
	 * @access public
	 * @return YHack_Orchestrator
	 */
	public function setWeatherCondition($weatherCondition)
	{
		$this->_weatherCondition = $weatherCondition;
		return $this;
	}

    /**
     * Maps the weather condition of the user's city to a tag (mood)
     * 
     * @todo stop mocking up!!!
     * @return void
     */
    protected function _mapWeatherConditionToTag()
    {
        return $this->_mapWeatherConditionToTagMockup();
    }
    
    /**
     * Gets a songs list from Last.fm using the specified tag
     * 
     * @return array
     */
    protected function _getSongListByTag()
    {
        $lastFmSongsByTag = new YHack_LastFmAPI_TagTopTracks(array('tag' => $this->_tag));
        $songsList = $lastFmSongsByTag->topTracks();
        
        return $songsList;
    }
    
    /**
     * Mocking up the tag, sets the tag to "sad"
     * 
     * @return void
     */
    private function _mapWeatherConditionToTagMockup()
    {
        $this->_tag = 'sad';
    }
    
}
