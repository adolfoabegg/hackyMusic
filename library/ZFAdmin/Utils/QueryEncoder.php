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
 * Encode the existing query elements to prevent XSS attacks
 *
 *
 * @category ZFAdmin
 * @package ZFAdmin_Utils
 * @subpackage Exception
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdmin_Utils_QueryEncoder 
{
    /**
     * Encode the existing query elements to prevent XSS attacks
     * 
     * @param array $array 
     * @access protected
     * @return array
     */
    public static function encodeQuery(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[urlencode(urldecode($key))] = self::encodeQuery($value);
            } else {
                $array[urlencode(urldecode($key))] = urlencode(urldecode($value));
            }
        }

        return $array;
    }
}

