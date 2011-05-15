<?php
/**
 * This class get some data from Youtube.com
 *
 * @category YHack
 * @package YahooYql
 * @copyright Codefathers team
 */

class YHack_YahooYql_YoutubeSearch extends YHack_YahooYql_Abstract
{
    /**
     * The yql's dataset for Youtube's search service.
     */
    protected $_dataset = 'youtube.search';
    
    /**
     * The list of column that is going to be fetched from the dataset
     * 
     * @var string 
     */
    protected $_columns = array('url');
        
    /**
     * Sets the song info
     * 
     * @param string $song 
     * @return void
     */
    public function setQuery($query)
    {
        $this->_filters = array("query = \"{$query}\"");
    }
    
    /**
     * The constructor
     * 
     * @param array $config 
     */
    public function __constructor($config = array())
    {
        if (!empty($config)) {
            $this->setOptions($config);
        }
        $this->_dataset = self::DATASET;
    }
    
}