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
 * Abstract parent table for all other tables in the application.
 *
 * Adds a number of convenience features to Zend's implementation:
 *
 * 1. The "fetch*" magic methods:
 *    The class provides a number of magic methods that allow easier data retrival
 *    from the table. 
 *
 *    i) Formatting the data according to your needs:
 *       The first part of the magic method's name tells the table how the data should be formatted
 *       fetchOne or fetchRow - fetch the first row, returns a Zend_Db_Table_Row object
 *       fetchAll             - fetch all rows, returns a Zend_Db_Table_Rowset object
 *       fetchPaginator       - fetch all rows, returns a Zend_Paginator object
 *       fetchPairs           - fetch all rows as a paired array (primaryKey => displayColumn)
 *                              that can be used to populate a Zend_Form_Element_Select object;
 *                              if the select has only two columns, it will return the an array using the
 *                              first column as key and the second one as value
 *       fetchCount           - fetch number of rows returned by your query
 *
 *    ii) Joining related tables:
 *       The second part of the magic method's name indicates whether data from related tables should be added
 *       to the query. Do enable this feature just add "WithJoinedRows" after the part on formatting in the 
 *       method's name:
 *         fetchAllWithJoinedRows
 *         fetchPaginatorWithJoinedRows
 *
 *       Only tables in a "belongsTo" relation with the current table will be added to the query. To avoid name 
 *       collisions the columns from will be prefixed with the relation's alias (lowercased and underscored)
 *       from the _referenceMap array. For example, given the following reference map
 *       
 *       protected $_referenceMap = array(
 *           'CarParts' => array(
 *                'columns'       => 'car_part_id',
 *                'refTableClass' => 'CarPartsTable',
 *                'refColumns'    => 'id',
 *            ),
 *       );
 *
 *       the data from the CarPartsTable will be available in the result as car_parts__name, 
 *       car_parts__manufacturer_id and so on. Please refer to the documentation for more examples.
 *          
 *    iii) Selecting the data you want
 *       The are several methods of adding conditions to a query:
 *
 *       a) by using a predefined table method that returns a select object:
 *          public function selectActive() {
 *              // select the "active" records - an active record is any record that was
 *              // used in the last 30 days and is not marked as disabled:
 *              $select = $this->select()
 *                             ->where('disabled = 0')
 *                             ->where('last_accessed >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
 *              return $select;
 *          }
 *
 *         Now, the active rows can be used in the following manner using magic methods:
 *         $table->fetchAllActive(); // rowset with all the active rows
 *         $table->fetchPairsActive(); // array that can be used to populate Zend_Form_Element_Select elements
 *         $table->fetchPaginatorActive(); // Zend_Paginator object encapsulating the "active" records
 *         $table->fetchPaginatorWithJoinedRowsActive(); // Zend_Paginator encapsulating "active" records 
 *                                                       // and their related data
 *
 *       b) by specifing which columns should be included in the "where" part of the select object:
 *          This is achieved by using the keyword "By" followed by the column names written in camel case. The method
 *          should receive a list of parameters which will be used in the the query.
 *
 *          $table->fetchOneByUsernameAndPassword($username, $password);
 *          $table->fetchAllByType(TYPE_DEFAULT);
 *          
 * 2. Display column
 *    Each table can have a "display column" which will contain the data that's usually displayed to the user. This is
 *    required for automatization purposes. For example, for a Persons table, the displayColumn will be the "name" and so on.
 *
 * 3. Sorting
 *    Each select object can be sorted automatically by using the sortSelect() method which receives an array containing
 *    the sorting criteria (column name and direction). If the searching is done by a foreign key, the system will
 *    detect it, join the new table and apply its default sorting rules.
 *
 * @category ZFAdmin
 * @package ZFAdmin_Table
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

