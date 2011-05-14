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
 * Form for managing the user groups
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Form
 * @subpackage Administration
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdmin_Form_Administration_GroupsForm extends ZFAdmin_Form_FormAbstract
{
    /**
     * Init the form
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->addElement(
            'text',
            'name',
            array(
                'label'      => 'Name',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );

        $this->addElement(
            'select',
            'parent_id',
            array(
                'label'      => 'Parent group',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'multiOptions' => $parentIdOptions,
            )
        );
        

        $this->addElement(
            'hidden',
            'id',
            array(
                'required' => true,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );

        $this->addElement(
            'submit',
            'submit',
            array(
                'label'      => 'Submit',
                'required'   => true,
            )
        );
        
    }
}

