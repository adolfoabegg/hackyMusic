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
 * Parent form for ZFAdmin forms, it adds several features such as converting a
 * "normal" add / edit form to a "search" and adds an ant-CSRF token to improve
 * security
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Form
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

abstract class ZFAdmin_Form_FormAbstract extends Zend_Form
{
    /**
     * Flag indicating the form's state
     * 
     * @var bool
     * @access protected
     */
    protected $_isSearchForm = false;

    /**
     * List of elements to be removed when converting this
     * form to a "search form"
     * 
     * @var array
     * @access protected
     */
    protected $_removeFromSearch = array();

    /**
     * Init the form's basic logic
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        $config = Zend_Registry('config');

        if ($config->ZFAdmin->preventCsrfAttacks) {
            // add an anti-csrf token to all forms
            $this->addElement(
                'hash',
                'csrfhash',
                array(
                    'required' => true,
                    'salt'     => $config->ZFAdmin->csrfSalt,
                )
            );
        }
    }

    /**
     * Convert this form to a "search form"
     * 
     * @access public
     * @return ZFAdmin_Form_Abstract
     */
    public function convertToSearchForm()
    {
        if ($this->isSearchForm()) {
            return $this;
        }

        // rename the submit button
        $submit = $this->getElement('submit');
        if ($submit) {
            $submit->setLabel('Search');
        }

        // searches are always done via GET
        // as per HTTP specifications
        $this->setMethod('get');

        if (!in_array($this->_removeFromSearch, 'csrfhash')) {
            // always remove the csrfhash
            $this->_removeFromSearch []= 'csrfhash';
        }

        foreach ($this->_removeFromSearch as $element) {
            $this->removeElement($element);
        }

        foreach ($this->getElements() as $element) {
            // mark all elements as not required - used for partial searches
            $element->setRequired(false);

            // remove the database related validators 
            foreach ($element->getValidators() as $validator) {
                if ($validator instanceof Zend_Validate_Db_Abstract) {
                    $element->removeValidator(get_class($validator));
                }
            }
        }

        return $this;
    }

    /**
     * Convert a form to its normal state
     * 
     * @access public
     * @return ZFAdmin_Form_Abstract
     */
    public function convertToNormalForm()
    {
        if (!$this->isSearchForm()) {
            return $this;
        }

        // clear the elements and re-run init
        $this->clearElements();
        $this->init();
    }

    /**
     * Check if the form is in "search state"
     * 
     * @access public
     * @return bool
     */
    public function isSearchForm()
    {
        return (bool) $this->_isSearchForm;
    }
}

