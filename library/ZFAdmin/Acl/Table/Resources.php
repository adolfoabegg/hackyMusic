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
 * Table managing the ACL resources
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Acl
 * @subpackage Table
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdmin_Acl_Table_Resources extends ZFAdmin_Table_TableAbstract
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = 'zfadmin_resources';

    /**
     * Holds the associated model class
     *
     * @var string
     * @access protected
     */
    protected $_rowClass = 'ZFAdmin_Acl_Row_Resource'; 

    /**
     * Holds the table's reference map
     * 
     * @var array
     * @access protected
     */
    protected $_referenceMap = array(
        'Privilege' => array(
            'columns'       => 'id',
            'refTableClass' => 'ZFAdmin_Acl_Table_Privileges',
            'refColumns'    => 'privilege_id',
        ),
        'Acl' => array(
            'columns'       => 'id',
            'refTableClass' => 'ZFAdmin_Acl_Table_Acls',
            'refColumns'    => 'resource_id',
        ),  
    );

    /**
	 * Force the table's name
     *
     * @param bool $withFromPart 
     * @access public
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = Zend_Db_Table_Abstract::SELECT_WITHOUT_FROM_PART)
    {
        $select = parent::select($withFromPart);
        $select->from($this->_name)
			   ->setIntegrityCheck(false);

		return $select;
	}
}

