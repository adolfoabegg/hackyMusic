<?php
/**
 * Fetch weather information from Yahoo!
 *
 *
 * @category YHack
 * @package YahooAPI
 * @copyright Codefathers team
 */

class YHack_LastFmAPI_TagTopTracks extends YHack_LastFmAPI_Abstract
{
    protected $_rpcMethod = 'tag.gettoptracks';
    
    /**
     * The tag to get the top tracks with.
     * 
     * @var string
     */
    protected $_tag = null;

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
     * Sets the tag to get the top tracks with.
     * 
     * @param string $tag 
     */
    public function setTag($tag)
    {
        $this->_tag = $tag;
    }
    
    /**
     * Gets the top tracks for the given tag.
     * Returns an array with the top tracks
     * 
     * @param string $tag
     * @return array 
     */
    public function topTracks($tag = null)
    {
        if ($tag != null) {
            $this->_tag = $tag;
        }
        
        $config = Zend_Registry::get('AppConfig');
        
        $parameters = array(
            'tag'   =>  $this->_tag,
            'limit' =>  $config->lastfm->tracks->limit * 4,//we don't want the same songs all the time
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
        foreach($simpleXmlObject->toptracks->track as $track) {
            $tracks[] = array(
                'track'     => $track->name,
                'artist'    => $track->artist->name,
                'albumImage'=> $track->image[0],//smallest image
            );
        }
        
        //we don't want the same songs all the time
        shuffle($tracks);
        $finalTracks = array_slice($tracks, 1, $config->lastfm->tracks->limit, true);
        
        return $finalTracks;       
    }

    
}
