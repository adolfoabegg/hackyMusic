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
 * Singleton object that manages the ACL. It generates a Zend_Acl object 
 * according to the information stored in the database that can be queried directly
 * for user permissions or passed to other components such as Zend_Navigate
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Acl
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdmin_Acl_Manager 
{
    /**
     * Singleton object
     *  
     * @static
     * @var ZFAdmin_Acl_Manager
     */
    protected static $_instance = null;

    /**
     * Zend_Acl encapsulating the ACL - this will be regenerated on every
     * request unless caching is used
     * 
     * @var Zend_Acl
     * @access protected
     */
    protected $_acl = null;

    /**
     * Object used for caching the ACL
     * 
     * @var Zend_Cache
     * @access protected
     */
    protected $_cache = null;

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
     * @return ZFAdmin_Acl_Manager
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new ZFAdmin_Acl_Manager();
        }
    
        return self::$_instance;
    }

    /**
     * Get the cached Zend_Acl object or regenerate it
     * 
     * @access public
     * @return Zend_Acl
     */
    public function getAcl()
    {
        if (null === $this->_acl) {
            if ($this->_cache !== null) {
                if (isset($this->_cache->ZFAdmin_Acl_Manager)) {
                    $this->_acl = $this->_cache->ZFAdmin_Acl_Manager;
                } else {
                    $this->_acl = $this->_generateAcl();
                    $this->_cache->ZFAdmin_Acl_Manager = $this->_acl;
                }
            } else {
                $this->_acl = $this->_generateAcl();
            }
        }

        return $this->_acl;
    }

    /**
     * Generate the Zend_Acl object using info from the database
     * 
     * @access protected
     * @return Zend_Acl
     */
    protected function _generateAcl()
    {
        // recreate the ACL
        $acl = new Zend_Acl();

        // whitelisting approach
        $acl->deny();

        $config = null;

        if (Zend_Registry::isRegistered('AppConfig')) {
            $config = Zend_Registry::get('AppConfig');
        }

        // add the user created groups
        $groupsTable  = $this->_getGroupsTable();
        $groupsRowset = $groupsTable->fetchAllOrdered();

        foreach ($groupsRowset as $groupRow) {
            $parent = $groupRow->findParent()->current();
            if ($parent) {
                $acl->addRole($groupRow->name, $parent->name);
            } else {
                $acl->addRole($groupRow->name);
            }
        }

        if ($config) {
            // import the default groups from the ini file
            if (!empty($config->zfadmin->acl->defaultGroups)) {
                foreach ($config->zfadmin->acl->defaultGroups as $group) {
                    $acl->addRole($group);
                }
            }
        }

        // add the resources
		$resourcesTable = new ZFAdmin_Acl_Table_Resources();
		$resourcesRowset = $resourcesTable->fetchAll();
		foreach ($resourcesRowset as $resourceRow) {
			$acl->addResource($resourceRow->name);
		}

		// populate the acl
        $aclsTable = new ZFAdmin_Acl_Table_Acls();
        $aclsRowset = $aclsTable->fetchAllWithJoinedRows();

        foreach ($aclsRowset as $aclRow) {
            $acl->addResource($aclRow->zfadmin_resources_name);
            if ($aclRow->allow) {
                $acl->allow(
                    $aclRow->zfadmin_groups_name,
                    $aclRow->zfadmin_resources_name,
                    $aclRow->zfadmin_privileges_name
                );
            }
        }
        
        // load predefined acls from the config
        if ($config) {
            if (!empty($config->zfadmin->acl->defaultAccess->allow)) {
                foreach ($config->zfadmin->acl->defaultAccess->allow as $key => $permissions) {
                    if ($key == 'all') {
                        $group = null;
                    } else {
                        $group = $key;
                    }

                    if (is_string($permissions)) {
                        $permissions = array($permissions);
                    } else {
                        if (is_object($permissions)) {
                            $permissions = $permissions->toArray();
                        }
                    }

                    foreach ($permissions as $value) {
                        if (strpos($value, '.') !== false) {
                            list($resource, $privilege) = explode('.', $value);
                        } else {
                            $privilege = null;

                            if ($value == 'all') {
                                $resource = null;
                            } else {
                                $resource = $value;
                            }
                        }
                        
                        $acl->allow($group, $resource, $privilege);
                    }
                }
            }

            if (!empty($config->zfadmin->acl->defaultAccess->deny)) {
                foreach ($config->zfadmin->acl->defaultAccess->deny as $key => $permissions) {
                    if ($key == 'all') {
                        $group = null;
                    } else {
                        $group = $key;
                    }

                    if (is_string($permissions)) {
                        $permissions = array($permissions);
                    } else {
                        if (is_object($permissions)) {
                            $permissions = $permissions->toArray();
                        }
                    }

                    foreach ($permissions as $value) {
                        if (strpos($value, '.') !== false) {
                            list($resource, $privilege) = explode('.', $value);
                        } else {
                            $privilege = null;

                            if ($value == 'all') {
                                $resource = null;
                            } else {
                                $resource = $value;
                            }
                        }
                        
                        $acl->deny($group, $resource, $privilege);
                    }
                }
            }
        }

        return $acl;
    }

    /**
     * Convert an action name into a privilege and a controller into
     * a resource
     * 
     * @param string $controller
     * @param string $action 
     * @access public
     * @return array
     */
    public function getResourceAndPrivilege($controller, $action)
    {
        $aclMappingsTable = new ZFAdmin_Acl_Table_Mappings();
        $aclMappingRow = $aclMappingsTable->fetchOneByControllerAndAction($controller, $action);

        if (null === $aclMappingRow) {
            throw new ZFAdmin_Acl_Exception(
                sprintf(
                    'Controller %s and action %s are not registered in the database.',
                    $controller,
                    $action
                )
            );
        }

        return array('resource' => $aclMappingRow->resource, 'privilege' => $aclMappingRow->privilege);
    }
    
    /**
     * Parse any given url into a resource / privilege array
     * that can be used to query the ACL
     * 
     * @param mixed $url 
     * @access public
     * @return array
     */
    public function parseUrl($url)
    {
        $parts = explode($url, '/');
        $controller = $parts[0];

        if (isset($parts[1])) {
            $action = $parts[1];
        } else {
            $action = 'index';
        }


        return $this->getResourceAndPrivilege($controller, $action);
    }

    /**
     * Flushes the internal cache
     * 
     * @access public
     * @return ZFAdmin_Acl_Manager
     */
    public function flushCache()
    {
        if (null !== $this->_cache) {
            unset($this->_cache->ZFAdmin_Acl_Manager);
        }

        return $this;
    }

    /**
     * Set the Zend_Cache object used to cache the ACL
     *
     * @param Zend_Cache $cache
     * @access public
     * @return ZFAdmin_Acl_Manager
     */
    public function setCache(Zend_Cache $cache)
    {
        $this->_cache = $cache;
        return $this;
    }
    
    /**
     * Get the Zend_Cache object used to cache the ACL
     *
     * @access public
     * @return Zend_Cache
     */
    public function getCache()
    {
        return $this->_cache;    
    }

    /**
     * Remove the Zend_Cache object - the ACL will no longer be cached
     * 
     * @access public
     * @return ZFAdmin_Acl_Manager
     */
    public function removeCache()
    {
        $this->_cache = null;
        return $this;
    }

    /**
     * Retrieve the current configuration based on the 
     * 
     * @access protected
     * @return ZFAdmin_Table_TableAbstract
     */
    protected function _getGroupsTable()
    {
        if (!Zend_Registry::isRegistered('AppConfig')) {
            return new ZFAdmin_Acl_Table_Groups();
        }

        $config = Zend_Registry::get('AppConfig');
        if (empty($config->zfadmin->acl->groupsTable)) {
            return new ZFAdmin_Acl_Table_Groups();
        }

        $table = new $config->zfadmin->acl->{'groupsTable'}();

        return $table;
    }
}
