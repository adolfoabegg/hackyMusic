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
 * Parent controller for all ZFAdmin controller that provides basic CRUD functionality.
 * Child controllers call the methods defined here to generate the UI and the basic 
 * interaction. If additional logic is required to cover a particular situation, just 
 * override the inherited method or write the code in the child controller.
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Controller
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

abstract class ZFAdmin_Controller_ControllerAbstract extends Zend_Controller_Action
{
    /**
     * Page's title - will be placed in the <title> tag
     * 
     * @var title
     * @access protected
     */
    protected $_title = null;

    /**
     * Current controller's main form
     * 
     * @var string|ZFAdmin_Form_Abstract
     * @access protected
     */
    protected $_form = null;

    /**
     * Current controller's main table
     * 
     * @var string|ZFAdmin_Table_Abstract
     * @access protected
     */
    protected $_table = null;

    /**
     * Generate the UI to add an item
     * 
     * @param string $successUrl 
     * @param string $invalidDataMessage 
     * @param string $successMessage 
     * @access protected
     * @return void
     */
    protected function _addItem($successUrl,  $successMessage = 'Item "%s" was added succesfully', 
        $invalidDataMessage = 'Please correct the errors highlighted below')
    {
        if ($this->getMethod()->isPost()) {
            $form = $this->getForm();
            
            if ($form->isValid($this->getRequest()->getPost())) {
                $table = $this->getTable();

                $row = $table->createRow(array());
                $row->save();

                $this->_helper->flashMessenger(
                    array(
                        'success' => $this->_translate(
                            sprintf(
                                $successMessage,
                                Zend_Filter::filterStatic($row->getDisplayValue(), 'HtmlEntities')
                            )
                        ),
                    )
                );
                $this->_redirect($successUrl);
            } else {
                $this->_helper->flashMessenger(
                    array(
                        'error' => $this->_translate($invalidDataMessage),
                    )
                );
            }
        }
    }

    /**
     * Generate the UI to edit an item
     * 
     * @param string $successUrl 
     * @param string $successMessage 
     * @param string $invalidIdMessage 
     * @param string $itemNotFoundMessage 
     * @access protected
     * @return void
     */
    protected function _editItem($successUrl, $successMessage = 'Item "%s" was succesfully updated.', 
        $invalidIdMessage = '"%s" is not a valid id.', $itemNotFoundMessage = 'The requested item could not be found')
    {
        $row = $this->_retrieveItem($invalidIdMessage, $itemNotFoundMessage);

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm();

            if($form->isValid($this->getRequest()->getPost())) {
                $row->setFromArray($form->getValues());
                $row->save();
                $this->_helper->flashMessenger(
                    array(
                        'success' => $this->translate(
                            sprintf(
                                $successMessage,
                                Zend_Filter::filterStatic($row->getDisplayValue(), 'HtmlEntities')
                            )
                        ),
                    )
                );
                $this->_redirect($successUrl);
            } 
        } else {
            $form->populate($row->toArray());
        }
        
        $this->view->form = $form;
        $this->view->row  = $row;   
    }

    /**
     * Retrieve a row from the table based on key in the URL (only supports numeric IDs)
     * 
     * @param string $redirectUrl 
     * @param string $invalidIdMessage 
     * @param string $itemNotFoundMessage 
     * @param mixed $redirectIfNotFound 
     * @param string $key 
     * @access protected
     * @return void
     */
    protected function _retrieveItem($redirectUrl = null, $invalidIdMessage = '"%s" is not a valid id.', 
        $itemNotFoundMessage = 'The requested item could not be found', $redirectIfNotFound = true, $key = 'id')
    {
        $id = $this->_getParam($key);

        if (!is_numeric($id)) {
            $this->_helper->flashMessenger(
                array(
                    'error' => $this->_translate(
                        sprintf(
                            $invalidIdMessage,
                            Zend_Filter::filterStatic($id, 'HtmlEntities')
                        )
                    ),
                )
            );
            $this->_redirect($redirectUrl);
        }

        $table = $this->getTable();
        $row = $table->find($id)->current();

        if ($redirectIfNotFound && empty($row)) {
            $this->_helper->flashMessenger(
                array(
                    'error' => $this->_translate($itemNotFoundMessage),
                )
            );
            $this->_redirect($redirectUrl);
        }
        
        return $row;
    }

    /**
     * Generate the UI to browse & search a list of items from the current table
     * 
     * @access protected
     * @return void
     */
    protected function _listing()
    {
        $table = $this->getTable();
        $form  = $this->getForm();

        $form->convertToSearchForm();
        
        $sortingUtility = new ZFAdmin_Utils_Sorting();
        $sortingUtility->setDefaultCriteria(
            array(
                'column'    => $table->getSortingColumn(),
                'direction' => $table->getDefaultSortingOrder(),
            )
        );

        $paginator = $table->search(
            $form->getNonNullValidValues($this->getRequest()->getQuery()),
            $sortingUtility->extractSortingCriteria()
        );

        $paginator->setCurrentPageNumber($this->_getPage());

        $this->view->paginator  = $paginator;
        $this->view->form       = $form;
        $this->view->sortingCriteria = $table->getLastCriteriaInfo();
    }

    /**
     * Translate a string if there's a Zend_Translate available in the 
     * registry - proxy to Zend_Translate's translate() method
     * 
     * @param mixed $messageId 
     * @param mixed $locale 
     * @access protected
     * @return void
     */
    protected function _translate($messageId, $locale = null)
    {
        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $translator = Zend_Registry::get('Zend_Translate');
            $messageId = $translator->translate($messageId, $locale);
        }

        return $messageId;
    }

    /**
     * Get the table for this controller
     * 
     * @access public
     * @return ZFAdmin_Table_Abstract
     */
    public function getTable()
    {
        if (null === $this->_table) {
            throw new ZFAdmin_Controller_Exception_TableNotDefined();   
        }

        if (is_string($this->_table)) {
            $this->_table = new $this->{_table}();
        }

        return $this->_table;
    }

    /**
     * Get the form for this controller
     * 
     * @access public
     * @return ZFAdmin_Form_Abstract
     */
    public function getForm()
    {
        if (null === $this->_form) {
            throw new ZFAdmin_Controller_Exception_FormNotDefined();
        }

        if (is_string($this->_form)) {
            $this->_form = new $this->{_form}();
        }

        return $this->_form;
    }

    /**
     * Get the page number from the current reuqest
     * 
     * 
     * @param string $page 
     * @param int $default 
     * @access public
     * @return int
     */
    public function _getPage($page = 'page', $default = 1)
    {
        return $this->getRequest()->getParam($page, $default);
    }
}
