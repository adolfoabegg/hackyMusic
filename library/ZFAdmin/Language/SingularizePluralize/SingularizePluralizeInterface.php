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
 * Parent interface for all Singularize / Pluralize classes
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Language
 * @package SingularizePluralize
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu> based on work by Sho Kuwamoto <sho@kuwamoto.org>
 * @since v.0.1.0
 */

interface ZFAdmin_Language_SingularizePluralize_SingularizePluralizeInterface
{
    /**
     * Pluralize the value
     * 
     * @param string $value 
     * @access public
     * @return string
     */
    public function pluralize($value);

    /**
     * Singularize the value
     * 
     * @param string $value 
     * @access public
     * @return string
     */
    public function singularize($value);

    /**
     * Check if the value is in a plural form
     * 
     * @param string $value 
     * @access public
     * @return bool
     */
    public function isPlural($value);

    /**
     * Check if the value is in a singular form
     * 
     * @param string $value 
     * @access public
     * @return bool
     */
    public function isSingular($value);
}

