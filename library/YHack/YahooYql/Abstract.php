<?php
/**
 * A simple class that wraps Yahoo!'s Yql
 * Use it as the base class to query Yql's datasets
 *
 * @category YHack
 * @package YahooYql
 * @copyright Codefathers team
 */

abstract class YHack_YahooYql_Abstract 
{
    const YQL_ENDPOINT = 'http://query.yahooapis.com/v1/public/yql';
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
     * The last resultset.
     * 
     * @var string
     */
    protected $_result = null;
    
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
     * Returns the result of the last query
     * @return string
     */
    public function getResult()
    {
        return $this->_result;
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
        $client = new Zend_Http_Client(self::YQL_ENDPOINT);
        $columns = implode(', ', $this->_columns);
        $filters = '';
        if (count($filters) > 0) {
            $filters = ' WHERE ' . implode($this->_filterType . ' ', $this->_filters);
        }
        $parameters = array(
            'q' => 'SELECT ' . implode(', ', $this->_columns) . ' FROM ' . $this->_dataset . $filters . ' LIMIT ' . $this->_limit,
            'format' => $this->_format,
            'env' => 'store://datatables.org/alltableswithkeys',
        );
        $client->setParameterGet($parameters);
        $this->_result = $client->request('GET')->getBody();
        return $this->_result;
    }
}