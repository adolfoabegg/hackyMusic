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
 * Create a ?page=x url while maintaining the current url parameters and preventing
 * XSS attacks
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_View
 * @subpackage Helper
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdmin_View_Helper_PaginationUrl extends Zend_View_Helper_Abstract
{
    /**
     * Convenience method
     * call $this->paginationUrl() in the view to access 
     * the helper
     *
     * @param int page
     * @param Zend_Controller_Request_Http $request 
     * @access public
     * @return string
     */
    public function paginationUrl($page, Zend_Controller_Request_Http $request = null)
    {
        $array = array(
            'page' => $page,
        );

        if (null === $request) {
            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();
        }
        
        $array += ZFAdmin_Utils_QueryEncoder::encodeQuery($request->getQuery());

        return '?' . http_build_query($array);
    }
}


