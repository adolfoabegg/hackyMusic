<?php
/**
 * The Music controller, gets a list of tracks by tag or, similar tracks for a song.
 *
 * @category application
 * @package controllers
 * @copyright Codefathers team
 */

class MusicController extends YHack_Controller
{
    /**
     * Controller's entry point
     *
     * @return void
     */
    public function indexAction()
    {
        
    }
    
    /**
     * This action will return the list of songs by mood in JSON format.
     * You must pass in the tag as a parameter.
     * 
     * @return void
     */
    public function getSongsByTagAction()
    {
        $tag = $this->_getParam('tag', '');
        
        if (empty($tag)) {
            $this->_helper->json(Zend_Json::encode(array('AppException' => 'You must specify a tag!')));
        }
        
        $lastFmTopTracks = new YHack_LastFmAPI_TagTopTracks(array('tag' =>  $tag));
        $topTracks = $lastFmTopTracks->topTracks();
        
        $this->_helper->json(Zend_Json::encode($topTracks));
    }
    
    /**
     * Return the list of similar songs for a given song+artist in JSON format.
     * 
     * @return void
     */
    public function getSimilarSongsAction()
    {
        $track = $this->_getParam('song', '');
        $artist = $this->_getParam('artist', '');
        
        if (empty($track) || empty($artist)) {
            $this->_helper->json(Zend_Json::encode(array('AppException' => 'You must specify a song its artist!')));
        }
        
        $config = array(
            'track' => $track,
            'artist'=> $artist,
        );
        
        $lastFmSimilarTracks = new YHack_LastFmAPI_TrackGetSimilar($config);
        $similarSongs = $lastFmSimilarTracks->similarTracks();
        
        $this->_helper->json(Zend_Json::encode($similarSongs));
    }
    
    
}

