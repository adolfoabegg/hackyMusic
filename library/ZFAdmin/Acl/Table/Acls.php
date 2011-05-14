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
 * Table managing the ACL relationship between groups, resources and privileges.
 * It uses the whitelisting approach.
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

class ZFAdmin_Acl_Table_Acls extends ZFAdmin_Table_TableAbstract
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
    protected $_name = 'zfadmin_acls';

    /**
     * Holds the associated model class
     *
     * @var string
     * @access protected
     */
    protected $_rowClass = 'ZFAdmin_Acl_Row_Acl'; 

    /**
     * Holds the table's reference map
     * 
     * @var array
     * @access protected
     */
    protected $_referenceMap = array(
        'Resource' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'ZFAdmin_Acl_Table_Resources',
            'refColumns'    => 'id',
        ),
        'Privilege' => array(
            'columns'       => 'privilege_id',
            'refTableClass' => 'ZFAdmin_Acl_Table_Privileges',
            'refColumns'    => 'id',
        ),
        'Group' => array(
            'columns'       => 'group_id',
            'refTableClass' => 'ZFAdmin_Acl_Table_Groups',
            'refColumns'    => 'id',
        ),
    );

    /**
     * Init hook
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();

        if (!Zend_Registry::isRegistered('AppConfig')) {
            return;
        }

        $config = Zend_Registry::get('AppConfig');
        if (empty($config->zfadmin->acl->groupsTable)) {
            return;
        }

        $table = new $config->zfadmin->acl->{'groupsTable'}();

        if (!$table instanceof ZFAdmin_Table_TableAbstract) {
            throw new ZFAdmin_Acl_Exception(
                sprintf(
                    'Table %s must extend from ZFAdmin_Table_TableAbstract!',
                    $config->zfadmin->acl->groupsTable
                )
            );
        }

        if (!in_array('name', $table->info(Zend_Db_Table_Abstract::COLS))) {
            throw new ZFAdmin_Acl_Exception(
                sprintf(
                    'Table %s must have a "name" column that can be displayed to the user!',
                    $config->zfadmin->acl->groupsTable
                )
            );
        }

        $this->_referenceMap['Group']['refTableClass'] = $config->zfadmin->acl->groupsTable;
    }

    /**
     * Save an array of resources and privileges to the database:
     *
     * - if a new controller or action is found, an insert is performed
     * - if an existing controller or action is found, an update to the new resource / privilege is performed
     * - if an existing controller or action is not found, it will be deleted with all related data
     *
     * @param array $resources 
     * @access public
     * @return void
     */
    public function updateResourcesAndPrivileges(array $data)
    {
        // used tables
        $mappingsTable   = new ZFAdmin_Acl_Table_Mappings();
        $resourcesTable  = new ZFAdmin_Acl_Table_Resources();
        $privilegesTable = new ZFAdmin_Acl_Table_Privileges();

        $inflector = new Zend_Filter_Inflector(':string');
        $inflector->setRules(
            array(':string' => array('Word_CamelCaseToDash', 'StringToLower'))
        );

        $validResources     = array();
        $validPrivileges    = array();
        $validMappings      = array();

        foreach ($data as $resource) {
            $action     = null;
            $controller = null;

            $controller = $inflector->filter(
                array(
                    'string' => substr($resource['name'], 0, strlen($resource['name']) - 10)
                )
            );

            // do the mappings: controller to resources and actions to privileges for that resource
            $mapping = $mappingsTable->fetchOneByControllerAndAction($controller, null);
            if (!$mapping) {
                $mapping = $mappingsTable->createRow(
                    array(
                        'controller' => $controller,
                        'action'     => null,
                    )
                );
            }

            $mapping->resource = $resource['resource'];
            $mapping->save();
            
            $validMappings []= $mapping->id;

            foreach ($resource['methods'] as $method) {
                $action = $inflector->filter(
                    array(
                        'string' => substr($method['name'], 0, strlen($method['name']) - 6)
                    )
                );

                $mapping = $mappingsTable->fetchOneByControllerAndAction($controller, $action);
                if (!$mapping) {
                    $mapping = $mappingsTable->createRow(
                        array(
                            'controller' => $controller,
                            'action'     => $action,
                        )
                    );
                }

                $mapping->resource  = $resource['resource'];
                $mapping->privilege = $method['privilege'];
                $mapping->save();

                $validMappings []= $mapping->id;
            }

            // update the resources and privileges tables
            $resourceRow = $resourcesTable->fetchOneByName($resource['resource']);
            if (!$resourceRow) {
                $resourceRow = $resourcesTable->createRow(
                    array(
                        'name'        => $resource['resource'],
                        'description' => $resource['description'],
                    )
                );
            } else {
            	$resourceRow->description = $resource['description'];
            }

			$resourceRow->save();

            $validResources []= $resourceRow->id;

            foreach ($resource['methods'] as $method) {
				$privilege	  = $method['privilege'];
                $privilegeRow = $privilegesTable->fetchOneByResourceIdAndName($resourceRow->id, $privilege);
                if (!$privilegeRow) {
                    $privilegeRow = $privilegesTable->createRow(
                        array(
                            'resource_id' => $resourceRow->id,
                            'name'        => $privilege,
							'description' => $method['description'],
                        )
                    );
                } else {
                	$privilegeRow->description = $method['description'];
                }

				$privilegeRow->save();
                $validPrivileges []= $privilegeRow->id;
            }
        }


        // clean up old data
        $resourcesTable->delete('id NOT IN (' . implode(', ', $validResources) . ')');
        $privilegesTable->delete('id NOT IN (' . implode(', ', $validPrivileges) . ')');
        $mappingsTable->delete('id NOT IN (' . implode(', ', $validMappings) . ')');

        // delete existing ACLs
        $this->delete('resource_id NOT IN (' . implode(', ', $validResources) . ')');
        $this->delete('privilege_id NOT IN (' . implode(', ', $validPrivileges) . ')');
    }

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
