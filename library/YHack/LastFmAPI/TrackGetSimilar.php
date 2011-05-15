<?php
/**
 * Fetch weather information from Yahoo!
 *
 *
 * @category YHack
 * @package YahooAPI
 * @copyright Codefathers team
 */

class YHack_LastFmAPI_TrackGetSimilar extends YHack_LastFmAPI_Abstract
{
    protected $_rpcMethod = 'track.getsimilar';
    
    /**
     * The track title
     * 
     * @var string
     */
    protected $_track = null;
    
    /**
     * The artist's name
     * @var string
     */
    protected $_artist = null;

    /**
     * Constructor
     * 
     * @param array|Zend_Config $config 
     * @access public
     */
    public function __construct($config)
    {
        $this->setOptions($config);
    }
    
    /**
     * Sets the track to retrieve its similar songs.
     * 
     * @param string $track
     */
    public function setTrack($track)
    {
        $this->_track = $track;
    }
    
    /**
     * Sets the artist who plays the track.
     * 
     * @param string $artist
     */
    public function setArtist($artist)
    {
        $this->_artist = $artist;
    }
    
    /**
     * Gets the top tracks for the given tag.
     * Returns an array with the top tracks.
     * The result is limited by the value of lastfm.similartracks.limit in the application.ini file
     * 
     * @param string $tag
     * @return array 
     */
    public function similarTracks($track = null, $artist = null)
    {
        if ($track != null && $track != null) {
            $this->_track = $track;
            $this->_artist = $artist;
        }
        
        $config = Zend_Registry::get('AppConfig');
        
        $parameters = array(
            'track'   =>  $this->_track,
            'artist'  =>  $this->_artist,
            'limit'   => $config->lastfm->tracks->limit,
        );
        
        /* @var $simpleXmlObject SimpleXMLElement */
        $simpleXmlObject = '';
        
        try {
            $simpleXmlObject = $this->_rpc($parameters);
        } catch (Zend_Http_Client_Exception $e) {
            $this->_helper->json(Zend_Json::encode(array('HttpException' => $e->getMessage())));
        } catch (Exception $e) {
            $this->_helper->json(Zend_Json::encode(array('AppException' => $e->getMessage())));
        }
        
        $tracks = array();
        foreach($simpleXmlObject->similartracks->track as $track) {
            $tracks[] = array(
                'track'     => $track->name,
                'artist'    => $track->artist->name,
                'albumImage'=> $track->image[0],//smallest image
            );
        }
        
        return $tracks;       
    }

    
}