abstract class ZFAdmin_Table_TableAbstract extends Zend_Db_Table
{
    /**
     * Regular expression used to parse magic methods
     */
    const MAGIC_REGEXP = '/^fetch(One|Row|All|Paginator|Count|Pairs)(WithJoinedRows|)(By|)(.+?|)$/';

    /**
     * Column which will be displayed to the user
     * 
     * @var string
     * @access protected
     */
    protected $_displayColumn = null;

    /**
     * Number of items displayed per paginator page for the current
     * table
     * 
     * @var int
     * @access protected
     */
    protected $_itemsPerPage = null;

    /**
     * Global number of items per paginator page, serves as default
     * 
     * @static
     * @var int
     * @access protected
     */
    protected static $_globalItemsPerPage = 100;

    /**
     * Array of columns on which the system should perform a loose
     * search using "LIKE" instead of "="
     * 
     * @var array
     * @access protected
     */
    protected $_looseSearchColumns = array();

    /**
     * Default column to be used for sorting
     * 
     * @var string
     * @access protected
     */
    protected $_sortingColumn = null;

    /**
     * Default sorting order
     * 
     * @var string
     * @access protected
     */
    protected $_defaultSortingOrder = 'ASC';

    /**
     * Allow all instance methods to be called statically by dinamically creating
     * an instance of the table and call the methods on it
     * 
     * @param string $method 
     * @param array $args 
     * @static
     * @access public
     * @return mixed
     */
    public static function __callStatic($method, array $args)
    {
        if (preg_match(self::MAGIC_REGEXP, $method)) {
            $instance = new static();

            return call_user_func_array(
                array($instance, $method),
                $args
            );
        }
        
        throw new Zend_Db_Table_Exception('Unrecognized static method `' . $method . '()`');
    }

    /**
     * __call() magic method, please refer to the class' documentation 
     * to see the available methods
     *
     * @param string $name 
     * @param array $arguments 
     * @access public
     * @return mixed
     */
    public function __call($name, array $arguments = array())
    {
        $result = $this->_parseMagicMethods($name, $arguments);

        switch ($result['method']) {
            case 'one':
                // fetch one object (returns Zend_Db_Table_Row)
                return $this->fetchRow($result['select']);
                break;
            case 'all':
                // fetch all objects (returns Zend_Db_Table_Rowset)
                return $this->fetchAll($result['select']);
                break;
            case 'paginator':
                // fetch paginated results (returns Zend_Paginator)
                $paginator = Zend_Paginator::factory($result['select']);
                $paginator->setItemCountPerPage($this->getItemsPerPage());

                return $paginator;
                break;
            case 'pairs':
                return $this->_fetchPairs($result['select']);
                break;
            case 'count':
                // fetch pairs - array('primaryKey' => '$displayColumn')
                $select = $result['select'];
                if (!$select->getPart(Zend_Db_Select::FROM)) {
                    $select->from($this->_name);
                }

                $select->reset(Zend_Db_Select::COLUMNS)
                       ->columns(array('COUNT(*)'));

                return $this->_db->fetchOne($select);
                break;
        }
    }

