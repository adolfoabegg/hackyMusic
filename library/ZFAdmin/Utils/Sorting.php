<?php
/**
 * Copyright (c) 2011 Tudor Barbu <miau at motane dot lu>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Utility to extract the sorting data from the request and creating sorting
 * URLs
 *
 * @category ZFAdmin
 * @package ZFAdmin_Utils
 * @subpackage Exception
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdmin_Utils_Sorting 
{
    /**
     * Key to hold the column's name in the request
     * 
     * @var string
     * @access protected
     */
    protected $_columnIndex = 'column';

    /**
     * Key to hold the sorting direction in the request
     * 
     * @var string
     * @access protected
     */
    protected $_directionIndex = 'direction';

    /**
     * Request used to fetch the requested from - defaults to the current HTTP request
     * 
     * @var Zend_Controller_Request_Http
     * @access protected
     */
    protected $_request = null;

    /**
     * Default sorting criteria
     * 
     * @var mixed
     * @access protected
     */
    protected $_defaultCriteria = null;

    /**
     * Singleton object
     *  
     * @static
     * @var ZFAdmin_Utils_Sorting
     */
    protected static $_instance = null;

    /**
     * Class constructor - must not be called directly
     * 
     * @access protected
     */
    protected function __construct()
    {
        
    }
    
    /**
     * __clone() implementation that prevents singleton objects cloning
     * 
     * @access public 
     * @return void
     */
    public function __clone()
    {
        throw new Exception('Please do not clone singleton objects');    
    }
    
    /**
     * Returns a singleton instance of this class
     * 
     * @static
     * @access public
     * @return ZFAdmin_Utils_Sorting
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new ZFAdmin_Utils_Sorting();
        }
    
        return self::$_instance;
    }

    /**
     * Configure the sorting utility
     * 
     * @param Zend_Config|array $config 
     * @access public
     * @return ZFAdmin_Utils_Sorting
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
     * Extract the sorting criteria from the given request.
     * If no criteria is found, fall back to the default one
     * 
     * @access public
     * @return array
     */
    public function extractSortingCriteria()
    {
        $request = $this->getRequest();

        $result = array();
        $result['column'] = $request->getParam($this->_columnIndex);

        $direction = $request->getParam($this->_directionIndex);
        if ($direction != 'ASC' && $direction != 'DESC') {
            $direction = null;
        }

        $result['direction'] = $direction;

        if (null == $result['column'] || null == $result['direction']) {
            $result = $this->getDefaultCriteria();
        }

        return $result;
    }

    /**
     * Create a sorting URL
     * 
     * @param string $column
     * @param string $defaultSortingOrder 
     * @access public
     * @return string
     */
    public function createUrl($column, $defaultSortingOrder = 'ASC')
    {
        $criteria = array();
        $criteria[$this->getColumnIndex()] = $column;

        $sortingCriteria = $this->extractSortingCriteria();
        if ($sortingCriteria['column'] == $column) {
            // the last sort was on this column, reverse it
            if ($sortingCriteria['direction'] == 'ASC') {
                $criteria[$this->getDirectionIndex()] = 'DESC';
            } else {
                $criteria[$this->getDirectionIndex()] = 'ASC';
            }
        } else {
            $criteria[$this->getDirectionIndex()] = $defaultSortingOrder;
        }

        $criteria += $this->getRequest()->getQuery();
        unset($criteria['page']);
        $criteria = ZFAdmin_Utils_QueryEncoder::encodeQuery($criteria);

        return '?' . http_build_query($criteria);
    }

    /**
     * Set the column index
     *
     * @param string $columnIndex
     * @access public
     * @return ZFAdmin_Utils_Sorting
     */
    public function setColumnIndex($columnIndex)
    {
        $this->_columnIndex = $columnIndex;

        return $this;
    }
    
    /**
     * Get the column index
     *
     * @access public
     * @return string
     */
    public function getColumnIndex()
    {
        return $this->_columnIndex;    
    }

    /**
     * Set the direction index
     *
     * @param string $directionIndex
     * @access public
     * @return ZFAdmin_Utils_Sorting
     */
    public function setDirectionIndex($directionIndex)
    {
        $this->_directionIndex = $directionIndex;

        return $this;
    }
    
    /**
     * Get the direction index
     *
     * @access public
     * @return string
     */
    public function getDirectionIndex()
    {
        return $this->_directionIndex;    
    }

    /**
     * Set the request
     *
     * @param Zend_Controller_Request_Http $request
     * @access public
     * @return ZFAdmin_Utils_Sorting
     */
    public function setRequest($request)
    {
        $this->_request = $request;

        return $this;
    }
    
    /**
     * Get the request
     *
     * @access public
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            $front = Zend_Controller_Front::getInstance();
            $this->_request = $front->getRequest();
        }

        return $this->_request;
    }

    /**
     * Set the default criteria
     *
     * @param array $defaultCriteria
     * @access public
     * @return ZFAdmin_Utils_Sorting
     */
    public function setDefaultCriteria($defaultCriteria)
    {
        $this->_defaultCriteria = $defaultCriteria;

        return $this;
    }
    
    /**
     * Get the default criteria
     *
     * @access public
     * @return array
     */
    public function getDefaultCriteria()
    {
        return $this->_defaultCriteria;    
    }
}
