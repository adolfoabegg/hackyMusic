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
 * Parser used to parse a folder with controllers and extract the ACL information
 * from the files via phpdoc comments
 *
 *
 * @category ZFAdminCli
 * @package ZFAdminCli_Acl
 * @subpackage Exception
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdminCli_Acl_Parser 
{
    /**
     * Path to the controllers
     * 
     * @var string
     * @access protected
     */
    protected $_path = null;

    /**
     * Create the parser
     * 
     * @param mixed $config
     */
    public function __construct($config)
    {
        $this->setOptions($config);
    }

    /**
     * Set the options for the parser
     * 
     * @param mixed $config
     * @access public
     * @return ZFAdminCli_Acl_Parser
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
     * Set the path to the controller's directory
     * 
     * @param mixed $path 
     * @access public
     * @return ZFAdminCli_Acl_Parser
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    /**
     * Get the path to the controller's directory
     * 
     * @access public
     * @return string
     */
    public function getPath()
    {
        if (null === $this->_path) {
            throw new Zend_Exception('Path not specified!');
        }

        return $this->_path;
    }

    /**
     * Parse the files and retrieve the ACL data
     * 
     * @access public
     * @return array
     */
    public function parse()
    {
        $path      = $this->getPath();
        $iterator  = new DirectoryIterator($path);

        $resources = array();

        foreach ($iterator as $file) {
            if ($file->isDot() || substr($file->getFilename(), -4) != '.php') {
                continue;
            }

            require_once $file->getPathname();

            $reflectionFile = new Zend_Reflection_File($file->getPathname());
            foreach($reflectionFile->getClasses() as $class) {
                if (!$class->getDocblock()->hasTag('zfa_resource')) {
                    throw new ZFAdminCli_Acl_Exception_ResourceNotSpecified(
                        sprintf(
                            'Class %s does not have a resource name! Specify one using @zfa_resource tag.',
                            $class->getName()
                        )
                    );
                }

                $classInfo = array();
                
                $resource = $class->getDocblock()->getTag('zfa_resource')->getDescription();
                if (!$resource) {
                    throw new ZFAdminCli_Acl_Exception_ResourceNotSpecified(
                        sprintf(
                            'Class %s does not have a resource name! Specify one using @zfa_resource tag.',
                            $class->getName()
                        )
                    );
                }

				$parts = preg_split('/[\s]+/', $resource);
				if (count($parts) > 1) {
					$resource    = $parts[0];
					unset($parts[0]);
					$description = implode(' ', $parts);
				} else {
					$description = '';
				}

                $classInfo['resource']    = $resource;
                $classInfo['description'] = $description;
                $classInfo['name']        = $class->getName();
                $classInfo['methods']     = array();

                foreach ($class->getMethods() as $method) {
                    if (substr($method->getName(), -6) == 'Action') {
                        if (!$method->getDocblock()->hasTag('zfa_privilege')) {
                            throw new ZFAdminCli_Acl_Exception_PrivilegeNotSpecified(
                                sprintf(
                                    'Privilege missing for action %s in class %s! Specify it using @zfa_privilege tag',
                                    $method->getName(),
                                    $class->getName()
                                )
                            );
                        }

                        $privilege = $method->getDocblock()->getTag('zfa_privilege')->getDescription();
                        if (!$privilege) {
                            throw new ZFAdminCli_Acl_Exception_PrivilegeNotSpecified(
                                sprintf(
                                    'Privilege missing for action %s in class %s! Specify it using @zfa_privilege tag',
                                    $method->getName(),
                                    $class->getName()
                                )
                            );
                        }

						$parts = preg_split('/[\s]+/', $privilege);
						if (count($parts) > 1) {
							$privilege   = $parts[0];
							unset($parts[0]);
							$description = implode(' ', $parts);
						} else {
							$description = '';
						}


						$classInfo['methods'] []= array(
                            'name'        => $method->getName(),
                            'privilege'   => $privilege,
                            'description' => $description,
                        );
                    }
                }

                $resources []= $classInfo;
            }
        }

        return $resources;
    }
}