    /**
     * Parse the name of a magic method and attempt to detect 
     * 
     * @param string $name 
     * @param array $arguments 
     * @access protected
     * @return array
     */
    protected function _parseMagicMethods($name, array $arguments = array())
    {
        $parts = array();

        if (!preg_match(self::MAGIC_REGEXP, $name, $parts)) {    
            require_once 'ZFAdmin/Table/Exception/NoMatch.php';
            throw new ZFAdmin_Table_Exception_NoMatch(
                sprintf(
                    'Method %s not found in class %s.',
                    $name,
                    get_class($this)
                )
            );
        }

        // validate the data
        if (($parts[1] == 'Count' || $parts[1] == 'Pairs') && $parts[2]) {
            require_once 'ZFAdmin/Table/Exception/InvalidFormat.php';
            throw new ZFAdmin_Table_Exception_InvalidFormat(
                '"Count" or "Pairs" call cannot be done with joined rows.'
            );
        }

        if ($parts[3]) {
            if (!$parts[4]) {
                require_once 'ZFAdmin/Table/Exception/InvalidFormat.php';
                throw new ZFAdmin_Table_Exception_InvalidFormat(
                    'You must specify at least one column to run a fetchBy query.'
                );
            }

            $select = $this->select();
            $columns = explode('And', $parts[4]);
            $tableColumns = $this->info(Zend_Db_Table::COLS);

            $inflector = new Zend_Filter_Inflector(':string');
            $inflector->setRules(
                array(':string' => array('Word_CamelCaseToUnderscore', 'StringToLower'))
            );

            $index = 0;
            foreach ($columns as $column) {
                $inflectedColumn = $inflector->filter(
                    array('string' => $column)
                );

                if (!in_array($inflectedColumn, $tableColumns)) {
                    require_once 'ZFAdmin/Table/Exception/InvalidFormat.php';
                    throw new ZFAdmin_Table_Exception_InvalidFormat(
                        sprintf(
                            'Column "%s" not found in table "%s".',
                            $inflectedColumn,
                            get_class($this)
                        )
                    );
                }

                if (null === $arguments[$index]) {
                    $select->where($this->_db->quoteIdentifier($inflectedColumn) . ' IS NULL');
                } else {
                    $select->where($this->_db->quoteIdentifier($inflectedColumn) . ' = ?', $arguments[$index]);
                }

                ++$index;
            }
        } else {
            if ($parts[4]) {
                $selectMethod = 'select' . $parts[4];

                // do we have a select* method?
                if (!method_exists($this, $selectMethod)) {
                    require_once 'ZFAdmin/Table/Exception/MethodNotFound.php';
                    throw new ZFAdmin_Table_Exception_MethodNotFound(
                        sprintf(
                            'Method "%s" does not exist in class %s.',
                            $selectMethod,
                            get_class($this)
                        )
                    );
                }

                // get the Zend_Db_Table_Select object
                $select = call_user_func_array(array($this, $selectMethod), $arguments);
            } else {
                $select = $this->select();
            }
        }

        if ($parts[2]) {
            // join with related tables - voodoo
            $inflector = new Zend_Filter_Inflector(':string'); 
            $inflector->addRules(array(':string' => array('Word_CamelCaseToUnderscore', 'StringToLower')));

            foreach ($this->_referenceMap as $key => $ref) {
                if ($ref['columns'] == 'id') {
                    // only join when the relation is "belongsTo"
                    continue;
                }
                $table = new $ref['refTableClass']();
                $tableName = $table->getName();
            
                $alias = $inflector->filter(array('string' => $key));

                $joinedData = array();
                foreach ($table->info(Zend_Db_Table_Abstract::COLS) as $col) {
                    $joinedData []= $alias . '.' . $col . ' AS ' . $alias . '__' . $col;
                }

                try {
                    $joinedData []= $alias . '.' . $table->getNameColumn() . ' AS ' . $alias . '__display_column';
                }
                catch(Zend_Exception $ze) {
                    // the table doesn't have a display column, just ignore it
                }

                $select->joinLeft(
                    array($alias => $tableName), 
                    $this->_name . '.' . $ref['columns'] . ' = ' . $alias . '.' . $ref['refColumns'],
                    $joinedData
                );
            }
        }

        if ($parts[1] == 'Row') {
            $parts[1] = 'One';
        }

        $result = array('method' => strtolower($parts[1]),
                        'select' => $select);

        return $result;
    }


    /**
     * Fetch all rows as a paired array according to the select in a (primaryKey => displayColumn)
     * format
     * 
     * @param Zend_Db_Table_Select $select 
     * @access protected
     * @return array
     */
    protected function _fetchPairs(Zend_Db_Table_Select $select)
    {
        if (!$select->getPart(Zend_Db_Table_Select::FROM)) {
            $select->from($this->_name);
        }

        $columns = $select->getPart(Zend_Db_Table_Select::COLUMNS);
        if (count($columns) != 2) {
            $select->reset(Zend_Db_Select::COLUMNS)
                   ->columns(array('id', $this->getDisplayColumn()));
        }

        return $this->_db->fetchPairs($select);
    }

