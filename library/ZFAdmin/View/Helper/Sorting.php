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

class ZFAdmin_View_Helper_Sorting extends Zend_View_Helper_Abstract
{
    /**
     * Get an instance of the helper and use it as a fluent interface
     *
     * @access public
     * @return ZFAdmin_View_Helper_Sorting
     */
    public function sorting()
    {
        return $this;
    }

    /**
     * Get the CSS class for $column (can be one of: '', 'sorting-desc', 'sorting-asc')
     * 
     * @param string $column
     * @access public
     * @return string
     */
    public function cssClass($column)
    {
        $utility = ZFAdmin_Utils_Sorting::getInstance();
        $sortingCriteria = $utility->extractSortingCriteria();

        if ($sortingCriteria['column'] == $column) {
            return 'sorting-' . strtolower($sortingCriteria['direction']);
        }

        return '';
    }

    /**
     * Get the url for $column
     * 
     * @param string $column
     * @param string $defaultSortingOrder
     * @access public
     * @return string
     */
    public function url($column, $defaultSortingOrder = 'ASC')
    {
        $utility = ZFAdmin_Utils_Sorting::getInstance();
        return $utility->createUrl($column, $defaultSortingOrder);
    }
}

