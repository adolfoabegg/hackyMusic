<?php
/**
 * A simple class that wraps Yahoo!'s Yql
 *
 * @category YHack
 * @package YahooYql
 * @copyright Codefathers team
 */

class YHack_YahooYql_Query 
{
    const FILTER_CONJUNCTION = 'AND';
    const FILTER_DISJUNCTION = 'OR';
    
    /**
     * The dataset to query
     * 
     * @var string
     */
    protected $_dataset = null;
    
    /**
     * The filters to apply to the query
     * 
     * @var array
     */
    protected $_filters = array();
    
    /**
     * The filter type.
     * 
     * @var string
     */
    protected $_filterType = self::FILTER_CONJUNCTION;
    
    /**
     * The columns to get from the dataset
     * 
     * @var array
     */
    protected $_columns = array('*');
   
    /**
     * The limit to apply to the query
     * 
     * @var integer
     */
    protected $_limit = 1;
    
    /**
     * The format of the results.
     * Possible values: json|xml
     * 
     * @var string
     */
    protected $_format = 'json';
    
    /**
     * Sets the data set to query
     * 
     * @param string $dataset 
     */
    public function setDataset($dataset)
    {
        $this->_dataset = $dataset;
    }
    
    /**
     * Sets the filter(s) for the query
     * 
     * @param array $filters 
     */
    public function setFilters(array $filters)
    {
        $this->_filters = $filters;
    }
    
    /**
     * Sets the column(s) to get from the dataset
     *  
     * @param array $columns 
     */
    public function setColumns(array $columns)
    {
        $this->_columns = $columns;
    }
    
    /**
     * Sets a limit for the query
     * 
     * @param integer $limit 
     */
    public function setLimit($limit)
    {
        $limit = (int)$limit;
        if ($limit > 0) {
            $this->_limit = $limit;
        }
    }
    
    /**
     * Sets a limit to the query
     * 
     * @param string $format 
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }
    
    /**
     * Sets the filter type.
     * Please use the constants of this class self::FILTER_CONJUNCTION or self::FILTER_DISJUNCTION
     * 
     * @param string $filterType 
     */
    public function setFilterType($filterType)
    {
        $this->_filterType = $filterType;
    }
    
    /**
     * Constructor
     * 
     * @param array|Zend_Config $config
     */
    public function __construct($config = array()) 
    {
        if (!empty($config)) {
            $this->setOptions($config);
        }
    }
    
    /**
     * Set options
     *
     * @param array|Zend_Config $config
     * @return YHack_YahooYql_Query
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
     * Runs the query and returns the results in the specified format.
     * 
     * @return string
     */
    public function run()
    {
        $client = new Zend_Rest_Client('http://query.yahooapis.com/v1/public/yql');
        $columns = implode(', ', $this->_columns);
        $filters = '';
        if (count($filters) > 0) {
            $filters = ' WHERE ' . implode($this->_filterType . ' ', $this->_filters);
        }
        $client->q('SELECT ' . $this->_dataset . ' FROM ' . $this->_dataset . $filters . ' LIMIT ' . $this->_limit);
        $client->format($this->_format);
        $result = $client->get();
        
        return $result;
    }
}