    /**
     * Search the current table according to the criteria
     * 
     * @param array $criteria 
     * @param array $sorting
     * @access public
     * @return Zend_Db_Table_Select
     */
    public function selectSearchResults(array $criteria = array(), array $sorting = array())
    {
        $cols = $this->info(Zend_Db_Table_Abstract::COLS);

        $select = $this->select()
                       ->setIntegrityCheck(false);


        if (!$select->getPart(Zend_Db_Table_Select::FROM)) {
            $select->from($this->_name);
        }

        foreach ($criteria as $key => $value) {
            if (in_array($key, $cols)) {
                // is this a strictSearchColumns?
                if (!in_array($key, $this->_looseSearchColumns)) {
                    // use = as a strict search
                    $select->where($this->getName() . '.' . $key . ' = ?', $value);
                } else {
                    // use LIKE
                    $select->where($this->getName() . '.' . $key . ' LIKE ?', $value);
                }
            }
        }


        return $this->sortSelect($select, $sorting);
    }

    /**
     * Sort the $select object according to the sorting criteria. 
     * The array should follow the pattern:
     * array(
     *  'column'    => 'column_name_to_sort_on',
     *  'direction' => 'ASC_or_DESC',
     * )
     *
     * @param Zend_Db_Table_Select $select 
     * @param array $sorting 
     * @access public
     * @return Zend_Db_Table_Select
     */
    public function sortSelect(Zend_Db_Table_Select $select, array $sorting = array())
    {
        $cols = $this->info(Zend_Db_Table_Abstract::COLS);

        try {
            // sorting the table
            if (empty($sorting)) {
                $sorting = array(
                    'column'    => $this->getSortingColumn(), 
                    'direction' => $this->getDefaultSortingOrder(),
                );
            } else {
                if (!in_array($sorting['column'], $cols)) {
                    // invalid sorting data provided (non-existent column)
                    // check if we're sorting on an joined table's displayColumn
                    if (substr($sorting['column'], -16) == '__display_column') {
                        $key = substr($sorting['column'], 0, -16);

                        $inflector = new Zend_Filter_Inflector(':string'); 
                        $inflector->addRules(array(':string' => array('Word_UnderscoreToCamelCase')));

                        $key = ucfirst($inflector->filter(array('string' => $key)));
                        $sorting['column'] = $this->_referenceMap[$key]['columns'];
                    } else {
                        $sorting = array(
                            'column'    => $this->getSortingColumn(), 
                            'direction' => $this->getDefaultSortingOrder(),
                        );
                    }
                }
            }
        }
        catch(Zend_Exception $ze) {
            $sorting = array();
        }

        
        if (!empty($sorting)) {
            // sorting by an external reference?
            if (substr($sorting['column'], -3) == '_id') {
                $tableClass = null;

                foreach ($this->_referenceMap as $ref) {
                    if ($ref['columns'] == $sorting['column']) {
                        $tableClass = $ref['refTableClass'];
                        break;
                    }
                }
                
                if ($tableClass) {
                    $table = new $tableClass();
                    try {
                        // perform a normal sort on the referenced table's name column
                        $sortingColumn = $table->getSortingColumn();

                        $select->joinLeft($table->getName(), 
                                          $this->_name . '.' . $sorting['column'] . ' = ' . $table->getName() . '.id',
                                          array())
                               ->order($table->getName() . '.' . $sortingColumn . ' ' . $sorting['direction']);

                    }
                    catch (Zend_Exception $ze) {
                        // the referenced table doesn't have a displayColumn , just order by the field
                        $select->order($this->getName() . '.' . $sorting['column'] . ' ' . $sorting['direction']);
                    }
                } else {
                    $select->order($this->getName() . '.' . $sorting['column'] . ' ' . $sorting['direction']);
                }
            } else {
                $select->order($this->getName() . '.' . $sorting['column'] . ' ' . $sorting['direction']);
            }
        }

        return $select;
    }

    /**
     * Set the number of items per page for this table
     * 
     * @param int $itemsPerPage 
     * @access public
     * @return Zend_Db_Table_Abstract
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->_itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * Get the number of items per paginator page for this table. If this table doesn't
     * have "itemsPerPage" defined, the default - all tables - value will be returned
     * 
     * @access public
     * @return int
     */
    public function getItemsPerPage()
    {
        if (null === $this->_itemsPerPage) {
            $itemsPerPage = ZFAdmin_Table_TableAbstract::getGlobalItemsPerPage();
        } else {
            $itemsPerPage = $this->_itemsPerPage;
        }

        return $itemsPerPage;
    }

    /**
     * Set a column whose's value will be displayed to the
     * user in order to visually identify a row from this table.
     *
     * Examples: 
     *  - "name" for a "persons" table
     *  - "title" for a "books" table
     *
     * @param string $displayColumn
     * @access public
     * @return Zend_Db_Table_Abstract
     */
    public function setDisplayColumn($displayColumn)
    {
        $this->_displayColumn = $displayColumn;
        return $this;
    }
    
    /**
     * Get the "display column" for this table
     *
     * @access public
     * @return int
     * @throws ZFAdmin_Table_Exception_NoDisplayColumn
     */
    public function getDisplayColumn()
    {
        if (null === $this->_displayColumn) {
            require_once 'ZFAdmin/Table/Exception/NoDisplayColumn.php';
            throw new ZFAdmin_Table_Exception_NoDisplayColumn(
                sprintf('Class %s does not have a display column', get_class($this))
            );
        }

        return $this->_displayColumn;    
    }

    /**
     * Get this table's reference map
     * 
     * @access public
     * @return array
     */
    public function getReferenceMap()
    {
        return $this->_referenceMap;
    }

    /**
     * Get this table's name
     * 
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the sorting column
     *
     * @param string $sortingColumn
     * @access public
     * @return ZFAdmin_Table_TableAbstract 
     */
    public function setSortingColumn($sortingColumn)
    {
        $this->_sortingColumn = $sortingColumn;

        return $this;
    }
    
    /**
     * Get the sorting column
     *
     * @access public
     * @return string
     */
    public function getSortingColumn()
    {
        if (null === $this->_sortingColumn) {
            return $this->getDisplayColumn();
        }
        return $this->_sortingColumn;    
    }
    
    /**
     * Set the default sorting order
     *
     * @param string $defaultSortingOrder
     * @access public
     * @return ZFAdmin_Table_TableAbstract
     */
    public function setDefaultSortingOrder($defaultSortingOrder)
    {
        $this->_defaultSortingOrder = $defaultSortingOrder;

        return $this;
    }
    
    /**
     * Get the default sorting order
     *
     * @access public
     * @return string
     */
    public function getDefaultSortingOrder()
    {
        return $this->_defaultSortingOrder;    
    }
    
    /**
     * Set the number of items per paginator page for all tables. This can be
     * overriden on a per table basis by calling setItemsPerPage()
     * 
     * @param int $itemsPerPage 
     * @static
     * @access public
     * @return void
     */
    public static function setGlobalItemsPerPage($itemsPerPage)
    {
        ZFAdmin_Table_TableAbstract::$_globalItemsPerPage = $itemsPerPage;
    }

    /**
     * Get the default number of items per paginator page
     * 
     * @static
     * @access public
     * @return int
     */
    public static function getGlobalItemsPerPage()
    {
        return ZFAdmin_Table_TableAbstract::$_globalItemsPerPage;
    }
}
